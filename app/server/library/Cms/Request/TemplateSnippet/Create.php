<?php
namespace Cms\Request\TemplateSnippet;

use Cms\Request\Base;

/**
 * Request object for TemplateSnippet create
 *
 * @package      Cms
 * @subpackage   Request\TemplateSnippet
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
  private $description;
  /**
   * @var string
   */
  private $category;

  /**
   * @var string
   */
  private $content;

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setName($this->getRequestParam('name'));
    $this->setDescription($this->getRequestParam('description'));
    $this->setCategory($this->getRequestParam('category'));
    $this->setContent($this->getRequestParam('content'));
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
    $this->name = $name;
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $description
   */
  public function setDescription($description)
  {
    if ($description !== null) {
      $this->description = $description;
    }
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @param string $category
   */
  public function setCategory($category)
  {
    if ($category !== null) {
      $this->category = $category;
    }
  }
  /**
   * @return string
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * @param string $content
   */
  public function setContent($content)
  {
    if ($content !== null) {
      $this->content = $content;
    }
  }
  /**
   * @return string
   */
  public function getContent()
  {
    return $this->content;
  }
}
