<?php
namespace Cms\Request\Builder;

use Cms\Request\Base;

/**
 * GetWebsiteBuilds
 *
 * @package      Cms
 * @subpackage   Request
 */
class GetWebsiteBuilds extends Base
{
  /**
   * @var string
   */
  private $websiteId = null;

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
  }
  /**
   * @param string $id
   */
  public function setWebsiteId($id)
  {
    $this->websiteId = $id;
  }
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
}
