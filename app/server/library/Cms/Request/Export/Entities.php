<?php
namespace Cms\Request\Export;

use Cms\Request\Base;

/**
 * Entities export request
 *
 * @package      Cms
 * @subpackage   Request\Export
 */
abstract class Entities extends Base
{
  /**
   * @var array
   */
  private $ids = array();
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var string
   */
  private $exportName;

  protected function setValues()
  {
    $this->setIds($this->getRequestParam('ids'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setExportName($this->getRequestParam('name'));
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

  /**
   * @param string $name
   */
  public function setExportName($name)
  {
    $this->exportName = $name;
  }

  /**
   * @return string
   */
  public function getExportName()
  {
    return $this->exportName;
  }
}
