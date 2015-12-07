<?php
namespace Cms\Validator;

use Cms\Validator\UniqueId as UniqueIdValidator;
use Orm\Data as Unit;
use Seitenbau\Log as Log;
use Seitenbau\Registry as Registry;

/**
 * PageRights Validator.
 *
 * @package      Cms
 * @subpackage   Validator
 */
class PageRights extends \Zend_Validate_Abstract
{
  const INVALID_NO_OBJECT = 'noObject';
  const INVALID_NO_PAGEID = 'noPageId';
  const INVALID_NON_ALLOWED_PRIVILEGE = 'nonAllowedPrivilege';
  const INVALID_EMPTY_PRIVILEGE = 'emptyPrivilege';
  
  /**
   * @var array
   */
  protected $allowedPagePrivileges;
  
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID_NO_OBJECT => "kein Objekt",
    self::INVALID_NO_PAGEID => "'%value%' ist keine Page Id",
    self::INVALID_NON_ALLOWED_PRIVILEGE => "'%value%' mindestens ein Page Privileg ist nicht erlaubt",
    self::INVALID_EMPTY_PRIVILEGE => "'%value%' mindestens ein Page Privileg ist leer"
  );
  
  public function __construct()
  {
    $this->allowedPagePrivileges = array(
      'edit',
      'subAll',
      'subEdit'
    );
  }
  /**
   * @param  mixed $rights
   * @return boolean
   */
  public function isValid($rightsValue)
  {
    $this->_setValue($rightsValue);
    
    if (!is_object($rightsValue)) {
      $this->_error(self::INVALID_NO_OBJECT);
      return false;
    }
    
    $rights = get_object_vars($rightsValue);
    
    if (count($rights) === 0) {
      return true;
    }
    
    if (count($rights) > 0) {
      foreach ($rights as $pageId => $privileges) {
        if (!$this->validatePageId($pageId)) {
          $this->_setValue($pageId);
          $this->_error(self::INVALID_NO_PAGEID);
          return false;
        }
        if (!$this->validateNonEmptyPagePrivileges($privileges)) {
          $this->_setValue(implode('|', $privileges));
          $this->_error(self::INVALID_EMPTY_PRIVILEGE);
          return false;
        }
        if (!$this->validatePagePrivileges($privileges)) {
          $this->_setValue(implode('|', $privileges));
          $this->_error(self::INVALID_NON_ALLOWED_PRIVILEGE);
          return false;
        }
      }
    }
    
    return true;
  }
  /**
   * @param  string  $privileges
   * @return boolean
   */
  private function validateNonEmptyPagePrivileges($privileges)
  {
    if (!is_array($privileges)) {
      Registry::getLogger()->logData(
          __METHOD__,
          __LINE__,
          'Page privileges is not an array',
          $privileges,
          Log::ERR
      );
      return false;
    }
    if (count($privileges) === 0) {
      Registry::getLogger()->logData(
          __METHOD__,
          __LINE__,
          'Page privileges is an empty array',
          $privileges,
          Log::ERR
      );
      return false;
    }
    
    return true;
  }
  /**
   * @param  string  $privileges
   * @return boolean
   */
  private function validatePagePrivileges($privileges)
  {
    if (!is_array($privileges)) {
      Registry::getLogger()->logData(
          __METHOD__,
          __LINE__,
          'Page privileges is not an array',
          $privileges,
          Log::ERR
      );
      return false;
    }
    
    foreach ($privileges as $privilege) {
      if (!in_array($privilege, $this->allowedPagePrivileges)) {
        Registry::getLogger()->logData(
            __METHOD__,
            __LINE__,
            'Non allowed page privilege',
            $privilege,
            Log::ERR
        );
        $this->_setValue($privilege);
        return false;
      }
    }
    
    return true;
  }
  /**
   * @param  string  $pageId
   * @return boolean
   */
  private function validatePageId($pageId)
  {
    $pageIdValidator = new UniqueIdValidator(
        Unit\Page::ID_PREFIX,
        Unit\Page::ID_SUFFIX
    );

    if (!$pageIdValidator->isValid($pageId)) {
      Registry::getLogger()->logData(
          __METHOD__,
          __LINE__,
          'No page id in page rights',
          $pageId,
          Log::ERR
      );
      return false;
    }

    return true;
  }
}
