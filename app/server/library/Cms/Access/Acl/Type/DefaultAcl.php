<?php
namespace Cms\Access\Acl\Type;

use \Cms\Access\Acl\Base as AclBase;
use Seitenbau\Registry;

/**
 * Festlegung der Rechte fuer die einzelnen Rollen
 *
 * @package      Cms
 * @subpackage   Access\Acl
 */
class DefaultAcl extends AclBase
{
  public function __construct()
  {
    // all ressources added in base class constructor
    parent::__construct();

    // ROLES
    $this->addRole(new \Zend_Acl_Role(self::ROLE_GUEST));
    $this->addRole(new \Zend_Acl_Role(self::ROLE_TICKETUSER), self::ROLE_GUEST);
    $this->addRole(new \Zend_Acl_Role(self::ROLE_USER), self::ROLE_GUEST);
    $this->addRole(new \Zend_Acl_Role(self::ROLE_SUPERUSER), self::ROLE_USER);
    $this->addRole(new \Zend_Acl_Role(self::ROLE_CLIUSER));

    // RULES

    // deny for all users first
    $this->deny();
    
    $this->allow(self::ROLE_GUEST, self::RESOURCE_ERROR);

    $this->allow(self::ROLE_GUEST, self::RESOURCE_ERROR);
    $this->allow(self::ROLE_GUEST, self::RESOURCE_INDEX, array('index', 'info'));
    $this->allow(
        self::ROLE_GUEST,
        self::RESOURCE_USER,
        array('login', 'validateoptin', 'optin', 'renewpassword', 'logout')
    );
    $this->allow(self::ROLE_GUEST, self::RESOURCE_LOGIN, array('login'));
    $this->allow(self::ROLE_GUEST, self::RESOURCE_SHORTENER, array('ticket'));
    $this->allow(self::ROLE_GUEST, self::RESOURCE_BUILDER, array('publisherstatuschanged'));

    if (Registry::getConfig()->acl->render_as_guest === true) {
      $this->allow(self::ROLE_GUEST, self::RESOURCE_CDN, array('get'));
      $this->allow(self::ROLE_GUEST, self::RESOURCE_RENDER);
    }

    $this->allow(self::ROLE_TICKETUSER, self::RESOURCE_RENDER);
    $this->allow(self::ROLE_TICKETUSER, self::RESOURCE_CDN, array('get'));
    $this->allow(self::ROLE_TICKETUSER, self::RESOURCE_CREATOR);

    $this->allow(self::ROLE_USER);
    $this->allow(self::ROLE_SUPERUSER);

    $this->deny(null, self::RESOURCE_CLI);
    
    $this->allow(self::ROLE_CLIUSER, self::RESOURCE_ERROR);
    $this->allow(self::ROLE_CLIUSER, self::RESOURCE_CLI);
  }
}
