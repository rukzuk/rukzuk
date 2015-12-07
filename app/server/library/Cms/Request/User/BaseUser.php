<?php
namespace Cms\Request\User;

use Seitenbau\Types\Boolean as Boolean;
use Seitenbau\Locale as SbLocale;

/**
 * BaseUser Request
 *
 * @package      Cms
 * @subpackage   Request\User
 */
abstract class BaseUser extends \Cms\Request\Base
{
  /**
   * @var string
   */
  private $lastname;
  /**
   * @var string
   */
  private $firstname;
  /**
   * @var char
   */
  private $gender;
  /**
   * @var string
   */
  private $email;
  /**
   * @var string
   */
  private $language;
  /**
   * @var boolean
   */
  private $issuperuser;
  /**
   * @var boolean
   */
  private $isdeletable;

  /**
   * @param string $lastname
   */
  public function setLastname($lastname)
  {
    $this->lastname = $lastname;
  }
  /**
   * @return string
   */
  public function getLastname()
  {
    return $this->lastname;
  }

  /**
   * @param string $firstname
   */
  public function setFirstname($firstname)
  {
    $this->firstname = $firstname;
  }
  /**
   * @return string
   */
  public function getFirstname()
  {
    return $this->firstname;
  }

  /**
   * @param char $gender
   */
  public function setGender($gender)
  {
    $this->gender = $gender;
  }
  /**
   * @return char
   */
  public function getGender()
  {
    return $this->gender;
  }

  /**
   * @param string $email
   */
  public function setEmail($email)
  {
    $this->email = $email;
  }
  /**
   * @return string
   */
  public function getEmail()
  {
    return $this->email;
  }
 
  /**
   * @param string $language
   */
  public function setLanguage($language)
  {
    if (SbLocale::isLocale($language)) {
      $local = new SbLocale($language);
      $this->language = $local->getLanguageCode();
    } else {
      $this->language = $language;
    }
  }
  /**
   * @return string
   */
  public function getLanguage()
  {
    return $this->language;
  }

  /**
   * @param boolean $boolean
   */
  public function setIsSuperuser($boolean)
  {
    $this->issuperuser = $boolean;
  }
  /**
   * @return boolean
   */
  public function getIsSuperuser()
  {
    if (is_null($this->issuperuser)) {
      return null;
    }
    $superuserBoolean = new Boolean($this->issuperuser);
    return $superuserBoolean->getValue(Boolean::DOCTRINE_VALUE);
  }
  /**
   * @param boolean $boolean
   */
  public function setIsDeletable($boolean)
  {
    $this->isdeletable = $boolean;
  }
  /**
   * @return boolean
   */
  public function getIsDeletable()
  {
    if (is_null($this->isdeletable)) {
      return null;
    }
    $deletableBoolean = new Boolean($this->isdeletable);
    return $deletableBoolean->getValue(Boolean::DOCTRINE_VALUE);
  }
  
  protected function setValues()
  {
    $this->setLastname($this->getRequestParam('lastname'));
    $this->setFirstname($this->getRequestParam('firstname'));
    if ($this->getRequestParam('gender') !== '') {
      $this->setGender($this->getRequestParam('gender'));
    }
    $this->setEmail($this->getRequestParam('email'));
    if ($this->getRequestParam('superuser') !== null) {
      $this->setIsSuperuser($this->getRequestParam('superuser'));
    }
    if ($this->getRequestParam('deletable') !== null) {
      $this->setIsDeletable($this->getRequestParam('deletable'));
    }
    if ($this->getRequestParam('language') !== null) {
      $this->setLanguage($this->getRequestParam('language'));
    }
  }
}
