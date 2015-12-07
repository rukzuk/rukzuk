<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Shortener as Request;
use Orm\Data\Site as DataSite;
use Orm\Data\Template as DataTemplate;
use Orm\Data\Page as DataPage;
use Cms\Validator\TicketId as TicketIdValidator;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\Integer as IntegerValidator;
use Cms\Validator\Boolean as BooleanValidator;
use \Zend_Validate_StringLength as StringLengthValidator;
use \Zend_Validate_GreaterThan as GreaterThanValidator;
use \Zend_Validate_LessThan as LessThanValidator;
use Seitenbau\Types\Boolean as Boolean;
use Cms\Request\Validator\Error;

/**
 * Shortener request validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class Shortener extends Base
{
  private $expectedTypes = array('page', 'template');
  
  /**
   * @param Cms\Request\Shortener\Ticket $actionRequest
   */
  public function validateMethodTicket(Request\Ticket $actionRequest)
  {
    $this->validatetTicketId($actionRequest->getTicketId(), 'ticket');
  }

  /**
   * @param Cms\Request\Shortener\CreateRenderTicket $actionRequest
   */
  public function validateMethodCreateRenderTicket(Request\CreateRenderTicket $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId(), 'websiteid');
    $this->validateType($actionRequest->getType(), 'type');
    if ($actionRequest->getType() == 'template') {
      $this->validateTemplateId($actionRequest->getId(), 'id');
    } elseif ($actionRequest->getType() == 'page') {
      $this->validatePageId($actionRequest->getId(), 'id');
    }
    $this->validateIsBoolean($actionRequest->getProtect(), 'protect');
    if ($actionRequest->getCredentials() != null) {
      $this->validateCredentials($actionRequest->getCredentials(), 'credentials');
    }
    if ($actionRequest->getTicketLifetime() != null) {
      $this->validateInteger($actionRequest->getTicketLifetime(), 'ticketlifetime', array('min' => 0));
    }
    if ($actionRequest->getSessionLifetime() != null) {
      $this->validateInteger($actionRequest->getSessionLifetime(), 'sessionlifetime', array('min' => 0));
    }
    if ($actionRequest->getRemainingCalls() != null) {
      $this->validateInteger($actionRequest->getRemainingCalls(), 'remainingcalls', array('min' => 0));
    }
  }

  /**
   * @param string $id
   * @param string  $field
   * @return boolean
   */
  private function validatetTicketId($id, $field)
  {
    $ticketIdValidator = new TicketIdValidator();
    $ticketIdValidator->setMessage(
        "'".$id."' ist keine Ticket",
        TicketIdValidator::INVALID
    );

    if (!$ticketIdValidator->isValid($id)) {
      $messages = array_values($ticketIdValidator->getMessages());
      $this->addError(new Error($field, $id, $messages));
      
      return false;
    }
    return true;
  }

  /**
   * validiert die template id
   *
   * @param  string  $id
   * @param  string  $field
   * @return boolean
   */
  private function validateTemplateId($id, $fieldName)
  {
    $templateIdValidator = new UniqueIdValidator(
        DataTemplate::ID_PREFIX,
        DataTemplate::ID_SUFFIX
    );

    if (!$templateIdValidator->isValid($id)) {
      $messages = array_values($templateIdValidator->getMessages());
      $this->addError(new Error($fieldName, $id, $messages));
      return false;
    }
    return true;
  }

  /**
   * validiert die page id
   *
   * @param  string $id
   * @param  string  $field
   * @return boolean
   */
  private function validatePageId($id, $fieldName)
  {
    $idValidator = new UniqueIdValidator(
        DataPage::ID_PREFIX,
        DataPage::ID_SUFFIX
    );

    if (!$idValidator->isValid($id)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error($fieldName, $id, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param string  $type
   * @param string  $field
   * @return boolean
   */
  private function validateType($type, $fieldName)
  {
    $typeValidator = new \Zend_Validate_InArray($this->expectedTypes);
    $typeValidator->setMessage(
        "'%value%' ist kein unterstuetzter Typ",
        \Zend_Validate_InArray::NOT_IN_ARRAY
    );

    if (!$typeValidator->isValid($type)) {
      $messages = array_values($typeValidator->getMessages());
      $this->addError(new Error($fieldName, $type, $messages));

      return false;
    }
    return true;
  }
  
  /**
   * @param  boolean $value
   * @param  string  $field
   * @return boolean
   */
  private function validateIsBoolean($value, $field)
  {
    $boolean = new Boolean($value);
    $value = $boolean->getValue();
    
    $booleanValidator = new BooleanValidator();

    if (!$booleanValidator->isValid($value)) {
      $messages = array_values($booleanValidator->getMessages());
      $this->addError(new Error($field, $value, $messages));
      
      return false;
    }
    
    return true;
  }

  /**
   * @param  string $name
   * @param  string $field
   * @return boolean
   */
  private function validateCredentials($credentials, $fieldName)
  {
    $isValid = true;
    
    if (!($credentials instanceof \stdClass)) {
      $messages = array('Credentials muessen als Array uebermittelt werden');
      $this->addError(new Error($fieldName, null, $messages));
      return false;
    }
    
    if (!isset($credentials->username)) {
      $messages = array('username muss in den credential angegeben werden');
      $this->addError(new Error($fieldName, null, $messages));
      $isValid = false;
    } else {
      $stringLengthValidator = new StringLengthValidator(array(
        'min' => 1,
        'max' => 255
      ));
      $stringLengthValidator->setMessage(
          'Benutzername in den Zugangsdaten zu kurz',
          StringLengthValidator::TOO_SHORT
      );
      $stringLengthValidator->setMessage(
          'Benutzername in den Zugangsdaten zu lang',
          StringLengthValidator::TOO_LONG
      );

      if (!$stringLengthValidator->isValid(trim($credentials->username))) {
        $messages = array_values($stringLengthValidator->getMessages());
        $this->addError(new Error($fieldName, $credentials->username, $messages));
        $isValid = false;
      }
    }
    
    if (!isset($credentials->password)) {
      $messages = array('password muss in den credential angegeben werden');
      $this->addError(new Error($fieldName, null, $messages));
      $isValid = false;
    } else {
      $stringLengthValidator = new StringLengthValidator(array(
        'min' => 1,
        'max' => 255
      ));
      $stringLengthValidator->setMessage(
          'Passwort in den Zugangsdaten zu kurz',
          StringLengthValidator::TOO_SHORT
      );
      $stringLengthValidator->setMessage(
          'Passwort in den Zugangsdaten zu lang',
          StringLengthValidator::TOO_LONG
      );

      if (!$stringLengthValidator->isValid(trim($credentials->password))) {
        $messages = array_values($stringLengthValidator->getMessages());
        $this->addError(new Error($fieldName, '***', $messages));
        $isValid = false;
      }
    }
    
    return $isValid;
  }
  
  /**
   * @param  string  $value
   * @param  string  $field
   * @param  array   $limit
   * @return boolean
   */
  private function validateInteger($value, $field, $limit = array())
  {
    $integerValidator = new IntegerValidator();
    $integerValidator->setMessage(
        "'%value%' ist keine Zahl",
        IntegerValidator::INVALID
    );

    if (!$integerValidator->isValid($value)) {
      $messages = array_values($integerValidator->getMessages());
      $this->addError(new Error($field, $value, $messages));
      
      return false;
    }
    
    if (isset($limit['min'])) {
      $greaterThanValidator = new GreaterThanValidator($limit);
      $greaterThanValidator->setMessage(
          "'%value%' ist nicht größer oder gleich '%min%'",
          GreaterThanValidator::NOT_GREATER
      );
      if (!$greaterThanValidator->isValid($value)) {
        $messages = array_values($greaterThanValidator->getMessages());
        $this->addError(new Error($field, $value, $messages));
        return false;
      }
    }
    
    if (isset($limit['max'])) {
      $lessThanValidator = new LessThanValidator($limit);
      $lessThanValidator->setMessage(
          "'%value%' ist nicht kleiner oder gleich '%max%'",
          LessThanValidator::NOT_LESS
      );
      if (!$lessThanValidator->isValid($value)) {
        $messages = array_values($lessThanValidator->getMessages());
        $this->addError(new Error($field, $value, $messages));
        return false;
      }
    }
    
    return true;
  }
}
