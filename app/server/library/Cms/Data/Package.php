<?php


namespace Cms\Data;

use Cms\Dao\Base\SourceItem;

/**
 * Class Package Data
 *
 * @package Cms\Data
 */
class Package
{
  /**
   * @var string $websiteid
   */
  private $websiteid;

  /**
   * @var string $id
   */
  private $id;

  /**
   * @var string $name
   */
  private $name;

  /**
   * @var string $description
   */
  private $description = '';

  /**
   * @var string $version
   */
  private $version = null;

  /**
   * @var array $websiteSettings
   */
  private $websiteSettings = array();

  /**
   * @var array
   */
  private $websiteSettingsSource = array();

  /**
   * @var array $pageTypes
   */
  private $pageTypes = array();

  /**
   * @var array
   */
  private $pageTypesSource = array();

  /**
   * @var array $templateSnippets
   */
  private $templateSnippets = array();

  /**
   * @var array
   */
  private $templateSnippetsSource = array();

  /**
   * @var array
   */
  private $modules = array();

  /**
   * @var array
   */
  private $modulesSource = array();

  /**
   * @var boolean $isReadonly
   */
  private $isReadonly = true;

  /**
   * @var string $sourceType
   */
  private $sourceType = null;

  /**
   * @var SourceItem $source
   */
  private $source = null;

  /**
   * @param string $websiteid
   *
   * @return Package
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
    return $this;
  }

  /**
   * @return string
   */
  public function getWebsiteid()
  {
    return $this->websiteid;
  }

  /**
   * @param string $id
   *
   * @return Package
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param string $name
   *
   * @return Package
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
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
   *
   * @return Package
   */
  public function setDescription($description)
  {
    $this->description = $description;
    return $this;
  }

  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set version
   *
   * @param string $version
   *
   * @return $this
   */
  public function setVersion($version)
  {
    $this->version = $version;
    return $this;
  }

  /**
   * Get version
   *
   * @return string
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * Set available website setting ids
   *
   * @param array $websiteSettings
   *
   * @return $this
   */
  public function setWebsiteSettings(array $websiteSettings)
  {
    $this->websiteSettings = $websiteSettings;
    return $this;
  }

  /**
   * Get available website setting ids
   *
   * @return array
   */
  public function getWebsiteSettings()
  {
    return $this->websiteSettings;
  }

  /**
   * Set available page type ids
   *
   * @param array $pageTypes
   *
   * @return $this
   */
  public function setPageTypes(array $pageTypes)
  {
    $this->pageTypes = $pageTypes;
    return $this;
  }

  /**
   * Get available page type ids
   *
   * @return array
   */
  public function getPageTypes()
  {
    return $this->pageTypes;
  }


  /**
   * @param array $pageTypesSource
   *
   * @return $this
   */
  public function setPageTypesSource($pageTypesSource)
  {
    $this->pageTypesSource = $pageTypesSource;
    return $this;
  }

  /**
   * @return array
   */
  public function getPageTypesSource()
  {
    return $this->pageTypesSource;
  }

  /**
   * @param boolean $isReadonly
   *
   * @return Package
   */
  public function setReadonly($isReadonly)
  {
    $this->isReadonly = (bool)$isReadonly;
    return $this;
  }

  /**
   * @return boolean
   */
  public function isReadonly()
  {
    return $this->isReadonly;
  }

  /**
   * @param string $sourceType
   *
   * @return Package
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
   * @param SourceItem $source
   *
   * @return Package
   */
  public function setSource(SourceItem $source)
  {
    $this->source = $source;
    return $this;
  }


  /**
   * @return SourceItem
   */
  public function getSource()
  {
    return $this->source;
  }

  /**
   * @param array $websiteSettingsSource
   *
   * @return $this
   */
  public function setWebsiteSettingsSource($websiteSettingsSource)
  {
    $this->websiteSettingsSource = $websiteSettingsSource;
    return $this;
  }
  /**
   * @return array
   */
  public function getWebsiteSettingsSource()
  {
    return $this->websiteSettingsSource;
  }

  /**
   * @param array $templateSnippets
   */
  public function setTemplateSnippets($templateSnippets)
  {
    $this->templateSnippets = $templateSnippets;
  }

  /**
   * @return array
   */
  public function getTemplateSnippets()
  {
    return $this->templateSnippets;
  }

  /**
   * @param array $templateSnippetsSource
   */
  public function setTemplateSnippetsSource($templateSnippetsSource)
  {
    $this->templateSnippetsSource = $templateSnippetsSource;
  }

  /**
   * @return array
   */
  public function getTemplateSnippetsSource()
  {
    return $this->templateSnippetsSource;
  }

  /**
   * @param array $modules
   */
  public function setModules($modules)
  {
    $this->modules = $modules;
  }

  /**
   * @return array
   */
  public function getModules()
  {
    return $this->modules;
  }

  /**
   * @param array $modulesSource
   */
  public function setModulesSource($modulesSource)
  {
    $this->modulesSource = $modulesSource;
  }

  /**
   * @return array
   */
  public function getModulesSource()
  {
    return $this->modulesSource;
  }
  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'websiteId' => $this->getWebsiteid(),
      'id' => $this->getId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'version' => $this->getVersion(),
      'websiteSettings' => $this->getWebsiteSettings(),
      'pageTypes' => $this->getPageTypes(),
      'templateSnippets' => $this->getTemplateSnippets(),
      'modules' => $this->getModules(),
      'readonly' => $this->isReadonly(),
      'sourceType' => $this->getSourceType(),
      'source' => $this->getSource(),
    );
  }
}
