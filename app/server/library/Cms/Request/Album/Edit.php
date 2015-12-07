<?php
namespace Cms\Request\Album;

use Cms\Request\Base;

/**
 * Edit Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class Edit extends Base
{
  /**
   * @var string
   */
  private $id = null;
  /**
   * @var string
   */
  private $websiteId = null;

  /**
   * @var string
   */
  private $name = null;
  
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
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  
  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setName($this->getRequestParam('name'));
  }
}
