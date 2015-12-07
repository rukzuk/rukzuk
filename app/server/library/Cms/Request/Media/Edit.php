<?php
namespace Cms\Request\Media;

use Cms\Request\Base;

/**
 * Request object for Media Edit
 *
 * @package      Cms
 * @subpackage   Request
 */
class Edit extends Base
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
   * @var string
   */
  private $albumId;
  /**
   * @var string
   */
  private $name;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setAlbumId($this->getRequestParam('albumid'));
    $this->setName($this->getRequestParam('name'));
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
  public function getId()
  {
    return $this->id;
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
  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
}
