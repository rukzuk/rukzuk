<?php
namespace Cms\Response;

/**
 * Import
 *
 * @package      Cms
 * @subpackage   Response
 */
class Import
{
  /**
   * @var array
   */
  public $modules;
  /**
   * @var array
   */
  public $templates;
  /**
   * @var array
   */
  public $pages;
  /**
   * @var array
   */
  public $media;
  /**
   * @var array
   */
  public $templatesnippets;
  /**
   * @var array
   */
  public $website;
  /**
   * @var array
   */
  public $websiteSettings;
  /**
   * @var array
   */
  public $packages;
  /**
   * @var null|
   */
  public $websiteId;

  /**
   * @param array $importData
   */
  public function __construct(array $importData)
  {
    $this->websiteId = isset($importData['websiteId']) ? $importData['websiteId'] : null;
    $this->modules = isset($importData['modules']) ? $importData['modules'] : array();
    $this->templates = isset($importData['templates']) ? $importData['templates'] : array();
    $this->pages = isset($importData['pages']) ? $importData['pages'] : array();
    $this->media = isset($importData['media']) ? $importData['media'] : array();
    $this->templatesnippets = isset($importData['templatesnippets']) ? $importData['templatesnippets'] : array();
    $this->websiteSettings = isset($importData['websitesettings']) ? $importData['websitesettings'] : array();
    $this->packages = isset($importData['packages']) ? $importData['packages'] : array();
    $this->website = isset($importData['website']) ? $importData['website'] : array();
  }
  /**
   * @param array $modules
   */
  public function setModules(array $modules)
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
   * @param array $templates
   */
  public function setTemplates(array $templates)
  {
    $this->templates = $templates;
  }
  /**
   * @return array
   */
  public function getTemplates()
  {
    return $this->templates;
  }
  /**
   * @param array $pages
   */
  public function setPages(array $pages)
  {
    $this->pages = $pages;
  }
  /**
   * @param array
   */
  public function getPages()
  {
    return $this->pages;
  }
  /**
   * @param array $media
   */
  public function setMedia(array $media)
  {
    $this->media = $media;
  }
  /**
   * @return array
   */
  public function getMedia()
  {
    return $this->media;
  }
  /**
   * @param array $templateSnippets
   */
  public function setTemplateSnippets(array $templateSnippets)
  {
    $this->templatessnippets = $templateSnippets;
  }
  /**
   * @return array
   */
  public function getTemplateSnippets()
  {
    return $this->templatesnippets;
  }
  /**
   * @param array $website
   */
  public function setWebsite(array $website)
  {
    $this->website = $website;
  }
  /**
   * @return array
   */
  public function getWebsite()
  {
    return $this->website;
  }

  /**
   * @return array
   */
  public function getWebsiteSettings()
  {
    return $this->websiteSettings;
  }

  /**
   * @param array $websiteSettings
   */
  public function setWebsiteSettings($websiteSettings)
  {
    $this->websiteSettings = $websiteSettings;
  }

  /**
   * @return array
   */
  public function getPackages()
  {
    return $this->packages;
  }
}
