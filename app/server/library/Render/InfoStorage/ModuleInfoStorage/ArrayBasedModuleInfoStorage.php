<?php


namespace Render\InfoStorage\ModuleInfoStorage;

use Render\InfoStorage\ModuleInfoStorage\Exceptions\ModuleDoesNotExists;
use Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage;

class ArrayBasedModuleInfoStorage implements IModuleInfoStorage
{

  /**
   * @var array
   */
  protected $moduleData;

  /**
   * @var string
   */
  protected $moduleBasePath;

  /**
   * @var string
   */
  protected $assetPath;

  /**
   * @var string
   */
  protected $assetWebPath;

  /**
   * @param array  $moduleData array(moduleId => array("mainClass" => ...))
   * @param string $moduleBasePath
   * @param string $assetPath
   * @param string $assetWebPath
   */
  public function __construct(
      array &$moduleData,
      $moduleBasePath = '',
      $assetPath = '',
      $assetWebPath = ''
  ) {
    $this->moduleData = $moduleData;
    $this->moduleBasePath = $moduleBasePath;
    $this->assetPath = $assetPath;
    $this->assetWebPath = $assetWebPath;
  }

  /**
   * @param  $moduleId
   *
   * @return string
   */
  public function getModuleMainClassFilePath($moduleId)
  {
    $data = $this->getModuleDataById($moduleId);
    return $this->moduleBasePath . '/' . $data["mainClassFilePath"];
  }

  /**
   * @param  $moduleId
   *
   * @return string
   */
  public function getModuleClassName($moduleId)
  {
    $data = $this->getModuleDataById($moduleId);
    return $data["mainClassName"];
  }

  /**
   * @param  $moduleId
   *
   * @return array
   */
  public function getModuleManifest($moduleId)
  {
    $data = $this->getModuleDataById($moduleId);
    return $data['manifest'];
  }

  /**
   * @param $moduleId of the module
   *
   * @return string
   */
  public function getModuleApiType($moduleId)
  {
    $manifest = $this->getModuleManifest($moduleId);
    return $manifest['apiType'];
  }

  /**
   * @param string $moduleId of the module
   *
   * @return string
   */
  public function getModuleCodePath($moduleId)
  {
    $data = $this->getModuleDataById($moduleId);
    return $this->moduleBasePath . '/' . $data["codePath"];
  }

  /**
   * @param $moduleId of the module
   *
   * @return string
   */
  public function getModuleAssetPath($moduleId)
  {
    return $this->assetPath . '/modules/' . $moduleId;
  }

  /**
   * @param $moduleId of the module
   *
   * @return string
   */
  public function getModuleAssetUrl($moduleId)
  {
    return $this->assetWebPath . '/modules/' . $moduleId;
  }

  /**
   * Returns the default form values of the specified module
   *
   * @param $moduleId of the module
   *
   * @return array
   */
  public function getModuleDefaultFromValues($moduleId)
  {
    $data = $this->getModuleDataById($moduleId);
    if (!isset($data['defaultFormValues'])) {
      return array();
    }
    return $data['defaultFormValues'];
  }

  /**
   * Returns the custom data of the specified module
   *
   * @param string $moduleId of the module
   *
   * @return mixed
   */
  public function getModuleCustomData($moduleId)
  {
    $data = $this->getModuleDataById($moduleId);
    return $data['customData'];
  }

  /**
   * @param string $moduleId
   *
   * @throws ModuleDoesNotExists
   * @return array
   */
  protected function getModuleDataById($moduleId)
  {
    if (!isset($this->moduleData[$moduleId])
      || !is_array($this->moduleData[$moduleId])) {
      throw new ModuleDoesNotExists();
    }
    return $this->moduleData[$moduleId];
  }

  /**
   * Returns the config data of the specified module
   *
   * @param string $moduleId of the module
   *
   * @return array
   */
  public function getModuleConfig($moduleId)
  {
    $manifest = $this->getModuleManifest($moduleId);
    return $manifest['config'];
  }
}
