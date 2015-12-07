<?php
namespace Cms\Access\AuthBackend\Type;

use \Cms\Access\AuthBackend\Base as AuthBackendBase;
use \Cms\Access\Acl\Base as AclBase;
use \Seitenbau\Registry as Registry;
use Cms\Access\PasswordHasher;
use Cms\Access\Auth\Result;
use Cms\Business\User;

/**
 * @package      Cms
 * @subpackage   Access\AuthBackend
 */
class CmsAuthBackend extends AuthBackendBase
{
  const BACKEND_NAME = 'Cms';

  /**
   * @param   \Cms\Access\Manager $accessManager
   */
  public function __construct(\Cms\Access\Manager $accessManager)
  {
    parent::__construct($accessManager);
  }

  /**
   * @return  string
   */
  public function getBackendName()
  {
    return self::BACKEND_NAME;
  }

  /**
   * @return  boolean
   */
  protected function validateRegisteredUserOnStart()
  {
    return true;
  }

  /**
   * @return boolean
   */
  public function logout()
  {
    if (!$this->isIdentityCreatedFromBackend(self::BACKEND_NAME, $this->getIdentityAsArray())) {
      return false;
    }

    $this->clearIdentity();
    return true;
  }

  /**
   * @param   string $identity    - email address
   * @param   mixed  $credentials - e.g. password string
   *
   * @return  \Cms\Access\Auth\Result
   */
  public function checkLogin($identity, $credentials)
  {
    if (is_null($identity) || is_null($credentials) || !is_string($credentials)) {
      return null;
    }

    try {
      // get user object
      $userBusiness = new User('User');
      $user = $userBusiness->getByEmail($identity);

      // check credentials
      $ph = new PasswordHasher();
      if (!$ph->validate($credentials, $user->getPassword())) {
        return null;
      }

      // create auth result and return it
      return $this->createSuccessAuthResult($user);

    } catch (\Exception $e) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $e, \Seitenbau\Log::DEBUG);
      return null;
    }
  }

  /**
   * @param   array $identity
   *
   * @return  array
   */
  public function getRoles(array $identity)
  {
    $roles = array('guest');
    if (isset($identity['superuser'])) {
      if (true == $identity['superuser']) {
        $roles[] = AclBase::ROLE_SUPERUSER;
      } else {
        $roles[] = AclBase::ROLE_USER;
      }
    }
    return $roles;
  }

  /**
   * @param   array $identity
   *
   * @return  array
   */
  public function getWebsitePrivileges(array $identity)
  {
    $websitePrivileges = array();
    if (isset($identity['groups']) && is_array($identity['groups'])) {
      $groupBusiness = new \Cms\Business\Group('Group');
      foreach ($identity['groups'] as $group) {
        if (isset($websitePrivileges[$group['websiteid']])) {
          continue;
        }
        $websitePrivileges[$group['websiteid']] = $groupBusiness->getWebsitePrivilegesOfAuthenticatedUser(
            $identity['id'],
            $group['websiteid']
        );
      }
    }
    return $websitePrivileges;
  }


  /**
   * @param   array  $identity
   * @param   string $websiteId
   * @param   string $area
   * @param   string $privilege
   *
   * @return  boolean
   */
  public function checkWebsitePrivilegeForIdentity($identity, $websiteId, $area, $privilege)
  {

    if (Registry::getConfig()->acl->render_as_guest === true) {
      if ($area == 'render' && $privilege == 'all') {
        return true;
      }
    }

    $groupBusiness = new \Cms\Business\Group('Group');
    $groupsOfUser = $groupBusiness->getAllByUserAndWebsiteId($identity['id'], $websiteId);

    if (is_array($groupsOfUser) && count($groupsOfUser) > 0) {
      foreach ($groupsOfUser as $groupOfUser) {
        if ($area == 'render' && $privilege == 'all') {
          return true;
        }
        $rightsOfGroupAsJson = $groupOfUser->getRights();
        if ($rightsOfGroupAsJson !== \Cms\Dao\Group::DEFAULT_EMPTY_RIGHTS) {
          $rightsOfGroupAsArray = json_decode($rightsOfGroupAsJson, true);
          foreach ($rightsOfGroupAsArray as $rightOfGroup) {
            if (isset($rightOfGroup['area']) && $rightOfGroup['area'] === $area
              && isset($rightOfGroup['privilege']) && $rightOfGroup['privilege'] === $privilege
            ) {
              return true;
            }
          }
        }
      }
    }
    return false;
  }

  /**
   * @param array  $identity
   * @param string $websiteId
   *
   * @return boolean
   */
  public function isInAnyWebsiteGroup(array $identity, $websiteId)
  {
    $userGroups = (isset($identity['groups']))
      ? $identity['groups']
      : array();

    if (is_array($userGroups)) {
      foreach ($userGroups as $userGroup) {
        if ($userGroup['websiteid'] == $websiteId) {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * @param   array $identity
   *
   * @return  string|null
   */
  public function getUserId(array $identity)
  {
    if (!$this->isIdentityCreatedFromBackend($this->getBackendName(), $identity)) {
      return null;
    }
    if (!isset($identity['id']) || empty($identity['id'])) {
      return null;
    }
    return $identity['id'];
  }
  /**
   * @param \Cms\Data\User $user
   *
   * @return Result
   */
  protected function createSuccessAuthResult($user)
  {
    $authIdentity = $user->toArray();

    // groups
    $authIdentity['groups'] = array();
    $groups = $user->getGroups();
    foreach ($groups as $g) {
      $authIdentity['groups'][] = array('id' => $g->getId(), 'websiteid' => $g->getWebsiteId());
    }

    $authResult = $this->createAuthResult(
        Result::SUCCESS,
        $authIdentity,
        array('Authentication successful.'),
        self::BACKEND_NAME
    );

    return $authResult;
  }
}
