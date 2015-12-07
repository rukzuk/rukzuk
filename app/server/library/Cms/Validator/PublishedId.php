<?php
namespace Cms\Validator;

use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\BuildId as BuildIdValidator;
use Cms\Validator\Integer as IntegerValidator;

/**
 * PublishedId
 *
 * @package      Cms
 * @subpackage   Validator
 */
class PublishedId extends \Zend_Validate_Abstract
{
  const INVALID = 'publisehdIdInvalid';
  const NOT_A_STRING = 'notAString';
  const IS_EMPTY = 'isEmpty';
  
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::IS_EMPTY => "PublishedId is empty.",
    self::INVALID => "Invalid publishedId '%value%' given.",
    self::NOT_A_STRING => "PublishedId '%value%' not a string.",
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

    $parts = explode('.', $value);
    if (count($parts) != 3) {
      $this->_error(self::INVALID);
      return false;
    }
    
    $buildIdValidator = new BuildIdValidator();
    if (!$buildIdValidator->isValid($parts[0])) {
      $this->_error(self::INVALID);
      return false;
    }
    
    $integerValidator = new IntegerValidator();
    if (!$integerValidator->isValid($parts[1])) {
      $this->_error(self::INVALID);
      return false;
    }
    
    $uniqueIdValidator = new UniqueIdValidator('', '');
    if (!$uniqueIdValidator->isValid($parts[2])) {
      $this->_error(self::INVALID);
      return false;
    }

    return true;
  }
}
