<?php
namespace Cms\Request\Import;

use Cms\Request\Base;

/**
 * Request object for Import Overwrite
 *
 * @package      Cms
 * @subpackage   Request
 */
class Overwrite extends Base
{
  /**
   * @var string
   */
  private $importId;
  
  /**
   * @var array
   */
  private $templates = array();
  /**
   * @var array
   */
  private $modules = array();
  /**
   * @var array
   */
  private $templateSnippets = array();
  /**
   * @var array
   */
  private $media = array();
  
  /**
   * @param string $id
   */
  public function setImportId($id)
  {
    $this->importId = $id;
  }
  /**
   * @return string
   */
  public function getImportId()
  {
    return $this->importId;
  }
  /**
   *
   */
  public function setTemplates($templates)
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
   *
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
   *
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
   *
   */
  public function setMedia($media)
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
  
  protected function setValues()
  {
    $this->setImportId($this->getRequestParam('importid'));
    
    if ($this->getRequestParam('templates') !== null) {
      $this->templates = $this->getRequestParam('templates');
    }
    
    if ($this->getRequestParam('modules') !== null) {
      $this->modules = $this->getRequestParam('modules');
    }
    
    if ($this->getRequestParam('templatesnippets') !== null) {
      $this->templateSnippets = $this->getRequestParam('templatesnippets');
    }
    
    if ($this->getRequestParam('media') !== null) {
      $this->media = $this->getRequestParam('media');
    }
  }
}
