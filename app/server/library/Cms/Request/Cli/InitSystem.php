<?php
namespace Cms\Request\Cli;

use Cms\Request\Base;
use Seitenbau\Types\Boolean as Boolean;

/**
 * InitSystem Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class InitSystem extends Base
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
   * @var string
   */
  private $sendregistermail = false;

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
    $this->language = $language;
  }
  /**
   * @return string
   */
  public function getLanguage()
  {
    return $this->language;
  }

  /**
   * @param boolean $sendregistermail
   */
  public function setSendregistermail($sendregistermail)
  {
    $this->sendregistermail = $sendregistermail;
  }
  /**
   * @return boolean
   */
  public function getSendregistermail()
  {
    $sendregistermailBoolean = new Boolean($this->sendregistermail);
    return $sendregistermailBoolean->getValue(Boolean::DOCTRINE_VALUE);
  }
  
  protected function setValues()
  {
    $this->setEmail($this->getRequestParam('email'));
    $this->setLastname($this->getRequestParam('lastname'));
    $this->setFirstname($this->getRequestParam('firstname'));
    if ($this->getRequestParam('gender') !== '') {
      $this->setGender($this->getRequestParam('gender'));
    }
    if ($this->getRequestParam('language') !== null) {
      $this->setLanguage($this->getRequestParam('language'));
    }
    if ($this->getRequestParam('sendregistermail') !== null) {
      $this->setSendregistermail($this->getRequestParam('sendregistermail'));
    }
  }
}
