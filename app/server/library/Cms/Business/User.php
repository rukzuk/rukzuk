<?php
namespace Cms\Business;

use Cms\Business\User\UserIsReadOnlyException;
use Seitenbau\Registry;
use Cms\Exception as CmsException;
use Seitenbau\Log as SbLog;

/**
 * Stellt die Business-Logik fuer User zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 *
 * @method \Cms\Service\User getService
 */
class User extends Base\Service
{
  const USER_LOGIN_ACTION = 'USER_LOGIN_ACTION';
  const USER_CREATE_ACTION = 'USER_CREATE_ACTION';
  const USER_DELETE_ACTION = 'USER_DELETE_ACTION';

  /**
   * @return boolean
   */
  private function isUserMailActive()
  {
    if (Registry::getConfig()->user->mail->activ == 1) {
      return true;
    }
    
    return false;
  }

  /**
   * @param string $userId
   * @param string $oldPassword
   * @param string $newPassword
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function changePassword($userId, $oldPassword, $newPassword)
  {
    $user = $this->getService()->getById($userId);
    if (!$this->validatePassword($oldPassword, $user->getPassword())) {
      throw new CmsException(1021, __METHOD__, __LINE__);
    }
    return $this->getService('User')->changePassword(
        $userId,
        $this->getHashedPassword($newPassword)
    );
  }

  /**
   * check option code.
   * If optin code is valid, set the new password and login user if username given
   *
   * @param string $code
   * @param string $newPassword
   * @param string $username
   */
  public function optin($code, $newPassword, $username = null)
  {
    $optinService = $this->getOptinService();

    $optinService->validateCode($code);
    $userId = $optinService->getUserIdByCode($code);

    $this->getService('User')->changePassword(
        $userId,
        $this->getHashedPassword($newPassword)
    );

    $optinService->deleteByCode($code);

    if (!empty($username)) {
      try {
        $this->login($username, $newPassword);
      } catch (\Exception $doNothing) {
      }
    }
  }

  /**
   * @param string $code
   */
  public function validateOptin($code)
  {
    $this->getOptinService()->validateCode($code);
  }

  /**
   * @param string $email
   *
   * @throws \Cms\Exception
   */
  public function renewPassword($email)
  {
    if (!$this->isUserMailActive()) {
      return;
    }

    // user not found
    try {
      $user = $this->getService()->getByEmail($email);
    } catch (\Cms\Exception $e) {
      throw new \Cms\Exception(1040, __METHOD__, __LINE__, null, $e);
    }

    // user is readonly
    if ($user->isReadonly()) {
      throw new UserIsReadOnlyException(1041, __METHOD__, __LINE__, $user);
    }
    
    $optinService = $this->getOptinService();
    $optins = $optinService->createAndStorePasswordCode($user);
    $optinService->sendRenewPasswordMails($optins);

  }

  /**
   * @param array   $userIds
   * @param boolean $sendMail
   *
   * @return array
   */
  public function register(array $userIds, $sendMail = true)
  {
    if (!$this->isUserMailActive()) {
      return;
    }
    
    $users = $this->getService()->getByIds($userIds);
    if (is_array($users) && count($users) > 0) {
      $optinService = $this->getOptinService();
      $optins = $optinService->createAndStoreOptinCodes($users);
      if ($sendMail) {
        $optinService->sendOptinMails($optins);
      }
    }
    return $optins;
  }
  
  /**
   * @param  array $createValues
   * @return \Cms\Data\User
   */
  public function create(array $createValues)
  {
    return $this->getService()->create($createValues);
  }

  /**
   * @param  string $id
   * @param  array  $editValues
   * @return \Cms\Data\User
   */
  public function edit($id, array $editValues)
  {
    if (isset($editValues['password']) && !empty($editValues['password'])) {
      $editValues['password'] = $this->getHashedPassword($editValues['password']);
      $this->getOptinService()->deleteByUserId($id);
    }
    return $this->getService()->edit($id, $editValues);
  }

  /**
   * @param  string $id
   * @return boolean
   */
  public function delete($id)
  {
    $this->getOptinService()->deleteByUserId($id);
    return $this->getService()->delete($id);
  }

  /**
   * @return boolean
   */
  public function deleteAll()
  {
    $this->getOptinService()->deleteAll();
    return $this->getService()->deleteAll();
  }

  /**
   * @param  string  $websiteId
   * @return array[] Cms\Data\User
   */
  public function getAll($websiteId = null)
  {
    $all = $this->getService()->getAll($websiteId);

    $groupService = $this->getService('Group');

    foreach ($all as $index => $user) {
      $groupsOfUser = $groupService->getAllByUserId($user->getId());
      $user->setGroups($groupsOfUser);
      $all[$index] = $user;
    }

    return $all;
  }

  /**
   * @param  string $id
   * @return \Cms\Data\User
   */
  public function getById($id)
  {
    $user = $this->getService()->getById($id);

    $groupService = $this->getService('Group');
    $user->setGroups($groupService->getAllByUserId($user->getId()));

    return $user;
  }
  
