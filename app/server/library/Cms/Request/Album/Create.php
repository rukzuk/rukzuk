<?php
namespace Cms\Request\Album;

use Cms\Request\Base;

/**
 * Create Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class Create extends Base
{
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
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setName($this->getRequestParam('name'));
  }
}
