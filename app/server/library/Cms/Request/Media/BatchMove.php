<?php
namespace Cms\Request\Media;

use Cms\Request\Base;

/**
 * BatchMove Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class BatchMove extends Base
{
  /**
   * @var string
   */
  private $ids;
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var string
   */
  private $albumId;
  
  /**
   * @param string $ids
   */
  public function setIds($ids)
  {
    $this->ids = $ids;
  }
  /**
   * @return string
   */
  public function getIds()
  {
    return $this->ids;
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
   * @param string $id
   */
  public function setAlbumId($id)
  {
    $this->albumId = $id;
  }
  /**
   * @return string
   */
  public function getAlbumId()
  {
    return $this->albumId;
  }
  protected function setValues()
  {
    $this->setIds($this->getRequestParam('ids'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setAlbumId($this->getRequestParam('albumid'));
  }
}
