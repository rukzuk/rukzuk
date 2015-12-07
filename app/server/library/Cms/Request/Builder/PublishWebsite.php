<?php
namespace Cms\Request\Builder;

use Cms\Request\Base;

/**
 * PublishWebsite
 *
 * @package      Cms
 * @subpackage   Request
 */
class PublishWebsite extends Base
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
    $this->setBuildId($this->getRequestParam('id'));
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
   * @param string $build
   */
  public function setBuildId($id)
  {
    $this->buildId = $id;
  }
  /**
   * @return string
   */
  public function getBuildId()
  {
    return $this->buildId;
  }
}
