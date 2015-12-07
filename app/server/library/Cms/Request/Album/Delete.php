<?php
namespace Cms\Request\Album;

use Cms\Request\Base;

/**
 * Delete Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class Delete extends Base
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
  
  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
  }
}
