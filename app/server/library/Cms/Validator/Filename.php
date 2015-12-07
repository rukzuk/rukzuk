<?php
namespace Cms\Validator;

/**
 * Filename
 *
 * @package      Cms
 * @subpackage   Validator
 */
class Filename extends \Zend_Validate_Abstract
{
  const IS_EMPTY = 'isEmpty';
  const INVALID = 'invalid';

  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::IS_EMPTY => "File name is empty.",
    self::INVALID => "Invalid file name '%value%' given.",
  );

  /**
   * @param  string $value
   * @return boolean
   */
  public function isValid($value)
  {
    $this->_setValue($value);

    if (empty($value)) {
      $this->_error(self::IS_EMPTY);
      return false;
    }

    $nonAllowedChars = array(
      '?', '*', ':', ';', '\\', '/' ,'{', '}', '[', ']', '%'
    );

    foreach ($nonAllowedChars as $nonAllowedChar) {
      if (strpos($value, $nonAllowedChar) !== false) {
        $this->_error(self::INVALID);
        return false;
      }
    }

    return true;
  }
}
