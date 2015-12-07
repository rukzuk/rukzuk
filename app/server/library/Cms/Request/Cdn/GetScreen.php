<?php
namespace Cms\Request\Cdn;

use Cms\Request\Base;

/**
 * Request object fuer Cdn getScreen
 *
 * @package      Cms
 * @subpackage   Request
 */

class GetScreen extends Base
{
  private $id;

  private $websiteId;

  private $type;

  private $width;

  private $height;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setType($this->getRequestParam('type'));
    $this->setWidth($this->getRequestParam('width'));
    $this->setHeight($this->getRequestParam('height'));
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getType()
  {
    return $this->type;
  }

  public function setType($type)
  {
    $this->type = $type;
  }

  public function getWidth()
  {
    return $this->width;
  }

  public function setWidth($width)
  {
    $this->width = $width;
  }

  public function getHeight()
  {
    return $this->height;
  }

  public function setHeight($height)
  {
    $this->height = $height;
  }
}
