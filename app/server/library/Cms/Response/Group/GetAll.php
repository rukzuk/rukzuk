<?php
namespace Cms\Response\Group;

use Cms\Response;

/**
 * @package      Cms
 * @subpackage   Response
 */

class GetAll implements Response\IsResponseData
{
  /**
   * @var array
   */
  public $groups;
  /**
   * @param array $groups
   */
  public function __construct(array $groups = array())
  {
    $this->groups = array();
    $this->setGroups($groups);
  }
  
  /**
   * @param array $groups
   */
  protected function setGroups(array $groups)
  {
    foreach ($groups as $group) {
      $this->groups[] = new Response\Group($group);
    }
  }
  
  /**
   * @return array
   */
  public function getGroups()
  {
    return $this->groups;
  }
}
