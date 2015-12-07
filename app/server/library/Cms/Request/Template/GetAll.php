<?php
namespace Cms\Request\Template;

use Cms\Request\Base;

/**
 * Request object for Template GetAll
 *
 * @package      Cms
 * @subpackage   Request\Template
 */
class GetAll extends Base
{
  /**
   * @var string
   */
  private $websiteId;

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
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
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
}
