<?php
namespace Cms\Request\Validator;

use Cms\Request\Base as Request;
use Cms\Validator\RunId as RunIdValidator;
use Seitenbau\Registry;
use \Zend_Validate_EmailAddress as EmailValidator;
use Cms\Request\PropertyAccessException;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Orm\Data\Site as DataSite;
use Zend_Validate_StringLength as StringLengthValidator;

/**
 * request validator
 *
 * @package    Cms
 * @subpackage Request Validator
 */

abstract class Base
{
  protected $errors = array();

  protected $error;

  public function __construct()
  {
    $this->errors = array();
  }

  /**
   * validate a request object on basis of function-name(action)
   *
   * @param string $function
   * @param \Cms\Request\Abstract $actionRequest
   * @param boolean $setHttpErrorCode
   * @return true
   * @throws Exception
   */
  public function validate($function, Request $actionRequest, $abortExceptions = true)
  {
    $methodName = 'validateMethod' . $function;

    if (method_exists($this, $methodName)) {
      try {
        $this->$methodName($actionRequest);
      } catch (PropertyAccessException $e) {
        $message = str_replace('%name%', $e->getName(), $this->_('error.validation.missing_parameter'));
        $this->addError(new Error($e->getName(), null, array($message)));
      }

      if (count($this->getErrors()) > 0) {
        foreach ($this->getErrors() as $error) {
          \Cms\ExceptionStack::addException($error);
        }

        if (count(\Cms\ExceptionStack::getExceptions()) > 0) {
        // soll nur der Status-Code veraendert werden (Bsp: Rueckgabe Images)
          if ($abortExceptions == true) {
          // bisherige Fehler loggen und reset
            foreach (\Cms\ExceptionStack::getExceptions() as $exception) {
              \Seitenbau\Registry::getLogger()->logException(
                  __METHOD__,
                  __LINE__,
                  $exception,
                  \Seitenbau\Log::NOTICE
              );
            }
            //\Cms\ExceptionStack::reset();
            return false;
          } else {
            \Cms\ExceptionStack::throwErrors();
          }
        }
      }
      return true;
    } else {
      $data = array('method' => $methodName);
      throw new \Cms\Exception(-12, __METHOD__, __LINE__, $data);
    }
  }

  /**
   * get all errors
   *
   * @return array
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * set errors
   *
   * @param array $errors
   */
  public function setErrors(array $errors)
  {
    $this->errors = $errors;
  }

  /**
   * added one error
   *
   * @param string $error
   */
  public function addError(Error $error)
  {
    $this->errors[] = $error;
  }

  public function getError()
  {
    return $this->error;
  }
  
  protected function validateBoolean($value, $name)
  {
    $booleanValidator = new \Zend_Validate_InArray(array('1', '0'));

    if (!$booleanValidator->isValid($value)) {
      $messages = array_values($booleanValidator->getMessages());
      $this->addError(new Error($name, $value, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param  string $email
   * @param  string  $field
   * @return boolean
   */
  protected function validateEmail($email, $field = 'email')
  {
    $emailValidator = new EmailValidator(
        array('hostname' => new \Zend_Validate_Hostname(
            array('tld' => false)
        ))
    );
    $emailValidator->setMessage(
        'Ungültiger Typ. String erwartet',
        EmailValidator::INVALID
    );
    $emailValidator->setMessage(
        "'%value%' ist keine gültige Email nach dem Format name@hostname",
        EmailValidator::INVALID_FORMAT
    );
    $emailValidator->setMessage(
        "'%hostname%' ist kein gültiger Hostname für die Email '%value%'",
        EmailValidator::INVALID_HOSTNAME
    );
    $emailValidator->setMessage(
        "'%value%' ist länger als die zulässige Länge",
        EmailValidator::LENGTH_EXCEEDED
    );

    if (!$emailValidator->isValid($email)) {
      $messages = array_values($emailValidator->getMessages());
      $this->addError(new Error($field, $email, $messages));

      return false;
    }

    return true;
  }

  /**
   * @param string $runId
   * @param string $name
   * @return boolean
   */
  protected function validateRunId($runId, $name = 'runid')
  {
    $runIdValidator = new RunIdValidator();
    if (!$runIdValidator->isValid($runId)) {
      $messages = array_values($runIdValidator->getMessages());
      $this->addError(new Error('runid', $runId, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param string $id
   * @param string  $key
   *
   * @return boolean
   */
  protected function validateWebsiteId($id, $key = 'websiteid')
  {
    $websiteIdValidator = new UniqueIdValidator(
        DataSite::ID_PREFIX,
        DataSite::ID_SUFFIX
    );
    $websiteIdValidator->setMessage(
        $this->_('error.validation.base.websiteid.invalid'),
        UniqueIdValidator::INVALID
    );
    if (!$websiteIdValidator->isValid($id)) {
      $messages = array_values($websiteIdValidator->getMessages());
      $this->addError(new Error($key, $id, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param  string  $password
   * @param  string  $field
   * @return boolean
   */
  protected function validatePassword($password, $field)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => Registry::getConfig()->user->password->min,
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

  protected function _($key, $locale = null)
  {
    if (is_null($locale)) {
      $locale = Registry::getLocale('Zend_Translate');
    }
    return $this->getTranslator()->_($key, $locale);
  }

  protected function getTranslator()
  {
    return Registry::get('Zend_Translate');
  }
}
