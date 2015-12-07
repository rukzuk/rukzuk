<?php
namespace Cms\Request\Template;

use Cms\Request\Base;

/**
 * Request object for Template create
 *
 * @package      Cms
 * @subpackage   Request\Template
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
  private $name;

  /**
   * @var string
   */
  private $content;

  /**
   * @var string
   */
  private $pageType;


  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setName($this->getRequestParam('name'));
    $this->setContent($this->getRequestParam('content'));
    $this->setPageType($this->getRequestParam('pagetype'));
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

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = trim($name);
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $content
   */
  public function setContent($content)
  {
    $this->content = $content;
  }

  /**
   * @return string
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * @return string
   */
  public function getPageType()
  {
    return $this->pageType;
  }

  /**
   * @param string $pageType
   */
  public function setPageType($pageType)
  {
    $this->pageType = $pageType;
  }
}
