<?php
namespace Cms\Response\Import;

use Cms\Response\IsResponseData;
use Cms\Data;

/**
 * Import conflict response
 *
 * @package      Cms
 * @subpackage   Response
 */
class Conflict implements IsResponseData
{
  /**
   * @var string
   */
  public $importId = null;
  /**
   * @var type
   */
  public $conflict = array();
  /**
   * @param array $conflictData
   */
  public function __construct(array $conflictData)
  {
    $this->conflict = array(
      'templates' => array(),
      'modules' => array(),
      'media' => array(),
      'templatesnippets' => array(),
    );
    
    if (isset($conflictData['importId'])) {
      $this->importId = $conflictData['importId'];
    }
    if (isset($conflictData['conflict']['templates'])) {
      $this->conflict['templates'] = $conflictData['conflict']['templates'];
    }
    if (isset($conflictData['conflict']['modules'])) {
      $this->conflict['modules'] = $conflictData['conflict']['modules'];
    }
    if (isset($conflictData['conflict']['media'])) {
      $this->conflict['media'] = $conflictData['conflict']['media'];
    }
    if (isset($conflictData['conflict']['templatesnippets'])) {
      $this->conflict['templatesnippets'] = $conflictData['conflict']['templatesnippets'];
    }
  }
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
   * @param array $templates
   */
  public function setConflictingTemplates(array $templates)
  {
    $this->conflict['templates'] = $templates;
  }
  /**
   * @return array
   */
  public function getConflictingTemplates()
  {
    return $this->conflict['templates'];
  }
  /**
   * @param array $modules
   */
  public function setConflictingModules(array $modules)
  {
    $this->conflict['modules'] = $modules;
  }
  /**
   * @return array
   */
  public function getConflictingModules()
  {
    return $this->conflict['modules'];
  }
  /**
   * @param array $media
   */
  public function setConflictingMedia(array $media)
  {
    $this->conflict['media'] = $media;
  }
  /**
   * @return array
   */
  public function getConflictingMedia()
  {
    return $this->conflict['media'];
  }
  /**
   * @param array $templates
   */
  public function setConflictingTemplateSnippets(array $templateSnippets)
  {
    $this->conflict['templatesnippets'] = $templateSnippets;
  }
  /**
   * @return array
   */
  public function getConflictingTemplateSnippets()
  {
    return $this->conflict['templatesnippets'];
  }
}
