<?php
namespace Cms\Response\Cli;

use Cms\Response\User;
use Cms\Response\IsResponseData;
use Seitenbau\Types\Boolean as Boolean;

/**
 * @package      Cms
 * @subpackage   Response
 */

class CheckLogin implements IsResponseData
{
  public $id = null;
  public $lastname = null;
  public $firstname = null;
  public $email = null;
  public $superuser = false;

  public function __construct($identity)
  {
    if (is_array($identity)) {
      $this->setId($identity['id']);
      $this->setLastname($identity['lastname']);
      $this->setFirstname($identity['firstname']);
      $this->setEmail($identity['email']);
      $this->setSuperuser($identity['superuser']);
    }
  }
  
  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
  
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
   * @param boolean $boolean
   */
  public function setSuperuser($boolean)
  {
    $boolean = new Boolean($boolean);
    $this->superuser = $boolean->getValue(Boolean::STRICT_VALUE);
  }
  
  /**
   * @return boolean
   */
  public function getSuperuser()
  {
    return $this->superuser;
  }
}
