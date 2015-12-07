<?php
namespace Cms\Access;

use \Cms\Access\Acl\Factory as AclFactory;
use \Cms\Access\Acl\Base as AclBase;
use \Cms\Access\AuthBackend\Factory as AuthBackendFactory;
use \Seitenbau\Registry as Registry;
use \Seitenbau\Log as SbLog;

/**
 * @package      Cms
 * @subpackage   Access
 */
class Manager
{
  //const AUTH_BACKEND_DOCTRINE     = 'DoctrineAuthBackend';
  const AUTH_BACKEND_CMS = 'CmsAuthBackend';
  const AUTH_BACKEND_CLI = 'CliAuthBackend';
  const AUTH_BACKEND_TICKET = 'TicketAuthBackend';

  const AUTH_BACKEND_DEFAULT = self::AUTH_BACKEND_CMS;

  private static $instance;

  private $acl;
  private $auth;
  private $frontController;
  private $authBackends = array();

  protected function __construct()
  {
  }

  public static function isGroupCheckActiv()
  {
    return (Registry::getConfig()->group->check->activ == true);
  }

  public function hasIdentity()
  {
    return $this->getAuth()->hasIdentity();
  }

  public function clearIdentity()
  {
    if ($this->hasIdentity()) {
      $this->getAuth()->clearIdentity();
    }
  }

  public function getIdentityAsArray()
  {
    $identity = array();
    if ($this->hasIdentity()) {
      $authStorage = $this->getAuth()->getStorage()->read();
      if ($authStorage instanceof \Zend_Auth_Result) {
        $identity = $authStorage->getIdentity();
      }
    }
    return $identity;
  }

  public function setAuthResult(Auth\Result $result)
  {
    $this->getAuth()->getStorage()->write($result);
  }

  public function isAllowed($resource = null, $privilege = null)
  {
    if (!$this->getAcl()->has($resource)) {
      return false;
    }
    foreach ($this->getRoles() as $role) {
      if ($this->getAcl()->isAllowed($role, $resource, $privilege)) {
        return true;
      }
    }
    return false;
  }

  /**
   * checks if user is member of one or more groups for the given website id
   *
   * @param array $identity
   * @param array $websiteId
   *
   * @return boolean
   */
  public function isInAnyWebsiteGroup($identity, $websiteId)
  {
    foreach ($this->authBackends as $authBackend) {
      if ($authBackend->isInAnyWebsiteGroup($identity, $websiteId)) {
        return true;
      }
    }
    return false;
  }

  public function logout()
  {
    foreach ($this->authBackends as $authBackend) {
      if (!$this->hasIdentity()) {
        break;
      }
      $authBackend->logout();
    }
  }

  /**
   * @param   string $identity
   * @param   mixed  $credentials
   *
   * @throws \Cms\Exception
   * @return  \Cms\Access\Auth\Result
   */
  public function login($identity, $credentials)
  {
    $authResult = null;
    foreach ($this->authBackends as $authBackend) {
      $authResult = $authBackend->login($identity, $credentials);
      if ($this->isAuthResultValid($authResult)) {
        break;
      }
    }
    if (!$this->isAuthResultValid($authResult)) {
      throw new \Cms\Exception(6, __METHOD__, __LINE__);
    }
    $this->checkIfSpaceIsExpired($authResult);
    $this->setAuthResult($authResult);
    $this->setLastLogin(new \DateTime('now'));
    return $authResult;
  }

  /**
   * @param   string $identity
   * @param   mixed  $credentials
   *
   * @return  \Cms\Access\Auth\Result
   */
  public function checkLogin($identity, $credentials)
  {
    $authResult = null;
    foreach ($this->authBackends as $authBackend) {
      $authResult = $authBackend->checkLogin($identity, $credentials);
      if ($this->isAuthResultValid($authResult)) {
        break;
      }
    }
    $this->checkIfSpaceIsExpired($authResult);
    return $authResult;
  }

  public function isAuthResultValid($authResult)
  {
    return ($authResult !== null && $authResult->isValid());
  }

  public function isCliMode()
  {
    return \Cms\Version::isCliMode();
  }

  public function init(\Zend_Controller_Front $frontController, \Zend_Auth $auth)
  {
    $this->frontController = $frontController;
    $this->auth = $auth;
    $this->initAcl();
    $this->initAuthBackends();
    $this->initControllerPlugins();
  }

  protected function initAcl()
  {
    $this->acl = AclFactory::get();
  }

  protected function registerControllerPlugin(\Zend_Controller_Plugin_Abstract $controllerPlugin)
  {
    $frontController = $this->getFrontController();
    if (!$frontController->hasPlugin(get_class($controllerPlugin))) {
      $frontController->registerPlugin($controllerPlugin);
    }
  }

  protected function initAuthBackends()
  {
    if ($this->isCliMode()) {
      $this->addAuthBackend(AuthBackendFactory::get(self::AUTH_BACKEND_CLI, $this));
    }
    $this->addAuthBackend(AuthBackendFactory::get(self::AUTH_BACKEND_TICKET, $this));
    $this->addAuthBackend(AuthBackendFactory::get(self::AUTH_BACKEND_DEFAULT, $this));
  }

  protected function addAuthBackend(\Cms\Access\AuthBackend\Base $authBackend, $unique = true)
  {
    if (!$unique || !$this->hasAuthBackend(get_class($authBackend))) {
      $this->authBackends[] = $authBackend;
    }
  }

