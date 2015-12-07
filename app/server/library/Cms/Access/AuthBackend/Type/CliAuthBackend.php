<?php
namespace Cms\Access\AuthBackend\Type;

use \Cms\Access\AuthBackend\Base as AuthBackendBase;
use Cms\Access\Auth\Result as AuthResult;
use \Cms\Access\Acl\Base as AclBase;

/**
 * @package      Cms
 * @subpackage   Access\AuthBackend
 */
class CliAuthBackend extends AuthBackendBase
{
  const BACKEND_NAME = 'Cli';

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
    if ($this->getAccessManager()->isCliMode()) {
      $this->setAuthResultToManager($this->createCliUserAuthResult(), true);
    }
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
   * @param   string $identity
   * @param   mixed  $credentials
   *
   * @return  \Cms\Access\Auth\Result
   */
  public function checkLogin($identity, $credentials)
  {
    return null;
  }

  /**
   * @param   array $identity
   *
   * @return  array
   */
  public function getRoles(array $identity)
  {
    $roles = array();
    if (isset($identity['cliuser']) && true == $identity['cliuser']) {
      $roles[] = AclBase::ROLE_CLIUSER;
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
    return array();
  }

  /**
   * @params array  $identity
   * @params string $websiteId
   *
   * @return boolean
   */
  public function isInAnyWebsiteGroup(array $identity, $websiteId)
  {
    $accessManager = $this->getAccessManager();
    if (!$accessManager->isCliMode()) {
      return false;
    }
    return true;
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
    $accessManager = $this->getAccessManager();
    if (!$accessManager->isCliMode()) {
      return false;
    }
    return true;
  }

  /**
   * @param   array $identity
   *
   * @return  string|null
   */
  public function getUserId(array $identity)
  {
    return null;
  }

  /**
   * @return  \Cms\Access\Auth\Result
   */
  protected function createCliUserAuthResult()
  {
    return $this->createAuthResult(
        AuthResult::SUCCESS,
        array('cliuser' => true),
        array('Cli-Authentication successful.'),
        self::BACKEND_NAME
    );
  }
}
