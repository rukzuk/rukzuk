<?php
namespace Cms\Response\User;

use Cms\Response\User;
use Cms\Response\IsResponseData;

/**
 * @package      Cms
 * @subpackage   Response
 */

class GetAll implements IsResponseData
{
  /**
   * @var array
   */
  public $users;
  
  /**
   * @param array $users
   */
  public function __construct(array $users = array())
  {
    $this->users = array();
    $this->setUsers($users);
  }
  
  /**
   * @param array $users
   */
  protected function setUsers(array $users)
  {
    foreach ($users as $user) {
      $this->users[] = new User($user);
    }
  }
  
  /**
   * @return array
   */
  public function getUsers()
  {
    return $this->users;
  }
}
