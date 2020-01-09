<?php
namespace Cms\Response;

use Cms\Data\TemplateSnippet as TemplateSnippetData;
use Seitenbau\Registry;
use Cms\Response\IsResponseData;

/**
 * Einzelnes TemplateSnippet fuer Response-Darstellung
 *
 * @package      Cms
 * @subpackage   Response
 */

class TemplateSnippet implements IsResponseData
{
  /**
   * @var string
   */
  public $id = null;
  
  /**
   * @var string
   */
  public $websiteId = null;
  
  /**
   * @var string
   */
  public $name = null;
  
  /**
   * @var string
   */
  public $description = null;
  
  /**
   * @var string
   */
  public $category = null;
  
  /**
   * @var string
   */
  public $content;

  /**
   * @var boolean
   */
  public $readonly = null;

  /**
   * @var string
   */
  public $sourceType = null;

  /**
   * @var boolean
   */
  public $overwritten = false;

  /**
   * @var bool
   */
  public $baseLayout = false;

  /**
   * @var array
   */
  public $pageTypes = array();

  /**
   * @var string
   */
  public $previewImageUrl = null;

  /**
   * @param \Cms\Data\TemplateSnippet $data
   */
  public function __construct(TemplateSnippetData $data)
  {
    $this->setValuesFromData($data);
  }

  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Set description
   *
   * @param string $description
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }

  /**
   * Set category
   *
   * @param string $category
   */
  public function setCategory($category)
  {
    $this->category = $category;
  }

  /**
   * @param string $content
   */
  public function setContent($content)
  {
    if (is_string($content)) {
      $content = \Seitenbau\Json::decode($content, \Zend_Json::TYPE_OBJECT);
    }
    $this->content = $content;
  }

  /**
   * Set readonly flag
   *
   * @param boolean $isReadonly
   */
  public function setReadonly($isReadonly)
  {
    $this->readonly = (bool)$isReadonly;
  }

  /**
   * @param string $sourceType
   */
  public function setSourceType($sourceType)
  {
    $this->sourceType = $sourceType;
  }

  /**
   * @param bool $overwritten
   */
  public function setOverwritten($overwritten)
  {
    $this->overwritten = (bool)$overwritten;
  }

  /**
   * @param bool $isBaseLayout
   */
  public function setBaseLayout($isBaseLayout)
  {
    $this->baseLayout = (bool)$isBaseLayout;
  }

  /**
   * @param array $pageTypes
   */
  public function setPageTypes($pageTypes)
  {
    $this->pageTypes = $pageTypes;
  }

  /**
   * @param string $previewImageUrl
   */
  public function setPreviewImageUrl($previewImageUrl)
  {
    $this->previewImageUrl = $previewImageUrl;
  }

  /**
   * @param \Cms\Data\TemplateSnippet $data
   */
  protected function setValuesFromData(TemplateSnippetData $data)
  {
    $this->setWebsiteId($data->getWebsiteId());
    $this->setId($data->getId());
    $this->setName($data->getName());
    $this->setDescription($data->getDescription());
    $this->setCategory($data->getCategory());
    $this->setContent($data->getContent());
    $this->setReadonly($data->isReadonly());
    $this->setSourceType($data->getSourceType());
    $this->setOverwritten($data->isOverwritten());
    $this->setBaseLayout($data->isBaseLayout());
    $this->setPageTypes($data->getPageTypes());
    $this->setPreviewImageUrl($data->getPreviewImageUrl());
  }
}
