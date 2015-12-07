<?php
namespace Cms\Validator;

/**
 * JsonStructure Validator
 *
 * @package      Application
 * @subpackage   Controller
 */
class JsonStructure extends \Zend_Validate_Abstract
{
  const INVALID = 'noJsonStructure';
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => self::INVALID
  );
  /**
   * @param  string $value
   * @return boolean
   */
  public function isValid($value)
  {
    $this->_setValue($value);

    if (!is_string($value)) {
      $this->_error(self::INVALID);
      return false;
    }

    $result = json_decode($value, true);
    if (!is_array($result)) {
      $this->_error(self::INVALID);
      return false;
    }
    return true;
  }
}