  protected function hasAuthBackend($class)
  {
    foreach ($this->authBackends as $authBackend) {
      $type = get_class($authBackend);
      if ($class == $type) {
        return true;
      }
    }
    return false;
  }

  protected function initControllerPlugins()
  {
    foreach ($this->authBackends as $authBackend) {
      foreach ($authBackend->getControllerPlugins() as $nextPlugin) {
        $this->registerControllerPlugin($nextPlugin);
      }
    }
    $this->registerControllerPlugin(new \Cms\Controller\Plugin\Auth\DefaultAuth());
  }

  /**
   * @param   array   $identity
   * @param   boolean $strict
   *
   * @return  boolean
   */
  public function isSuperuser(array $identity, $strict = false)
  {
    return $this->hasIdentityGivenRole($identity, AclBase::ROLE_SUPERUSER, $strict);
  }

  /**
   * @param   array   $identity
   * @param   boolean $strict
   *
   * @return  boolean
   */
  public function hasIdentityRoleTicket(array $identity, $strict = false)
  {
    return $this->hasIdentityGivenRole($identity, AclBase::ROLE_TICKETUSER, $strict);
  }

  /**
   * @param   array   $identity
   * @param   boolean $strict
   *
   * @return  boolean
   */
  public function hasIdentityRoleGuest(array $identity, $strict = false)
  {
    return $this->hasIdentityGivenRole($identity, AclBase::ROLE_GUEST, $strict);
  }

  /**
   * @return  array
   */
  public function getRoles()
  {
    return $this->getRolesForIdentity($this->getIdentityAsArray());
  }

  /**
   * @param   array $identity
   *
   * @return  array
   */
  protected function getRolesForIdentity(array $identity)
  {
    $roles = array();
    foreach ($this->authBackends as $authBackend) {
      $roles = array_merge($roles, $authBackend->getRoles($identity));
    }
    return array_unique($roles);
  }

  /**
   * @return  array
   */
  public function getWebsitePrivileges()
  {
    return $this->getWebsitePrivilegesForIdentity($this->getIdentityAsArray());
  }

  /**
   * @param   array $identity
   *
   * @return  array
   */
  protected function getWebsitePrivilegesForIdentity(array $identity)
  {
    $websitePrivileges = array();
    foreach ($this->authBackends as $authBackend) {
      $websitePrivileges = array_merge($websitePrivileges, $authBackend->getWebsitePrivileges($identity));
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
    if ($this->isSuperuser($identity)) {
      return true;
    }

    foreach ($this->authBackends as $authBackend) {
      if ($authBackend->checkWebsitePrivilegeForIdentity($identity, $websiteId, $area, $privilege)) {
        return true;
      }
    }
    return false;
  }

  /**
   * @param   array   $identity
   * @param   string  $role
   * @param   boolean $strict
   *
   * @return  boolean
   */
  protected function hasIdentityGivenRole(array $identity, $role, $strict = false)
  {
    try {
      $allIdentityRoles = $this->getRolesForIdentity($identity);
    } catch (\Exception $e) {
      return false;
    }

    $hasRole = in_array($role, $allIdentityRoles);
    if (!$hasRole) {
      return false;
    }

    if ($strict && $hasRole && count($allIdentityRoles) != 1) {
      return false;
    } else {
      return $hasRole;
    }
  }

  protected function getAcl()
  {
    return $this->acl;
  }

  protected function getAuth()
  {
    return $this->auth;
  }

  protected function getFrontController()
  {
    return $this->frontController;
  }

  /**
   * @param \Cms\Access\Auth\Result|null $authResult
   *
   * @throws \Cms\Exception
   */
  protected function checkIfSpaceIsExpired($authResult)
  {
    $quota = new \Cms\Quota();
    if (!$quota->isSpaceExpired()) {
      return;
    }

    $exceptionData = array();
    if ($authResult instanceof \Cms\Access\Auth\Result) {
      $identity = $authResult->getIdentity();
      if (isset($identity['owner']) && $identity['owner'] == true) {
        $cfg = Registry::getConfig();
        if (isset($cfg->owner) && isset($cfg->owner->dashboardUrl)) {
          $exceptionData['redirect'] = $cfg->owner->dashboardUrl;
        }
      }
    }
    throw new \Cms\Exception(9, __METHOD__, __LINE__, $exceptionData);
  }

  /**
   * @param \DateTime $lastLogin
   */
  protected function setLastLogin(\DateTime $lastLogin)
  {
    foreach ($this->authBackends as $authBackend) {
      try {
        $authBackend->setLastLogin($lastLogin);
      } catch (\Exception $logOnly) {
        Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
      }
    }
  }

  /**
   * @return PasswordHasher
   */
  public function getPasswordHasher()
  {
    return new PasswordHasher();
  }

  /**
   * @return \Cms\Access\Manager
   */
  public static function singleton()
  {
    if (!isset(self::$instance)) {
      $className = __CLASS__;
      self::$instance = new $className;
    }
    return self::$instance;
  }

  final public function __clone()
  {
    throw new \Exception('Cloning not allowed on a singelton.');
  }

  final public function __wakeup()
  {
    throw new \Exception('Deserializing not allowed on a singelton.');
  }
}
