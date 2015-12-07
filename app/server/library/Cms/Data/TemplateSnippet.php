<?php
namespace Cms\Data;

use Seitenbau\UniqueIdGenerator;
use Orm\Data\TemplateSnippet as DataTemplateSnippet;

/**
 * TemplateSnippet Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
 */
class TemplateSnippet
{
  const SOURCE_LOCAL = 'local';
  const SOURCE_REPOSITORY = 'repo';

  /**
   * @var string $id
   */
  private $id;

  /**
   * @var string $websiteid
   */
  private $websiteid;

  /**
   * @var string $name
   */
  private $name;

  /**
   * @var text $description
   */
  private $description = '';

  /**
   * @var text $category
   */
  private $category = '';

  /**
   * @var text $content
   */
  private $content = '';

  /**
   * @var boolean $isReadonly
   */
  private $isReadonly;

  /**
   * @var string $sourceType
   */
  private $sourceType = null;

  /**
   * @var bool
   */
  private $overwritten = false;

  /**
   * @var bool
   */
  private $baseLayout = false;

  /**
   * @var array
   */
  private $pageTypes = array();

  /**
   * @var string
   */
  private $previewImageUrl = null;

  /**
   * @var timestamp
   */
  private $lastupdate;

  /**
   * Set websiteid
   *
   * @param string $websiteid
   *
   * @return TemplateSnippet
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
    return $this;
  }

  /**
   * Get websiteid
   *
   * @return string
   */
  public function getWebsiteid()
  {
    return $this->websiteid;
  }

  /**
   * Set id
   *
   * @param string $id
   *
   * @return TemplateSnippet
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * Get id
   *
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set name
   *
   * @param string $name
   *
   * @return TemplateSnippet
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set description
   *
   * @param text $description
   *
   * @return TemplateSnippet
   */
  public function setDescription($description)
  {
    $this->description = $description;
    return $this;
  }

  /**
   * Get description
   *
   * @return text
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set category
   *
   * @param text $category
   *
   * @return TemplateSnippet
   */
  public function setCategory($category)
  {
    $this->category = $category;
    return $this;
  }

  /**
   * Get category
   *
   * @return text
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * Set content
   *
   * @param text $content
   *
   * @return TemplateSnippet
   */
  public function setContent($content)
  {
    $this->content = $content;
    return $this;
  }

  /**
   * Get content
   *
   * @return text
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * Set readonly flag
   *
   * @param boolean $isReadonly
   * @return TemplateSnippet
   */
  public function setReadonly($isReadonly)
  {
    $this->isReadonly = (bool)$isReadonly;
    return $this;
  }

  /**
   * Get readonly flag
   *
   * @return boolean
   */
  public function isReadonly()
  {
    return $this->isReadonly;
  }

  /**
   * @param string $sourceType
   *
   * @return TemplateSnippet
   */
  public function setSourceType($sourceType)
  {
    $this->sourceType = $sourceType;
    return $this;
  }

  /**
   * @return string
   */
  public function getSourceType()
  {
    return $this->sourceType;
  }

  /**
   * @param bool $overwritten
   *
   * @return TemplateSnippet
   */
  public function setOverwritten($overwritten)
  {
    $this->overwritten = (bool)$overwritten;
    return $this;
  }

  /**
   * @return boolean
   */
  public function isOverwritten()
  {
    return $this->overwritten;
  }

  /**
   * @param bool $isBaseLayout
   *
   * @return TemplateSnippet
   */
  public function setBaseLayout($isBaseLayout)
  {
    $this->baseLayout = (bool)$isBaseLayout;
    return $this;
  }

  /**
   * @return boolean
   */
  public function isBaseLayout()
  {
    return $this->baseLayout;
  }

  /**
   * @param array $pageTypes
   */
  public function setPageTypes($pageTypes)
  {
    $this->pageTypes = $pageTypes;
  }

  /**
   * @return array
   */
  public function getPageTypes()
  {
    return $this->pageTypes;
  }

  /**
   * @param string $previewImageUrl
   *
   * @return TemplateSnippet
   */
  public function setPreviewImageUrl($previewImageUrl)
  {
    $this->previewImageUrl = $previewImageUrl;
    return $this;
  }

  /**
   * @return string
   */
  public function getPreviewImageUrl()
  {
    return $this->previewImageUrl;
  }

  /**
   * Get lastupdate
   *
   * @return int
   */
  public function getLastupdate()
  {
    return $this->lastupdate;
  }

  /**
   * Set lastupdate
   *
   * @param int $lastupdate
   *
   * @return TemplateSnippet
   */
  public function setLastupdate($lastupdate)
  {
    $this->lastupdate = $lastupdate;
    return $this;
  }

  /**
   * Setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataTemplateSnippet::ID_PREFIX .
                UniqueIdGenerator::v4() .
                DataTemplateSnippet::ID_SUFFIX;
    return $this->getId();
  }

  /**
   * Liefert alle Columns und deren Values
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'websiteid' => $this->getWebsiteid(),
      'id' => $this->getId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'category' => $this->getCategory(),
      'content' => $this->getContent(),
      'readonly' => $this->isReadonly(),
      'sourcetype' => $this->getSourceType(),
      'overwritten' => $this->isOverwritten(),
      'baselayout' => $this->isBaseLayout(),
      'pagetypes' => $this->getPageTypes(),
      'previewimageurl' => $this->getPreviewImageUrl(),
      'lastupdate' => $this->getLastUpdate(),
    );
  }

  /**
   * Liefert die Columns und deren Values welche bei einem Export
   * beruecksichtigt weerden zurueck.
   *
   * @return array
   */
  public function getExportColumnsAndValues()
  {
    return array(
      'id' => $this->getId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'category' => $this->getCategory(),
      'content' => $this->getContent(),
      'baseLayout' => $this->isBaseLayout(),
      'pageTypes' => $this->getPageTypes(),
    );
  }
}
