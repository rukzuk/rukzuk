<?php
namespace Cms\Validator;

/**
 * IsArray Validator
 *
 * @package      Application
 * @subpackage   Controller
 */
class IsArray extends \Zend_Validate_Abstract
{
  const INVALID_NO_ARRAY = 'noArray';
  const INVALID_EMPTY_ARRAY = 'emptyArray';
  /**
   * @var boolean
   */
  protected $considerEmptyArrayAsInvalid = true;

  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID_NO_ARRAY => "'%value%' ist kein Array",
    self::INVALID_EMPTY_ARRAY => "'%value%' ist ein leerer Array",
  );
  

  /**
   * @param boolean $considerEmptyArrayAsInvalid
   */
  public function __construct($considerEmptyArrayAsInvalid = true)
  {
    $this->considerEmptyArrayAsInvalid = $considerEmptyArrayAsInvalid;
  }

  /**
   * @param  mixed $value
   * @return boolean
   */
  public function isValid($value)
  {
    $this->_setValue($value);

    if (!is_array($value)) {
      $this->_error(self::INVALID_NO_ARRAY);
      return false;
    }
    if ($this->considerEmptyArrayAsInvalid && count($value) === 0) {
      $this->_error(self::INVALID_EMPTY_ARRAY);
      return false;
    }
    return true;
  }
}
