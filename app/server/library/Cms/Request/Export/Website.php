<?php
namespace Cms\Request\Export;

use Cms\Request\Base;
use Seitenbau\Types\Boolean as Boolean;

/**
 * Request object for Export Website
 *
 * @package      Cms
 * @subpackage   Request
 */
class Website extends Base
{
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var string
   */
  private $exportName;
  /**
   * @var boolean
   */
  private $complete = true;

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setExportName($this->getRequestParam('name'));
    if ($this->getRequestParam('complete') !== null) {
      $this->setComplete($this->getRequestParam('complete'));
    }
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
  /**
   * @param mixed $boolean
   */
  public function setComplete($boolean)
  {
    $this->complete = $boolean;
  }
  /**
   * @return boolean
   */
  public function getComplete()
  {
    return $this->complete;
  }
}
