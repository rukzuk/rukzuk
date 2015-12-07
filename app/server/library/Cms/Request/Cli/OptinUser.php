<?php
namespace Cms\Request\Cli;

use Cms\Request\Base;

/**
 * Clie/Optin Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class OptinUser extends Base
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
  
  protected function setValues()
  {
    $this->setCode($this->getRequestParam('code'));
    $this->setPassword($this->getRequestParam('password'));
  }
}
