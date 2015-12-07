<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Log as Request;
use Orm\Data\Site as DataWebsite;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\Integer as IntegerValidator;
use Cms\Request\Validator\Error;
use Zend_Validate_GreaterThan as GreaterThanValidator;

/**
 * Log request validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class Log extends Base
{
  /**
   * @var array
   */
  private $acceptedFormats = array('txt', 'json');
  
  /**
   * @param Cms\Request\Log\Get $actionRequest
   */
  public function validateMethodGet(Request\Get $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($actionRequest->getFormat() !== null) {
      $this->validateFormat($actionRequest->getFormat());
    }
    if ($actionRequest->getLimit() !== null) {
      $this->validateLimit($actionRequest->getLimit());
    }
  }
  /**
   * @param  string  $limit
   * @return boolean
   */
  private function validateLimit($limit)
  {
    $integerValidator = new IntegerValidator();
    $integerValidator->setMessage(
        "Limit '%value%' ist keine Zahl",
        IntegerValidator::INVALID
    );

    if (!$integerValidator->isValid($limit)) {
      $messages = array_values($integerValidator->getMessages());
      $this->addError(new Error('limit', $limit, $messages));
      
      return false;
    }
    
    $greaterThanValidator = new GreaterThanValidator(array('min' => 0));
    $greaterThanValidator->setMessage(
        "Limit '%value%' ist nicht groesser als '%min%'",
        GreaterThanValidator::NOT_GREATER
    );

    if (!$greaterThanValidator->isValid($limit)) {
      $messages = array_values($greaterThanValidator->getMessages());
      $this->addError(new Error('limit', $limit, $messages));
      return false;
    }
    
    return true;
  }
  /**
   * @param  string  $format
   * @return boolean
   */
  private function validateFormat($format)
  {
    $formatValidator = new \Zend_Validate_InArray($this->acceptedFormats);
    $formatValidator->setMessage(
        "'%value%' ist kein unterstuetztes Format",
        \Zend_Validate_InArray::NOT_IN_ARRAY
    );
    
    if (!$formatValidator->isValid($format)) {
      $messages = array_values($formatValidator->getMessages());
      $this->addError(new Error('format', $format, $messages));
      
      return false;
    }
    return true;
  }
}
