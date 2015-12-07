<?php
namespace Cms\Validator;

use Orm\Iface\Data\IsUnit;

/**
 * Unit Array Validator
 *
 * @package      Application
 * @subpackage   Controller
 */

class UnitArray extends \Zend_Validate_Abstract
{
  const INVALID_NO_ARRAY = 'noArray';
  const INVALID_UNIT = 'invalidUnit';
  const INVALID_CONTENT = 'invalidContent';
  
  /**
   * @var IsUnit
   */
  protected $unitExample;
  
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID_CONTENT => "Content ist kein Array",
    self::INVALID_NO_ARRAY => "'%value%' ist kein Array",
    self::INVALID_UNIT => "ungueliges Unit Attribut '%value%'",
  );
  
  public function __construct(IsUnit $unit)
  {
    $this->unitExample = $unit;
  }
  
  /**
   * @param  mixed $value
   * @return boolean
   */
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!is_array($value)) {
      $this->_error(self::INVALID_CONTENT);
      return false;
    }
    
    return $this->isValidTree($value);
  }

  /**
   * @param  array $tree
   * @return boolean
   */
  protected function isValidTree($tree)
  {
    foreach ($tree as $unit) {
      foreach ($unit as $unitKey => $unitValue) {
        // Traverse the hole tree and not only the first level
        if (!$this->isValidProperty($unitKey)) {
          return false;
        }
        
        // Check if the element holds subcontent
        // List of key names that can hold sub elements of the tree
        $subtrees = $this->unitExample->getChildPropertiesNames();
        if (in_array($unitKey, $subtrees)) {
          if (!$this->isValidSubTree($unitValue)) {
            return false;
          }
        }
      }
    }
    return true;
  }
  
  /**
   * @param  mixed $value
   * @return boolean
   */
  protected function isValidProperty($propertyName)
  {
    if (!property_exists($this->unitExample, $propertyName)) {
      $this->_setValue($propertyName);
      $this->_error(self::INVALID_UNIT);
      return false;
    }
    return true;
  }

  protected function isValidSubTree($unitValue)
  {
    // Check that the subcontent is array or null
    if (!is_array($unitValue) && $unitValue !== null) {
      $this->_setValue($unitValue);
      $this->_error(self::INVALID_NO_ARRAY);
      return false;
    }
    
    if (is_array($unitValue) && count($unitValue) > 0) {
      // Check content
      if (!$this->isValidTree($unitValue)) {
        // When the subtree is invalid,
        // then the hole tree is invalid
        return false;
      }
    }
    return true;
  }
}
