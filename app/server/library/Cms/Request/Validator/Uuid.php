<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Uuid as Request;
use Cms\Request\Validator\Error;
use Cms\Validator\Integer as IntegerValidator;
use Seitenbau\Registry as Registry;
use Zend_Validate_GreaterThan as GreaterThanValidator;
use Zend_Validate_LessThan as LessThanValidator;
    
/**
 * Uuid request validator
 *
 * @package    Cms
 * @subpackage Request\Validator
 */

class Uuid extends Base
{
  /**
   * Validate the getuuids action request
   *
   * @param Cms\Request\Uuid\GetUuids $actionRequest
   */
  protected function validateMethodGetUuids(Request\GetUuids $actionRequest)
  {
    $this->validateCount($actionRequest->getCount());
  }
  /**
   * Validate the count value
   *
   * @param  mixed $count
   * @return boolean
   */
  private function validateCount($count)
  {
    $integerValidator = new IntegerValidator();
    $integerValidator->setMessage(
        "Count '%value%' ist keine Zahl",
        IntegerValidator::INVALID
    );

    if (!$integerValidator->isValid($count)) {
      $messages = array_values($integerValidator->getMessages());
      $this->addError(new Error('count', $count, $messages));
      return false;
    }

    // !! Achtung: Fehler im Zend Framework Version 1.11.0 !!
    // Der zu pruefende Wert muss groesser als der Parameter 'min' sein.
    // D.h. da der count Parameter mindestens den Wert 1 haben muss,
    // wird als 'min' 0 uebergeben
    $greaterThanValidator = new GreaterThanValidator(array('min' => 0));
    $greaterThanValidator->setMessage(
        "Count '%value%' ist nicht groesser als '%min%'",
        GreaterThanValidator::NOT_GREATER
    );

    if (!$greaterThanValidator->isValid($count)) {
      $messages = array_values($greaterThanValidator->getMessages());
      $this->addError(new Error('count', $count, $messages));
      return false;
    }

    $config = Registry::getConfig();
    $configuredUuidLimit = intval($config->uuid->limit);

    $lessThanValidator = new LessThanValidator(array(
      'max' => $configuredUuidLimit
    ));
    $lessThanValidator->setMessage(
        "Count '%value%' ist groesser als das konfigurierte uuid limit '%max%'",
        LessThanValidator::NOT_LESS
    );

    if (!$lessThanValidator->isValid($count)) {
      $messages = array_values($lessThanValidator->getMessages());
      $this->addError(new Error('count', $count, $messages));
      return false;
    }
    
    return true;
  }
}
