<?php
namespace Cms\Request\Group;

use Cms\Request\Base;

/**
 * GetAll Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class GetAll extends Base
{
  /**
   * @var string
   */
  private $websiteId;
  
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
  
  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
  }
}
