<?php
namespace Cms\Validator;

use Seitenbau\Registry as Registry;
use Seitenbau\Config as Config;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Orm\Data\Page as DataPage;

/**
 * UserRight
 *
 * @package      Cms
 * @subpackage   Validator
 */
class UserRight extends \Zend_Validate_Abstract
{
  const INVALID_NO_OBJECT = 'noObjekt';
  const INVALID_NO_ARRAY_IDS = 'noArrayIds';
  const INVALID_AREA = 'invalidArea';
  const UNALLOWED_AREA = 'unallowedArea';
  const INVALID_PRIVILEGE = 'invalidPrivilege';
  const INVALID_IDS_NO_ARRAY = 'invalidIdsNoArray';
  const INVALID_HAS_NOT_ALL_REQUIRED_FIELDS = 'invalidHasNotAllRequireFields';
  const INVALID_PRIVILEGE_FOR_AREA = 'invalidPrivilegeForArea';
  const INVALID_IDS_PAGE_ID = 'invalidIdsPageId';
  
  /**
   * @var array
   */
  protected $allowedAreas;
  /**
   * @var array
   */
  protected $nonAllowedAreas;
  /**
   * @var array
   */
  protected $allowedPrivileges;
  
  /**
   * @var array
   */
  protected $rightMappings;
  
  /**
   * @var array
   */
  protected $requiredRightJsonFields;
  
