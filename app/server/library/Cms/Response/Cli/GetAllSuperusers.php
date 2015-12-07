<?php
namespace Cms\Response\Cli;

use Cms\Response\User;
use Cms\Response\IsResponseData;

/**
 * @package      Cms
 * @subpackage   Response
 */

class GetAllSuperusers implements IsResponseData
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
    $this->setSuperusers($users);
  }
  
  /**
   * @param array $users
   */
  protected function setSuperusers(array $users)
  {
    foreach ($users as $user) {
      $this->users[] = new User($user);
    }
  }
  
  /**
   * @return array
   */
  public function getSuperusers()
  {
    return $this->users;
  }
}
