<?php
namespace Cms\Request\User;

use Cms\Request\Base;

/**
 * Optin Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class Optin extends Base
{
  /**
   * @var string
   */
  private $code;
  /**
   * @var string
   */
  private $password;
  /**
   * @var string
   */
  private $username;
  
  /**
   * @param mixed $code
   */
  public function setCode($code)
  {
    $this->code = $code;
  }
  /**
   * @return mixed
   */
  public function getCode()
  {
    return $this->code;
  }
  /**
   * @param mixed $password
   */
  public function setPassword($password)
  {
    $this->password = $password;
  }
  /**
   * @return mixed
   */
  public function getPassword()
  {
    return $this->password;
  }
  /**
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }
  /**
   * @param string $username
   */
  public function setUsername($username)
  {
    $this->username = $username;
  }

  protected function setValues()
  {
    $this->setCode($this->getRequestParam('code'));
    $this->setPassword($this->getRequestParam('password'));
    if ($this->getRequestParam('username') != '') {
      $this->setUsername($this->getRequestParam('username'));
    }
  }
}
