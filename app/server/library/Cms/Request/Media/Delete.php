<?php
namespace Cms\Request\Media;

use Cms\Request\Base;

/**
 * Request object for Media Delete
 *
 * @package      Cms
 * @subpackage   Request
 */
class Delete extends Base
{
  /**
   * @var array
   */
  private $ids = array();

  /**
   * @var string
   */
  private $websiteId;


  protected function setValues()
  {
    $this->setIds($this->getRequestParam('ids'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
  }

  /**
   * @param mixed $ids
   */
  public function setIds($ids)
  {
    $this->ids = $ids;
  }
  /**
   * @return array
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
}
