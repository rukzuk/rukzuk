<?php
namespace Cms\Request\Group;

use Cms\Request\Base;

/**
 * GetPageRights Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class GetPageRights extends Base
{
  /**
   * @var string
   */
  private $websiteId;

  /**
   * @var string
   */
  private $id;

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

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setId($this->getRequestParam('id'));
  }
}
