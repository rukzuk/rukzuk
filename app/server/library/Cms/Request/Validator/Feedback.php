<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Feedback as Request;
use Cms\Request\Validator\Error;

/**
 * Feedback request validator
 *
 * @package    Cms
 * @subpackage Request\Validator
 */

class Feedback extends Base
{
  /**
   * validate the send action request
   *
   * @param Cms\Request\Feedback\Send $actionRequest
   */
  protected function validateMethodSend(Request\Send $actionRequest)
  {
    $this->validateBody($actionRequest->getBody());
    if ($this->validateSubject($actionRequest->getSubject()) != '') {
      $this->validateSubject($actionRequest->getSubject());
    }
    if ($actionRequest->getEmail() != '') {
      $this->validateEmail($actionRequest->getEmail());
    }
    if ($actionRequest->getClientErrors() != '') {
      $this->validateClientErrors($actionRequest->getClientErrors());
    }
    if ($actionRequest->getUserAgent() != '') {
      $this->validateUserAgent($actionRequest->getUserAgent());
    }
    if ($actionRequest->getPlatform() != '') {
      $this->validatePlatform($actionRequest->getPlatform());
    }
  }
  
  /**
   * validate the subject
   *
   * @param string  $subject
   * @return boolean
   */
  private function validateSubject($subject)
  {
    // Keine Voraussetzungen fuer Validierung bekannt
    return true;
  }

  /**
   * validate the body
   *
   * @param string  $body
   * @return boolean
   */
  private function validateBody($body)
  {
    $notEmptyValidator = new \Zend_Validate_NotEmpty();
    
    if (!$notEmptyValidator->isValid($body)) {
      $messages = array_values($notEmptyValidator->getMessages());
      $this->addError(new Error('body', $body, $messages));
      return false;
    }
    
    return true;
  }
  
  /**
   * @param type $clientErrors
   */
  private function validateClientErrors($clientErrors)
  {
    $arrayValidator = new \Cms\Validator\IsArray(false);
    
    if (!$arrayValidator->isValid($clientErrors)) {
      $messages = array_values($arrayValidator->getMessages());
      $this->addError(new Error('errors', $clientErrors, $messages));
      return false;
    }
    
    return true;
  }
  
  private function validateUserAgent($userAgent)
  {
    $stringValidator = new \Zend_Validate_StringLength(array(
        'max' => 255, 'min' => 1
    ));
    
    if (!$stringValidator->isValid($userAgent)) {
      $messages = array_values($stringValidator->getMessages());
      $this->addError(new Error('useragent', $userAgent, $messages));
      return false;
    }
    return true;
  }
  
  private function validatePlatform($platform)
  {
    $stringValidator = new \Zend_Validate_StringLength(array(
        'max' => 255, 'min' => 1
    ));
    
    if (!$stringValidator->isValid($platform)) {
      $messages = array_values($stringValidator->getMessages());
      $this->addError(new Error('platform', $platform, $messages));
      return false;
    }
    return true;
  }
}
