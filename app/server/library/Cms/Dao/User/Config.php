<?php
namespace Cms\Dao\User;

use Cms\Dao\User as UserDaoInterface;
use Seitenbau\Log;
use Seitenbau\Registry;
use Cms\Exception as CmsException;

/**
 * Class Config
 *
 * @package Cms\Dao\User
 */
class Config implements UserDaoInterface
{

  /**
   * @param  array   $columnValues
   * @param  boolean $useColumnsValuesId
   *
   * @throws CmsException
   * @return \Cms\Data\User
   */
  public function create(array $columnValues, $useColumnsValuesId = false)
  {
    // read-only DAO
    throw new UserIsReadOnlyException(1004, __METHOD__, __LINE__, null, new \Exception('read only DAO'));
  }

  /**
   * @param string $id
   * @param array  $columnsValues
   *
   * @return \Cms\Data\User|void
   * @throws CmsException
   */
  public function update($id, array $columnsValues)
  {
    $this->getById($id);
    // read-only DAO
    throw new UserIsReadOnlyException(1006, __METHOD__, __LINE__, null, new \Exception('read only DAO'));
  }

  /**
   * @param  string $id
   *
   * @throws UserIsReadOnlyException
   * @throws \Cms\Exception
   * @return boolean
   */
  public function delete($id)
  {
    $this->getById($id);
    // read-only DAO
    throw new UserIsReadOnlyException(1011, __METHOD__, __LINE__, null, new \Exception('read only DAO'));
  }

  /**
   * @param  string $websiteId
   *
   * @return \Cms\Data\User[]
   */
  public function getAll($websiteId = null)
  {
    $users = $this->getAllUsersFromConfig();
    return array_values($users);
  }

  /**
   * @param string $id
   *
   * @throws CmsException
   * @return \Cms\Data\User
   */
  public function getById($id)
  {
    $users = $this->getAllUsersFromConfig();
    if (array_key_exists($id, $users)) {
      return $users[$id];
    }
    throw new UserNotFoundException(1002, __METHOD__, __LINE__, array(
      'userId' => $id,
    ));
  }

  /**
   * @param array $ids
   *
   * @throws UserNotFoundException
   * @return \Cms\Data\User[]
   */
  public function getByIds(array $ids)
  {
    $result = array();
    $users = $this->getAllUsersFromConfig();
    foreach ($ids as $id) {
      if (!array_key_exists($id, $users)) {
        throw new UserNotFoundException(1002, __METHOD__, __LINE__, array(
          'userId' => $id,
        ));
      }
      $result[] = $users[$id];
    }
    return $result;
  }

  /**
   * @param string $email
   * @param string $id
   *
   * @throws CmsException
   * @return \Cms\Data\User
   */
  public function getByEmailAndIgnoredId($email, $id = null)
  {
    $users = $this->getAllUsersFromConfig();
    foreach ($users as $user) {
      // XXX: strcasecmp doesn't work correct on multi-byte/unicode/utf-8
      if (strcasecmp($user->getEmail(), $email) == 0 && $user->getId() != $id) {
        return $user;
      }
    }
    throw new UserNotFoundException(1002, __METHOD__, __LINE__, null);
  }

  /**
   * @return \Cms\Data\User[]
   */
  public function getAllSuperusers()
  {
    $superusers = array();
    $users = $this->getAllUsersFromConfig();
    foreach ($users as $user) {
      if ($user->isSuperuser()) {
        $superusers[] = $user;
      }
    }

    return $superusers;
  }

  /**
   * @return boolean
   */
  public function deleteAll()
  {
    // read-only DAO
    return false;
  }

  /**
   * Owner of the space
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\User
   */
  public function getOwner()
  {
    $user = $this->getOwnerFromConfig();
    if (is_null($user)) {
      throw new UserNotFoundException(1002, __METHOD__, __LINE__);
    }
    return $user;
  }

  /**
   * @return \Cms\Data\User[]
   */
  protected function getAllUsersFromConfig()
  {
    $users = $this->getUsersFromConfig();
    $owner = $this->getOwnerFromConfig();
    if (!is_null($owner)) {
      $users[$owner->getId()] = $owner;
    }
    return $users;
  }

  /**
   * @return \Cms\Data\User[]
   */
  protected function getUsersFromConfig()
  {
    $cfg = Registry::getConfig();
    if (!isset($cfg->users)) {
      return array();
    }
    $users = array();
    foreach ($cfg->users->toArray() as $userArray) {
      $user = $this->getUserFromArray($userArray);
      if (is_null($user)) {
        continue;
      }
      // All users in the config are superuser
      $user->setSuperuser(true);
      $user->setOwner(false);
      $users[$user->getId()] = $user;
    }
    return $users;
  }

  /**
   * @return \Cms\Data\User|null
   */
  protected function getOwnerFromConfig()
  {
    $cfg = Registry::getConfig();
    if (!isset($cfg->owner)) {
      return null;
    }
    $owner = $this->getUserFromArray($cfg->owner->toArray());
    if (is_null($owner)) {
      return null;
    }
    $owner->setSuperuser(true);
    $owner->setOwner(true);
    return $owner;
  }

  /**
   * Convert assoc array to user object
   * @param $assocUserArray
   * @return \Cms\Data\User
   */
  protected function getUserFromArray($assocUserArray)
  {
    // validate fields
    try {
      $this->validateRequiredFields($assocUserArray);
    } catch (\Exception $e) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $e, Log::ERR);
      return null;
    }

    $user = new \Cms\Data\User();
    $user->setId($assocUserArray['id']);

    // user is readonly and not deletable
    $user->setReadonly(true);
    $user->setDeletable(false);

    // required fields
    $user->setFirstname($assocUserArray['firstname']);
    $user->setLastname($assocUserArray['lastname']);
    $user->setEmail($assocUserArray['email']);
    $user->setPassword($assocUserArray['password']);

    // optional

    if (isset($assocUserArray['gender'])) {
      $user->setGender($assocUserArray['gender']);
    }

    if (isset($assocUserArray['language'])) {
      $user->setLanguage($assocUserArray['language']);
    }

    if (isset($assocUserArray['passwordResetUrl'])) {
      $sourceInfo = $user->getSourceInfo();
      $sourceInfo['passwordResetUrl'] = $assocUserArray['passwordResetUrl'];
      $user->setSourceInfo($sourceInfo);
    }

    if (isset($assocUserArray['dashboardUrl'])) {
      $sourceInfo = $user->getSourceInfo();
      $sourceInfo['dashboardUrl'] = $assocUserArray['dashboardUrl'];
      $user->setSourceInfo($sourceInfo);
    }

    if (isset($assocUserArray['upgradeUrl'])) {
      $sourceInfo = $user->getSourceInfo();
      $sourceInfo['upgradeUrl'] = $assocUserArray['upgradeUrl'];
      $user->setSourceInfo($sourceInfo);
    }

    return $user;
  }

  /**
   * @param array $assocUserArray
   *
   * @throws \Exception
   */
  private function validateRequiredFields($assocUserArray)
  {
    if (!isset($assocUserArray['id'])) {
      throw new \Exception("id doesn't exists");
    }
    if (!isset($assocUserArray['firstname'])) {
      throw new \Exception("firstname doesn't exists");
    }
    if (!isset($assocUserArray['lastname'])) {
      throw new \Exception("lastname doesn't exists");
    }
    if (!isset($assocUserArray['email'])) {
      throw new \Exception("email doesn't exists");
    }
    if (!isset($assocUserArray['password'])) {
      throw new \Exception("password doesn't exists");
    }
  }
}
