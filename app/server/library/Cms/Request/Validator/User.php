<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Error;
use Cms\Request\Validator\Base;
use Cms\Request\User as Request;
use Cms\Validator\Boolean as BooleanValidator;
use Cms\Validator\Gender as GenderValidator;
use Cms\Validator\IsArray as IsArrayValidator;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\UserId as UserIdValidator;
use Orm\Data\Group as DataGroup;
use Seitenbau\Registry as Registry;
use Seitenbau\Types\Boolean as Boolean;
use \Zend_Validate_StringLength as StringLengthValidator;
use \Zend_Validate_EmailAddress as EmailValidator;
use Seitenbau\Locale as SbLocale;
    
/**
 * User request validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class User extends Base
{
  /**
   * @param \Cms\Request\User\ChangePassword $actionRequest
   */
  public function validateMethodChangePassword(Request\ChangePassword $actionRequest)
  {
    $this->validateUserId($actionRequest->getUserId());
    $this->validatePassword($actionRequest->getPassword(), 'password');
    $this->validatePasswordForLogin($actionRequest->getOldPassword(), 'oldpassword');
  }
  /**
   * @param \Cms\Request\User\Optin $actionRequest
   */
  public function validateMethodOptin(Request\Optin $actionRequest)
  {
    $this->validateCode($actionRequest->getCode());
    $this->validatePassword($actionRequest->getPassword(), 'password');
    if ($actionRequest->getUsername() !== null) {
      $this->validateUsername($actionRequest->getUsername(), 'username');
    }
  }
  /**
   * @param \Cms\Request\User\ValidateOptin $actionRequest
   */
  public function validateMethodValidateOptin(Request\ValidateOptin $actionRequest)
  {
    $this->validateCode($actionRequest->getCode());
  }
  /**
   * @param \Cms\Request\User\RenewPassword $actionRequest
   */
  public function validateMethodRenewPassword(Request\RenewPassword $actionRequest)
  {
    $this->validateEmail($actionRequest->getEmail());
  }
  /**
   * @param \Cms\Request\User\Register $actionRequest
   */
  public function validateMethodRegister(Request\Register $actionRequest)
  {
    if ($this->validateIdsComeInAnArray($actionRequest->getUserIds(), 'Ids')) {
      foreach ($actionRequest->getUserIds() as $userId) {
        $this->validateUserId($userId);
      }
    }
  }
  
  /**
   * @param \Cms\Request\User\GetAll $actionRequest
   */
  public function validateMethodGetAll(Request\GetAll $actionRequest)
  {
    if ($actionRequest->getWebsiteId() !== null) {
      $this->validateWebsiteId($actionRequest->getWebsiteId());
    }
  }
  
  /**
   * @param \Cms\Request\User\AddGroups $actionRequest
   */
  public function validateMethodAddGroups(Request\AddGroups $actionRequest)
  {
    $this->validateUserId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    
    if ($this->validateIdsComeInAnArray($actionRequest->getGroupIds(), 'GroupIds')) {
      foreach ($actionRequest->getGroupIds() as $groupId) {
        $this->validateGroupId($groupId);
      }
    }
  }
  /**
   * @param \Cms\Request\User\RemoveGroups $actionRequest
   */
  public function validateMethodRemoveGroups(Request\RemoveGroups $actionRequest)
  {
    $this->validateUserId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    
    if ($this->validateIdsComeInAnArray($actionRequest->getGroupIds(), 'GroupIds')) {
      foreach ($actionRequest->getGroupIds() as $groupId) {
        $this->validateGroupId($groupId);
      }
    }
  }
  
  /**
   * @param \Cms\Request\User\GetById $actionRequest
   */
  public function validateMethodGetById(Request\GetById $actionRequest)
  {
    $this->validateUserId($actionRequest->getId());
  }
  /**
   * @param \Cms\Request\User\Delete $actionRequest
   */
  public function validateMethodDelete(Request\Delete $actionRequest)
  {
    $this->validateUserId($actionRequest->getId());
  }
  /**
   * @param \Cms\Request\User\Create $actionRequest
   */
  public function validateMethodCreate(Request\Create $actionRequest)
  {
    $this->validateEmail($actionRequest->getEmail());
    $this->validateName($actionRequest->getFirstname(), 'firstname');
    $this->validateName($actionRequest->getLastname(), 'lastname');
    if ($actionRequest->getGender() !== null) {
      $this->validateGender($actionRequest->getGender());
    }
    if ($actionRequest->getLanguage() !== null) {
      $this->validateLanguage($actionRequest->getLanguage(), 'language');
    }
    $this->validateIsBoolean($actionRequest->getIsSuperuser(), 'superuser');
    $this->validateIsBoolean($actionRequest->getIsDeletable(), 'deletable');
  }
  
  /**
   * @param \Cms\Request\User\Edit $actionRequest
   */
  public function validateMethodEdit(Request\Edit $actionRequest)
  {
    $this->validateUserId($actionRequest->getId());
    
    if ($actionRequest->getEmail() !== null) {
      $this->validateEmail($actionRequest->getEmail());
    }
    if ($actionRequest->getFirstname() !== null) {
      $this->validateName($actionRequest->getFirstname(), 'firstname');
    }
    if ($actionRequest->getLastname() !== null) {
      $this->validateName($actionRequest->getLastname(), 'lastname');
    }
    if ($actionRequest->getGender() !== null) {
      $this->validateGender($actionRequest->getGender());
    }
    if ($actionRequest->getLanguage() !== null) {
      $this->validateLanguage($actionRequest->getLanguage(), 'language');
    }
    if ($actionRequest->getPassword() !== null) {
      $this->validatePassword($actionRequest->getPassword(), 'password');
    }
    if ($actionRequest->getIsSuperuser() !== null) {
      $this->validateIsBoolean($actionRequest->getIsSuperuser(), 'superuser');
    }
    if ($actionRequest->getIsDeletable() !== null) {
      $this->validateIsBoolean($actionRequest->getIsDeletable(), 'deletable');
    }
  }
  
  /**
   * @param \Cms\Request\User\Login $actionRequest
   */
  public function validateMethodLogin(Request\Login $actionRequest)
  {
    $this->validateUsername($actionRequest->getUsername(), 'username');
    $this->validatePasswordForLogin($actionRequest->getPassword(), 'password');
  }
  
  /**
   * @param  mixed  $ids
   * @param  string $idsName
   * @return boolean
   */
  private function validateIdsComeInAnArray($ids, $idsName)
  {
    $isArrayValidator = new IsArrayValidator;
    $message = sprintf(
        "%s '%%value%%' sind kein Array",
        $idsName
    );
    $isArrayValidator->setMessage(
        $message,
        IsArrayValidator::INVALID_NO_ARRAY
    );
    $message = sprintf(
        "%s GroupIds '%%value%%' sind ein leerer Array",
        $idsName
    );
    $isArrayValidator->setMessage(
        $message,
        IsArrayValidator::INVALID_EMPTY_ARRAY
    );

    if (!$isArrayValidator->isValid($ids)) {
      $messages = array_values($isArrayValidator->getMessages());
      $lowercasedErrorfield = strtolower($idsName);
      $this->addError(new Error($lowercasedErrorfield, $ids, $messages));
      
      return false;
    }
    
    return true;
  }
  
  /**
   * @param  string  $id
   * @return boolean
   */
  private function validateUserId($id)
  {
    $userIdValidator = new UserIdValidator();

    if (!$userIdValidator->isValid($id)) {
      $messages = array_values($userIdValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));
      
      return false;
    }
    
    return true;
  }

  /**
   * @param string $id
   * @return boolean
   */
  private function validateGroupId($id)
  {
    $groupIdValidator = new UniqueIdValidator(
        DataGroup::ID_PREFIX,
        DataGroup::ID_SUFFIX
    );

    if (!$groupIdValidator->isValid($id)) {
      $messages = array_values($groupIdValidator->getMessages());
      $this->addError(new Error('groupid', $id, $messages));
      
      return false;
    }
    
    return true;
  }

  /**
   * @param  boolean $value
   * @param  string  $field
   * @return boolean
   */
  protected function validateIsBoolean($value, $field)
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
   * @param  string  $password
   * @param  string  $field
   * @return boolean
   */
  private function validatePasswordForLogin($password, $field)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 1,
      'max' => Registry::getConfig()->user->password->max
    ));
    $stringLengthValidator->setMessage(
        'Password zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Password zu lang',
        StringLengthValidator::TOO_LONG
    );

    if (!$stringLengthValidator->isValid(trim($password))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error($field, '...', $messages));
      
      return false;
    }
    
    return true;
  }
  
  /**
   * @param  char    $gender
   * qreturn boolean
   */
  private function validateGender($gender)
  {
    $genderValidator = new GenderValidator();

    if (!$genderValidator->isValid($gender)) {
      $messages = array_values($genderValidator->getMessages());
      $this->addError(new Error('gender', $gender, $messages));
      
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
      'min' => 2,
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
   * @param string $username
   * @param string $field
   * @return boolean
   */
  private function validateUsername($username, $field)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 1,
      'max' => 255
    ));
    $errorMessage = sprintf("Ungültiger Anmeldenamen", ucfirst($field));
    $stringLengthValidator->setMessage(
        $errorMessage,
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        $errorMessage,
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
    
    $stringLengthValidator->setMessage(
        "Ungültiger Option-Code",
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
   * @param string $description
   * @param string $fieldName
   * @return  boolean
   */
  private function validateLanguage($language, $fieldName)
  {
    if ($language == '') {
      return true;
    }
    
    if (SbLocale::isLocale($language)) {
      return true;
    }
    
    $this->addError(new Error($fieldName, $language, array(
      $this->_('error.validation.user.language.invalid')
    )));
    return false;
  }
}
