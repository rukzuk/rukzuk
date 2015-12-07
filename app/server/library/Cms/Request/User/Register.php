<?php
namespace Cms\Request\User;

use Cms\Request\Base;

/**
 * Register Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class Register extends Base
{
  /**
   * @var array
   */
  private $userIds = array();
  
  /**
   * @param mixed $ids
   */
  public function setUserIds($ids)
  {
    $this->userIds = $ids;
  }
  /**
   * @return array
   */
  public function getUserIds()
  {
    return $this->userIds;
  }
  
  protected function setValues()
  {
    $this->setUserIds($this->getRequestParam('ids'));
  }
}
