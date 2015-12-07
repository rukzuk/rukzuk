<?php
namespace Cms\Request\Media;

use Cms\Request\Base;

/**
 * Request object for Media GetMultipleByIds
 *
 * @package      Cms
 * @subpackage   Request
 */

class GetMultipleByIds extends Base
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

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setIds($this->getRequestParam('ids'));
  }
}
