<?php
namespace Cms\Validator;

/**
 * Boolean Validator
 *
 * @package      Application
 * @subpackage   Controller
 */

class Boolean extends \Zend_Validate_Abstract
{
  const INVALID_NO_BOOLEAN = 'noBoolean';
    
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID_NO_BOOLEAN => "'%value%' ist kein Boolean"
  );
  

  /**
   * @param boolean $typeSave
   */
  public function __construct()
  {
  }

  /**
   * @param  mixed $value
   * @return boolean
   */
  public function isValid($value)
  {
    $this->_setValue($value);

    if (!is_bool($value)) {
      $this->_error(self::INVALID_NO_BOOLEAN);
      return false;
    }
    
    return true;
  }
}
