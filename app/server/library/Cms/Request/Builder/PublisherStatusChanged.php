<?php
namespace Cms\Request\Builder;

use Cms\Request\Base;

/**
 * PublisherStatusChanged
 *
 * @package      Cms
 * @subpackage   Request
 */
class PublisherStatusChanged extends Base
{
  /**
   * @var string
   */
  private $websiteId = null;
  /**
   * @var string
   */
  private $buildId = null;
  /**
   * @var string
   */
  private $publishedId = null;

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setBuildId($this->getRequestParam('buildid'));
    $this->setPublishedId($this->getRequestParam('publishedid'));
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
  /**
   * @param string $publishedId
   */
  public function setPublishedId($publishedId)
  {
    $this->publishedId = $publishedId;
  }
  /**
   * @return string
   */
  public function getPublishedId()
  {
    return $this->publishedId;
  }
}
