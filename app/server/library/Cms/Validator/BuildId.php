<?php
namespace Cms\Validator;

/**
 * BuildId
 *
 * @package      Cms
 * @subpackage   Validator
 */
class BuildId extends \Zend_Validate_Abstract
{
  const INVALID = 'buildIdInvalid';
  const NO_ZERO_VERSION_ALLOWED = 'noZeroVersion';
  const NOT_A_STRING = 'notAString';
  const IS_EMPTY = 'isEmpty';
  
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::IS_EMPTY => "BuildId is empty.",
    self::INVALID => "Invalid buildId '%value%' given.",
    self::NO_ZERO_VERSION_ALLOWED => "Version '%value%' must be greater than zero.",
    self::NOT_A_STRING => "BuildId '%value%' not a string.",
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
    
    if (!is_string($value)) {
      $this->_error(self::NOT_A_STRING);
      return false;
    }

    if (strpos($value, '-') === false) {
      $this->_error(self::INVALID);
      return false;
    }
    
    if (substr($value, 0, 1) !== 'v') {
      $this->_error(self::INVALID);
      return false;
    }
    
    if (substr($value, 1, 1) === '0') {
      $this->_error(self::NO_ZERO_VERSION_ALLOWED);
      return false;
    }
    
    list($versionPart, $timestampPart) = explode('-', $value);
    
    if (!ctype_digit($timestampPart)) {
      $this->_error(self::INVALID);
      return false;
    }
    
    if (!ctype_digit(str_replace('v', '', $versionPart))) {
      $this->_error(self::INVALID);
      return false;
    }
    
    return true;
  }
}
