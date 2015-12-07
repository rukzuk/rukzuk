<?php
namespace Cms\Request\User;

use Cms\Request\Base;

/**
 * RenewPassword Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class RenewPassword extends Base
{
  /**
   * @var string
   */
  private $email;
  
  /**
   * @param mixed $email
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
  
  protected function setValues()
  {
    $this->setEmail($this->getRequestParam('email'));
  }
}
