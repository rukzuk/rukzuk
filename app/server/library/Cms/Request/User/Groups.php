<?php
namespace Cms\Request\User;

use Cms\Request\Base;

/**
 * AddGroups Request
 *
 * @package      Cms
 * @subpackage   Request\User
 */
abstract class Groups extends Base
{
  /**
   * @var string
   */
  private $id;
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var array
   */
  private $groupIds;
  
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
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }
  /**
   * @param string $groupIds
   */
  public function setGroupIds($groupIds)
  {
    $this->groupIds = $groupIds;
  }
  /**
   * @return array
   */
  public function getGroupIds()
  {
    return $this->groupIds;
  }
  
  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setGroupIds($this->getRequestParam('groupids'));
  }
}