  /**
   * Gib Informationenen zum angemeleten User zurueck
   */
  public function getInfoFromDeclaredUser()
  {
    $accessManager = $this->getAccessManager();
    if (!$accessManager->hasIdentity()) {
      return;
    }
    
    $userIdentityArray = $accessManager->getIdentityAsArray();
    $userIdentityArray['privilege'] = $accessManager->getWebsitePrivileges();
    
    return $userIdentityArray;
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $groupIds
   * @return boolean
   */
  public function addGroups($id, $websiteId, array $groupIds)
  {
    return $this->getService()->addGroups($id, $websiteId, $groupIds);
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $groupIds
   * @return boolean
   */
  public function removeGroups($id, $websiteId, array $groupIds)
  {
    return $this->getService()->removeGroups($id, $websiteId, $groupIds);
  }

  /**
   * @param   string  $username
   * @param   string  $userpassword
   * @return  boolean
   * @throws  \Cms\Exception (auch bei fehlerhafter Anmeldung)
   */
  public function login($username, $userpassword)
  {
    $this->getAccessManager()->login($username, $userpassword);
  }
  
  /**
   * @throws  \Cms\Exception
   */
  public function logout()
  {
    try {
      $accessManager = $this->getAccessManager();
      if ($accessManager->hasIdentity()) {
        $user = $accessManager->getIdentityAsArray();
        $this->getBusiness('Lock')->unlockByUserId($user['id']);
      }
    } catch (\Exception $logOnly) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::WARN);
    }
    $this->getAccessManager()->logout();
  }

  /**
   * @param  string $password
   * @return string
   */
  public function getHashedPassword($password)
  {
    return $this->getAccessManager()->getPasswordHasher()->create($password);
  }

  /**
   * @param string $password  the password to check
   * @param string $good_hash the hash which should be match the password
   *
   * @return boolean
   */
  public function validatePassword($password, $good_hash)
  {
    return $this->getAccessManager()->getPasswordHasher()->validate($password, $good_hash);
  }

  /**
   * Pruefung, ob der angemeldete User die Rechte fuer die aufgerufene Aktion
   * besitzt
   *
   * @param array  $identity
   * @param string $rightname Name des Rechts, auf das geprueft wird
   * @param array  $check
   *
   * @return boolean
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // Je nach Recht unterscheiden
    switch($rightname)
    {
      case 'create':
      case 'delete':
      case 'addGroups':
      case 'removeGroups':
        // Nur ein Superuser darf diese Aktionen ausfuehren
        if ($this->isSuperuser($identity)) {
        // Aktion erlaubt
          return true;
        }
            break;

      case 'changePassword':
        // Nur sein eigenes Passwort
        if (isset($identity['id']) && isset($check['id']) && $check['id'] == $identity['id']) {
          // Aktion erlaubt
          return true;
        }
            break;

      case 'edit':
            return $this->hasUserRightsToEditUser($identity, $check);
        break;
    }

    // Default: Keine Rechte
    return false;
  }

  /**
   * Pruefung, ob der angemeldete User die Rechte zum Bearbeiten eines Benutzer
   * besitzt
   *
   * @param array   $identity Array mit Benutzerinformationen
   * @param array   $check    Array mit den zu aendernden Attributen
   * @return boolean
   */
  private function hasUserRightsToEditUser($identity, $check)
  {
    // Superuser darf einen Benutzer bearbeiten
    if ($this->isSuperuser($identity)) {
      return true;
    }

    // Benutzer darf sich selbst bearbeiten
    if (isset($identity['id']) && isset($check['id'])
        && $check['id'] == $identity['id']
        && (isset($check['attributes']) && is_array($check['attributes']))) {
    // Benutzer darf aber nur bestimmte Attribute bearbeiten
      $expectedAttributes = array('email', 'lastname', 'firstname',
        'gender', 'password');
      foreach ($check['attributes'] as $nextAttribute => $nextAttributeValue) {
      // Wurde ein weiters Attribute uebergeben?
        if (!in_array($nextAttribute, $expectedAttributes)
            && isset($nextAttributeValue)
        ) {
          // Editieren nicht erlaubt
          return false;
        }
      }

      // Editieren erlaubt
      return true;
    }

    // Editierren nicht erlaubt
    return false;
  }

  /**
   * @return \Cms\Data\User[]
   */
  public function getAllSuperusers()
  {
    $all = $this->getService()->getAllSuperusers();

    $groupService = $this->getService('Group');

    foreach ($all as $index => $user) {
      $groupsOfUser = $groupService->getAllByUserId($user->getId());
      $user->setGroups($groupsOfUser);
      $all[$index] = $user;
    }

    return $all;
  }

  /**
   * @return \Cms\Data\User
   */
  public function getOwner()
  {
    $user = $this->getService()->getOwner();

    $groupService = $this->getService('Group');
    $groupsOfUser = $groupService->getAllByUserId($user->getId());
    $user->setGroups($groupsOfUser);

    return $user;
  }

  /**
   * Get User by E-Mail
   *
   * @param string $email
   *
   * @return \Cms\Data\User
   */
  public function getByEmail($email)
  {
    $user = $this->getService()->getByEmail($email);

    $groupService = $this->getService('Group');
    $user->setGroups($groupService->getAllByUserId($user->getId()));

    return $user;
  }

  /**
   * @return \Cms\Service\Optin
   */
  protected function getOptinService()
  {
    return $this->getService('Optin');
  }
}
