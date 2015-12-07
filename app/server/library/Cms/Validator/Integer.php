<?php
namespace Cms\Validator;

/**
 * Integer Validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class Integer extends \Zend_Validate_Abstract
{
  const INVALID = 'integerInvalid';
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => self::INVALID
  );
  /**
   * @param  mixed $value
   * @return boolean
   */
  public function isValid($value)
  {
    $this->_setValue($value);

    if (!is_int($value)) {
      if (!ctype_digit($value)) {
        $this->_error(self::INVALID);
        return false;
      }
      return true;
    }
    return true;
  }
}
