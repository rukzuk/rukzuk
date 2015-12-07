<?php
namespace Cms\Request\TemplateSnippet;

use Cms\Request\Base;

/**
 * Request object for TemplateSnippet edit
 *
 * @package      Cms
 * @subpackage   Request\TemplateSnippet
 */

class Edit extends Base
{
  /**
   * @var string
   */
  private $runId;

  /**
   * @var string
   */
  private $websiteId = null;

  /**
   * @var string
   */
  private $id = null;

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
    $this->setRunId($this->getRequestParam('runid'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setId($this->getRequestParam('id'));
    $this->setName($this->getRequestParam('name'));
    $this->setDescription($this->getRequestParam('description'));
    $this->setCategory($this->getRequestParam('category'));
    $this->setContent($this->getRequestParam('content'));
  }

  /**
   * @param string $runId
   */
  public function setRunId($runId)
  {
    $this->runId = $runId;
  }
  /**
   * @return string
   */
  public function getRunId()
  {
    return $this->runId;
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
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return string
   */
  public function getId()
  {
    if ($this->id === "" || $this->id === 0) {
      return null;
    }
    return $this->id;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    if ($name !== null) {
      $this->name = $name;
    }
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
