<?php
namespace Cms\Access\AuthBackend;

use Cms\Access\Auth\Result as AuthResult;
use Cms\Business\UserStatus as UserStatusBusiness;

/**
 * @package      Cms
 * @subpackage   Access\Auth
 */
abstract class Base
{
  private $accessManager;

  /**
   * @param   \Cms\Access\Manager $accessManager
   */
  public function __construct(\Cms\Access\Manager $accessManager)
  {
    $this->accessManager = $accessManager;
    $this->validateRegisteredUserOnStart();
  }

  /**
   * @return  array of \Cms\Controller\Plugin\Base
   */
  public function getControllerPlugins()
  {
    return array();
  }

  /**
   * @return \Cms\Access\Manager
   */
  protected function getAccessManager()
  {
    return $this->accessManager;
  }

  protected function clearIdentity()
  {
    $this->getAccessManager()->clearIdentity();
  }

  protected function setAuthResultToManager(\Cms\Access\Auth\Result $authResult, $clearIdentityFirst = false)
  {
    if ($clearIdentityFirst) {
      $this->clearIdentity();
    }
    $this->getAccessManager()->setAuthResult($authResult);
  }

  /**
   * @return array
   */
  protected function getIdentityAsArray()
  {
    return $this->getAccessManager()->getIdentityAsArray();
  }

  /**
   * @params array  $backendName
   * @params array  $identity
   *
   * @return boolean
   */
  public static function isIdentityCreatedFromBackend($backendName, array $identity)
  {
    return AuthResult::isIdentityCreatedFromBackend($backendName, $identity);
  }

  public function createAuthResult($code, $identity, array $messages = array(), $backendName = null)
  {
    $authResult = new AuthResult($code, $identity, $messages);
    if (isset($backendName)) {
      $authResult->addBackendName($backendName);
    }
    return $authResult;
  }

  /**
   * @param   string $identity
   * @param   mixed  $credentials
   *
   * @return  \Cms\Access\Auth\Result
   */
  public function login($identity, $credentials)
  {
    return $this->checkLogin($identity, $credentials);
  }

  /**
   * @param \DateTime $lastLogin
   */
  public function setLastLogin(\DateTime $lastLogin)
  {
    if (is_null($lastLogin)) {
      return;
    }

    $identity = $this->getIdentityAsArray();
    $userId = $this->getUserId($identity);
    if (empty($userId)) {
      return;
    }

    $this->getUserStatusBusiness()->setLastLogin($userId, $this->getBackendName(), $lastLogin);
  }

  /**
   * @return UserStatusBusiness
   */
  protected function getUserStatusBusiness()
  {
    return new UserStatusBusiness('UserStatus');
  }

  /**
   * @return  string
   */
  abstract public function getBackendName();

  /**
   * @params array  $identity
   * @params string $websiteId
   *
   * @return boolean
   */
  abstract public function isInAnyWebsiteGroup(array $identity, $websiteId);

  /**
   * @param   array $identity
   *
   * @return  array
   */
  abstract public function getRoles(array $identity);

  /**
   * @param   array $identity
   *
   * @return  array
   */
  abstract public function getWebsitePrivileges(array $identity);

  /**
   * @return boolean
   */
  abstract public function logout();

  /**
   * @param   string $identity
   * @param   mixed  $credentials
   *
   * @return  \Cms\Access\Auth\Result
   */
  abstract public function checkLogin($identity, $credentials);

  /**
   * @return  boolean
   */
  abstract protected function validateRegisteredUserOnStart();

  /**
   * @param   array $identity
   *
   * @return  string|null
   */
  abstract public function getUserId(array $identity);

  /**
   * @param   array  $identity
   * @param   string $websiteId
   * @param   string $area
   * @param   string $privilege
   *
   * @return  boolean
   */
  abstract protected function checkWebsitePrivilegeForIdentity($identity, $websiteId, $area, $privilege);
}
