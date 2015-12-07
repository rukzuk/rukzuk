<?php
namespace Cms\Request\Group;

use Cms\Request\Base;

/**
 * Base users request
 *
 * @package      Cms
 * @subpackage   Request\Group
 */
abstract class BaseUsers extends Base
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
  private $userIds;

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
   * @param string $userIds
   */
  public function setUserIds($userIds)
  {
    $this->userIds = $userIds;
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
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setUserIds($this->getRequestParam('userids'));
  }
}
