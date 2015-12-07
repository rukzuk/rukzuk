<?php
namespace Cms\Request\User;

/**
 * Edit Request
 *
 * @package      Cms
 * @subpackage   Request\User
 */
class Edit extends BaseUser
{
  /**
   * @var string
   */
  private $id;

  /**
   * @var string
   */
  private $password;

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @param string $password
   */
  public function setPassword($password)
  {
    $this->password = $password;
  }

  /**
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setPassword($this->getRequestParam('password'));
    parent::setValues();
  }
}
