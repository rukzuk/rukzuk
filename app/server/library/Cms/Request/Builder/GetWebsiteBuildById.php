<?php
namespace Cms\Request\Builder;

use Cms\Request\Base;

/**
 * GetWebsiteBuildById
 *
 * @package      Cms
 * @subpackage   Request
 */
class GetWebsiteBuildById extends Base
{
  /**
   * @var string
   */
  private $websiteId = null;
  /**
   * @var string
   */
  private $buildId = null;

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setBuildId($this->getRequestParam('buildid'));
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
  /**
   * @param string $buildId
   */
  public function setBuildId($buildId)
  {
    $this->buildId = $buildId;
  }
  /**
   * @return string
   */
  public function getBuildId()
  {
    return $this->buildId;
  }
}
