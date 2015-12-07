<?php
namespace Cms\Request\TemplateSnippet;

use Cms\Request\Base;

/**
 * Request object for TemplateSnippet GetAll
 *
 * @package      Cms
 * @subpackage   Request\TemplateSnippet
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
