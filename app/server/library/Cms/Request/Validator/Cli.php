<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Error;
use Cms\Request\Validator\Base;
use Cms\Request\Cli as Request;
use Seitenbau\Registry as Registry;
use Seitenbau\Types\Boolean as Boolean;
use Cms\Validator\Gender as GenderValidator;
use Cms\Validator\UserId as UserIdValidator;
use Cms\Validator\Boolean as BooleanValidator;
use \Zend_Validate_StringLength as StringLengthValidator;
    
/**
 * Cli request validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class Cli extends Base
{
  /**
   * @param \Cms\Request\Cli\CheckFtpLogin $actionRequest
   */
  public function validateMethodCheckFtpLogin(Request\CheckFtpLogin $actionRequest)
  {
    $this->validateUsername($actionRequest->getUsername());
    $this->validatePassword($actionRequest->getPassword(), 'password');
  }

  /**
   * @param \Cms\Request\Cli\CheckLogin $actionRequest
   */
  public function validateMethodCheckLogin(Request\CheckLogin $actionRequest)
  {
    $this->validateUsername($actionRequest->getUsername());
    $this->validatePassword($actionRequest->getPassword(), 'password');
  }
  
  /**
   * @param \Cms\Request\Cli\InitSystem $actionRequest
   */
  public function validateMethodInitSystem(Request\InitSystem $actionRequest)
  {
    if ($actionRequest->getEmail() !== null) {
      $this->validateEmail($actionRequest->getEmail());
      $this->validateName($actionRequest->getFirstname(), 'firstname');
      $this->validateName($actionRequest->getLastname(), 'lastname');
      if ($actionRequest->getGender() !== null) {
        $this->validateGender($actionRequest->getGender(), 'gender');
      }
      $this->validateIsBoolean($actionRequest->getSendregistermail(), 'sendregistermail');
    }
  }
  
  /**
   * @param \Cms\Request\Cli\RegisterUser $actionRequest
   */
  public function validateMethodRegisterUser(Request\RegisterUser $actionRequest)
  {
    $this->validateUserId($actionRequest->getId());
  }
  
  /**
   * @param \Cms\Request\Cli\OptinUser $actionRequest
   */
  public function validateMethodOptinUser(Request\OptinUser $actionRequest)
  {
    $this->validateCode($actionRequest->getCode());
    $this->validatePassword($actionRequest->getPassword(), 'password');
  }
   
  /**
   * @param \Cms\Request\Cli\UpdateSystem $actionRequest
   */
  public function validateMethodUpdateSystem(Request\UpdateSystem $actionRequest)
  {
    if ($actionRequest->getVersion() !== null) {
      $this->validateUpdateVersion($actionRequest->getVersion(), 'version');
    }
  }

  /**
   * @param \Cms\Request\Cli\BuildTheme $actionRequest
   */
  public function validateMethodBuildTheme(Request\BuildTheme $actionRequest)
  {
    if ($actionRequest->hasProperty('content')) {
      $this->validateThemeContent($actionRequest->getProperty('content'), 'content');
    }
  }

  /**
   * @param  string  $username
   * @param  string  $field
   * @return boolean
   */
  private function validateUsername($username, $field = 'username')
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 1,
      'max' => 255
    ));
    $stringLengthValidator->setMessage(
        'Benutzername zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Benutzername zu lang',
        StringLengthValidator::TOO_LONG
    );

    if (!$stringLengthValidator->isValid(trim($username))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error($field, $username, $messages));
      
      return false;
    }
    
    return true;
  }

  /**
   * @param  string  $gender
   * @param  string  $field
   * @return boolean
   */
  private function validateGender($gender, $field = 'gender')
  {
    $genderValidator = new GenderValidator();

    if (!$genderValidator->isValid($gender)) {
      $messages = array_values($genderValidator->getMessages());
      $this->addError(new Error($field, $gender, $messages));
      
      return false;
    }
    
    return true;
  }

  /**
   * @param  string  $name
   * @param  string  $field Feld fuer Validation Error
   * @return boolean
   */
  private function validateName($name, $field)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 1,
      'max' => 255
    ));
    $tooShortMessage = sprintf("Benutzer '%s' zu kurz", ucfirst($field));
    $stringLengthValidator->setMessage(
        $tooShortMessage,
        StringLengthValidator::TOO_SHORT
    );
    $tooLongMessage = sprintf("Benutzer '%s' zu lang", ucfirst($field));
    $stringLengthValidator->setMessage(
        $tooLongMessage,
        StringLengthValidator::TOO_LONG
    );

    if (!$stringLengthValidator->isValid(trim($name))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error($field, $name, $messages));
      
      return false;
    }
    
    return true;
  }
  
  /**
   * @param  string  $id
   * @param  string  $field
   * @return boolean
   */
  private function validateUserId($id, $field = 'id')
  {
    $userIdValidator = new UserIdValidator();

    if (!$userIdValidator->isValid($id)) {
      $messages = array_values($userIdValidator->getMessages());
      $this->addError(new Error('id', $field, $messages));
      
      return false;
    }
    
    return true;
  }
  
  /**
   * @param  mixed  $code
   * @return boolean
   */
  private function validateCode($code)
  {
    $configuredCodeLength = Registry::getConfig()->optin->code->length;
    
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => $configuredCodeLength,
      'max' => $configuredCodeLength
    ));
    
    $errorMessage = sprintf(
        "Code '%s' ist nicht %d Zeichen lang",
        $code,
        $configuredCodeLength
    );
    
    $stringLengthValidator->setMessage(
        $errorMessage,
        StringLengthValidator::TOO_SHORT
    );

    if ($code === null) {
      $code = str_repeat('x', $configuredCodeLength + 1);
    }
    
    if (!$stringLengthValidator->isValid(trim($code))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error('code', $code, $messages));
      
      return false;
    }
    
    return true;
  }
  
  /**
   * @param  string  $version
   * @param  string  $field
   * @return boolean
   */
  private function validateUpdateVersion($version, $field)
  {
    if (!preg_match('/^\d+$/', $version)) {
      $messages = array("Fehlerhafte Versionsformat '".$version."'");
      $this->addError(new Error($field, $version, $messages));
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
   * @param mixed $content
   * @param string $field
   *
   * @return bool
   */
  protected function validateThemeContent($content, $field)
  {
    if (!is_object($content) && !is_array($content)) {
      $messages = array('Invalid type given. Object or array expected');
      $this->addError(new Error($field, $content, $messages));
      return false;
    }
    return true;
  }
}
