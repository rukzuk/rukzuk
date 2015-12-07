<?php
namespace Cms\Request\User;

use Cms\Request\Base;

/**
 * ChangePassword Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class ChangePassword extends Base
{
  /**
   * @var string
   */
  private $userId;
  /**
   * @var string
   */
  private $oldPassword;
  /**
   * @var string
   */
  private $password;
  
  /**
   * @param mixed $id
   */
  public function setUserId($id)
  {
    $this->userId = $id;
  }
  /**
   * @return mixed
   */
  public function getUserId()
  {
    return $this->userId;
  }
  /**
   * @param mixed $password
   */
  public function setOldPassword($password)
  {
    $this->oldPassword = $password;
  }
  /**
   * @return mixed
   */
  public function getOldPassword()
  {
    return $this->oldPassword;
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
    $this->setUserId($this->getRequestParam('id'));
    $this->setOldPassword($this->getRequestParam('oldpassword'));
    $this->setPassword($this->getRequestParam('password'));
  }
}
