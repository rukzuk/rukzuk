<?php


namespace Render;

use Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage;

/**
 * Module information representation.
 * Normally used as a API parameter.
 *
 * @package Render
 */
class ModuleInfo
{
  /**
   * @var IModuleInfoStorage
   */
  private $moduleInfoStorage;

  /**
   * @var string
   */
  private $moduleId;

  /**
   * @var array
   */
  private $manifest;

  /**
   * @var string
   */
  private $assetPath;

  /**
   * @var string
   */
  private $assetUrl;

  /**
   * @var mixed
   */
  private $customData;

  /**
   * @param IModuleInfoStorage $moduleInfoStorage
   * @param string             $moduleId
   */
  public function __construct(IModuleInfoStorage $moduleInfoStorage, $moduleId)
  {
    $this->moduleInfoStorage = $moduleInfoStorage;
    $this->moduleId = $moduleId;
  }

  /**
   * Returns the module id of this module
   *
   * @return string
   */
  public function getId()
  {
    return $this->moduleId;
  }

  /**
   * Returns the asset path of this module
   *
   * @param string|null $path  path that should be calculated
   *
   * @return string
   */
  public function getAssetPath($path = null)
  {
    if (is_null($this->assetPath)) {
      $this->assetPath = $this->getModuleInfoStorage()
        ->getModuleAssetPath($this->getId());
    }
    if (is_null($path)) {
      return $this->assetPath;
    } else {
      return $this->assetPath . DIRECTORY_SEPARATOR . $path;
    }
  }

  /**
   * Returns the asset url of this module
   *
   * @param string|null $path  path that url should be calculated
   *
   * @return string
   */
  public function getAssetUrl($path = null)
  {
    if (is_null($this->assetUrl)) {
      $this->assetUrl = $this->getModuleInfoStorage()
        ->getModuleAssetUrl($this->getId());
    }
    if (is_null($path)) {
      return $this->assetUrl;
    } else {
      return $this->assetUrl . '/' . $path;
    }
  }

  /**
   * Returns the manifest of this module
   *
   * @return array
   */
  public function getManifest()
  {
    if (is_null($this->manifest)) {
      $this->manifest = $this->getModuleInfoStorage()
        ->getModuleManifest($this->getId());
    }
    return $this->manifest;
  }

  /**
   * @return mixed
   */
  public function getCustomData()
  {
    if (is_null($this->customData)) {
      $this->customData = $this->getModuleInfoStorage()
        ->getModuleCustomData($this->getId());
    }
    return $this->customData;
  }

  /**
   * Returns TRUE if this module is a extension module; FALSE otherwise.
   *
   * @return boolean
   */
  public function isExtension()
  {
    $manifest = $this->getManifest();
    if (!isset($manifest['moduleType'])) {
      return false;
    }
    return ($manifest['moduleType'] == 'extension');
  }

  /**
   * @return \Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage
   */
  protected function getModuleInfoStorage()
  {
    return $this->moduleInfoStorage;
  }
}