  /**
   * @var array
   */
  protected $idableAreas;
  
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID_NO_OBJECT => "Recht ist kein Objekt",
    self::INVALID_AREA => "Ungueltiger Bereich '%value%'",
    self::UNALLOWED_AREA => "Unerlaubter Bereich '%value%'",
    self::INVALID_PRIVILEGE => "Ungueltiges Privileg '%value%'",
    self::INVALID_IDS_NO_ARRAY => "Ids ist kein Array",
    self::INVALID_HAS_NOT_ALL_REQUIRED_FIELDS => "Recht hat nicht die erwarteten Felder",
    self::INVALID_PRIVILEGE_FOR_AREA => "Ungueltiges Privileg '%value%' für Bereich",
    self::INVALID_NO_ARRAY_IDS => "Ids '%value%' ist kein Array",
    self::INVALID_IDS_PAGE_ID => "Ungueltige Page Id '%value%'",
  );
  /**
   * @param array $nonAllowedArea
   */
  public function __construct(array $nonAllowedAreas = array())
  {
    $this->nonAllowedAreas = $nonAllowedAreas;
    
    $this->idableAreas = array(
      'pages' => 'Page'
    );
    
    $this->idablePrivileges = array(
      'edit',
      'subAll',
      'subEdit'
    );
    
    $this->requiredRightJsonFields = array(
      'area',
      'privilege',
      'ids'
    );
    
    $config = Registry::getConfig();
    $rightsConfig = $config->group->rights;
    $rightsFromConfigAsArray = $rightsConfig->toArray();
    
    $this->rightMappings = $rightsFromConfigAsArray;
    $this->allowedAreas = array();
    $this->allowedPrivileges = array();
    
    if (count($rightsFromConfigAsArray) > 0) {
      $this->allowedAreas = array_keys($rightsFromConfigAsArray);
      
      $allowedPrivileges = array();
      
      foreach ($rightsFromConfigAsArray as $right) {
        $privileges = array_values($right);
        foreach ($privileges as $privilege) {
          if (!in_array($privilege, $allowedPrivileges)) {
            $allowedPrivileges[] = $privilege;
          }
        }
      }
      
      $this->allowedPrivileges = $allowedPrivileges;
    }
  }
  
  /**
   * @param  mixed $value
   * @return boolean
   */
  public function isValid($valueData)
  {
    $this->_setValue($valueData);

    if (!is_object($valueData)) {
      $this->_error(self::INVALID_NO_OBJECT);
      return false;
    }
    
    $value = get_object_vars($valueData);
    
    if (!$this->validateRightJsonFields($value)) {
      return false;
    }
    
    if (!$this->validateRightJsonAreaIsAllowed($value)) {
      return false;
    }
    
    if (!$this->validateRightJsonArea($value)) {
      return false;
    }
    
    if (!$this->validateRightJsonPrivilege($value)) {
      return false;
    }
    
    if (!$this->validateRightJsonPrivilegeForArea($value)) {
      return false;
    }
    
    if (!$this->validateRightJsonIdsForIdableAreas($value)) {
      return false;
    }
    
    return true;
  }
  /**
   * @param  array $right
   * @return boolean
   */
  private function validateRightJsonFields(array $right)
  {
    $actualRightJsonFields = array_keys($right);
    
    sort($actualRightJsonFields);
    sort($this->requiredRightJsonFields);
    
    if ($actualRightJsonFields !== $this->requiredRightJsonFields) {
      $this->_setValue($actualRightJsonFields);
      $this->_error(self::INVALID_HAS_NOT_ALL_REQUIRED_FIELDS);
      
      return false;
    }
    
    return true;
  }
  /**
   * @param  array $right
   * @return boolean
   */
  private function validateRightJsonAreaIsAllowed(array $right)
  {
    $actualRightJsonArea = $right['area'];
    
    if (in_array($actualRightJsonArea, $this->nonAllowedAreas)) {
      $this->_setValue($actualRightJsonArea);
      $this->_error(self::UNALLOWED_AREA);
      
      return false;
    }
    
    return true;
  }
  /**
   * @param  array $right
   * @return boolean
   */
  private function validateRightJsonArea(array $right)
  {
    $actualRightJsonArea = $right['area'];
    
    if (!in_array($actualRightJsonArea, $this->allowedAreas)) {
      $this->_setValue($actualRightJsonArea);
      $this->_error(self::INVALID_AREA);
      
      return false;
    }
    
    return true;
  }
  /**
   * @param  array $right
   * @return boolean
   */
  private function validateRightJsonPrivilege(array $right)
  {
    $actualRightJsonPrivilege = $right['privilege'];
    
    if (!in_array($actualRightJsonPrivilege, $this->allowedPrivileges)) {
      $this->_setValue($actualRightJsonPrivilege);
      $this->_error(self::INVALID_PRIVILEGE);
      
      return false;
    }
    
    return true;
  }
  /**
   * @param  array $right
   * @return boolean
   */
  private function validateRightJsonPrivilegeForArea(array $right)
  {
    $actualRightJsonPrivilege = $right['privilege'];
    $actualRightJsonArea = $right['area'];
    
    $allowedPrivilegesForArea = $this->rightMappings[$actualRightJsonArea];
    
    if (!in_array($actualRightJsonPrivilege, $allowedPrivilegesForArea)) {
      $this->_setValue($actualRightJsonPrivilege);
      $this->_error(self::INVALID_PRIVILEGE_FOR_AREA);
      
      return false;
    }
    
    return true;
  }
  /**
   * @param  array $right
   * @return boolean
   */
  private function validateRightJsonIdsForIdableAreas(array $right)
  {
    $actualRightJsonPrivilege = $right['privilege'];
    $actualRightJsonArea = $right['area'];
    $actualRightJsonIds = $right['ids'];
    
    $idableAreas = array_keys($this->idableAreas);
    
    if (!in_array($actualRightJsonArea, $idableAreas)) {
      return true;
    }

    if (!in_array($actualRightJsonPrivilege, $this->idablePrivileges)) {
      return true;
    }
    
    if (!is_array($actualRightJsonIds) || count($actualRightJsonIds) === 0) {
      $this->_setValue($actualRightJsonIds);
      $this->_error(self::INVALID_IDS_NO_ARRAY);
      return false;
    }
    
    $idValidator = new UniqueIdValidator(
        DataPage::ID_PREFIX,
        DataPage::ID_SUFFIX
    );
    
    foreach ($actualRightJsonIds as $id) {
      if (!$idValidator->isValid($id)) {
        $this->_setValue($id);
        $this->_error(self::INVALID_IDS_PAGE_ID);
        return false;
      }
    }
    
    return true;
  }
}
