<?php
namespace Cms\Request\User;

use Cms\Request\Base;

/**
 * Login Request
 *
 * @package      Cms
 * @subpackage   Request
 */

class Login extends Base
{
  /**
   * @var string
   */
  private $username;

  /**
   * @var string
   */
  private $password;

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

  /**
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * @param string $password
   */
  public function setPassword($password)
  {
    $this->password = $password;
  }

  protected function setValues()
  {
    $this->setUsername($this->getRequestParam('username'));
    $this->setPassword($this->getRequestParam('password'));
  }
}
