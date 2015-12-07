<?php
namespace Cms\Access\Acl;

use \Seitenbau\Registry;

/**
 * Festlegung der Rechte fuer die einzelnen Rollen
 *
 * @package      Cms
 * @subpackage   Access\Auth
 */

abstract class Base extends \Zend_Acl
{
  const ROLE_GUEST                = 'guest';
  const ROLE_TICKETUSER           = 'ticketuser';
  const ROLE_USER                 = 'user';
  const ROLE_SUPERUSER            = 'superuser';
  const ROLE_CLIUSER              = 'cliuser';

  const RESOURCE_ALBUM            = 'album';
  const RESOURCE_BUILDER          = 'builder';
  const RESOURCE_CDN              = 'cdn';
  const RESOURCE_CLI              = 'cli';
  const RESOURCE_CREATOR          = 'creator';
  const RESOURCE_ERROR            = 'error';
  const RESOURCE_EXPORT           = 'export';
  const RESOURCE_FEEDBACK         = 'feedback';
  const RESOURCE_GROUP            = 'group';
  const RESOURCE_HEARTBEAT        = 'heartbeat';
  const RESOURCE_IMPORT           = 'import';
  const RESOURCE_INDEX            = 'index';
  const RESOURCE_LOCK             = 'lock';
  const RESOURCE_LOG              = 'log';
  const RESOURCE_LOGIN            = 'login';
  const RESOURCE_MAIL             = 'mail';
  const RESOURCE_MEDIA            = 'media';
  const RESOURCE_MODUL            = 'modul';
  const RESOURCE_PAGE             = 'page';
  const RESOURCE_RENDER           = 'render';
  const RESOURCE_REPARSE          = 'reparse';
  const RESOURCE_TEMPLATE         = 'template';
  const RESOURCE_TEMPLATESNIPPET  = 'templatesnippet';
  const RESOURCE_USER             = 'user';
  const RESOURCE_UUID             = 'uuid';
  const RESOURCE_WEBSITE          = 'website';
  const RESOURCE_SHORTENER        = 'shortener';
  const RESOURCE_WEBSITESETTINGS  = 'websitesettings';

  public function __construct()
  {
    // RESSOURCES
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_ALBUM));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_BUILDER));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_CDN));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_CREATOR));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_ERROR));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_EXPORT));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_FEEDBACK));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_GROUP));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_HEARTBEAT));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_IMPORT));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_INDEX));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_LOCK));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_LOG));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_LOGIN));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_MAIL));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_MEDIA));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_MODUL));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_PAGE));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_RENDER));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_REPARSE));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_CLI));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_TEMPLATE));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_TEMPLATESNIPPET));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_USER));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_UUID));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_WEBSITE));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_SHORTENER));
    $this->add(new \Zend_Acl_Resource(self::RESOURCE_WEBSITESETTINGS));
  }

  /**
   * @param string $role
   * @param array $groups
   */
  public function isAllowedToUse($role = null, array $groups = array(), $resource = null, $privilege = null)
  {
    return $this->getAcl()->isAllowed($role, $resource, $privilege);
  }

  /**
   * @see \Zend_Acl
   *
   * @param  Zend_Acl_Role_Interface|string     $role
   * @param  Zend_Acl_Resource_Interface|string $resource
   * @param  string                             $privilege
   * @param  array                              $group
   * @uses   Zend_Acl::get()
   * @uses   Zend_Acl_Role_Registry::get()
   * @return boolean
   */
  public function isAllowed($role = null, $resource = null, $privilege = null)
  {
    return parent::isAllowed($role, $resource, $privilege);
  }
}
