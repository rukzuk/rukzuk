<?php


namespace Cms\Render\InfoStorage\ModuleInfoStorage;

use \Cms\Service\Modul as ModuleService;
use MyProject\Proxies\__CG__\stdClass;
use Render\InfoStorage\ModuleInfoStorage\Exceptions\ModuleDoesNotExists;
use Render\InfoStorage\ModuleInfoStorage\Exceptions;
use Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage;
use Render\InfoStorage\ModuleInfoStorage\of;
use \Seitenbau\FileSystem as SBFS;

class ServiceBasedModuleInfoStorage implements IModuleInfoStorage
{

  const MODULE_CLASS_FILE_ENDING = ".php";
  const MODULE_NAMESPACE = '\\Rukzuk\\Modules\\';

  private $moduleService;
  private $websiteId;
  private $cache = array();


  /**
   * @param string         $websiteId
   * @param ModuleService  $moduleService
   */
  public function __construct($websiteId, ModuleService $moduleService)
  {
    $this->websiteId = $websiteId;
    $this->moduleService = $moduleService;
    $this->cache = array(
      'mainClassFilePath' => array(),
      'manifest' => array(),
      'dataPath' => array(),
      'defaultFormValues' => array(),
      'customData' => array()
    );
  }

  /**
   * @param $moduleDataPath
   *
   * @return string
   */
  public function getModuleMainClassFilePath($moduleId)
  {
    if (!isset($this->cache['mainClassFilePath'][$moduleId])) {
      $moduleDataPath = $this->getModuleDataPath($moduleId);
      $moduleClassFileName = $this->getModuleMainClassFileName($moduleId);
      $this->cache['mainClassFilePath'][$moduleId] =
        SBFS::joinPath($moduleDataPath, $moduleClassFileName);
    }
    return $this->cache['mainClassFilePath'][$moduleId];
  }

  /**
   * @param  $moduleId
   *
   * @return string
   */
  public function getModuleClassName($moduleId)
  {
    $moduleClassName = $this->getModuleClassNameWithoutNamespace($moduleId);
    return self::MODULE_NAMESPACE . $moduleClassName;
  }

  /**
   * @param  $moduleId
   *
   * @return string
   */
  protected function getModuleMainClassFileName($moduleId)
  {
    $moduleClassName = $this->getModuleClassNameWithoutNamespace($moduleId);
    return strtolower($moduleClassName . self::MODULE_CLASS_FILE_ENDING);
  }

  /**
   * @param  $moduleId
   *
   * @return array
   */
  public function getModuleManifest($moduleId)
  {
    if (!isset($this->cache['manifest'][$moduleId])) {
      $module = $this->getModuleById($moduleId);
      $this->cache['manifest'][$moduleId] = json_decode(json_encode($module->getManifest()), true);
    }
    return $this->cache['manifest'][$moduleId];
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
   * @return ModuleService
   */
  protected function getModuleService()
  {
    return $this->moduleService;
  }

  /**
   * @return string
   */
  protected function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param  $moduleId
   *
   * @return string
   */
  protected function getModuleDataPath($moduleId)
  {
    $moduleService = $this->getModuleService();
    return $moduleService->getDataPath($this->getWebsiteId(), $moduleId);
  }

  /**
   * @param $moduleId
   *
   * @return mixed
   */
  protected function getModuleClassNameWithoutNamespace($moduleId)
  {
    // the module id is used as class name (remove invalid characters)
    return preg_replace(
        array('/^[^a-zA-Z_\x7f-\xff]+/','/[^a-zA-Z0-9_\x7f-\xff]/'),
        array('', ''),
        $moduleId
    );
  }

  /**
   * @param string $moduleId of the module
   *
   * @return string
   */
  public function getModuleCodePath($moduleId)
  {
    return $this->getModuleDataPath($moduleId);
  }

  /**
   * @param $moduleId of the module
   *
   * @return string
   */
  public function getModuleAssetPath($moduleId)
  {
    return $this->getModuleService()->getAssetsPath(
        $this->getWebsiteId(),
        $moduleId
    );
  }

  /**
   * @param $moduleId of the module
   *
   * @return string
   */
  public function getModuleAssetUrl($moduleId)
  {
    return $this->getModuleService()->getAssetsUrl(
        $this->getWebsiteId(),
        $moduleId
    );
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
    if (!isset($this->cache['defaultFormValues'][$moduleId])) {
      $module = $this->getModuleById($moduleId);
      $formValues = $module->getFormvalues();
      if (is_object($formValues)) {
        $this->cache['defaultFormValues'][$moduleId] = json_decode(json_encode($formValues), true);
      } else {
        $this->cache['defaultFormValues'][$moduleId] = $formValues;
      }
    }
    return $this->cache['defaultFormValues'][$moduleId];
  }

  /**
   * Returns the custom data of the specified module
   *
   * @param string $moduleId of the module
   *
   * @return array
   */
  public function getModuleCustomData($moduleId)
  {
    if (!isset($this->cache['customData'][$moduleId])) {
      $module = $this->getModuleById($moduleId);
      $customData = $module->getCustomData();
      $this->cache['customData'][$moduleId] = json_decode(json_encode($customData), true);
      if (!is_array($this->cache['customData'][$moduleId])) {
        $this->cache['customData'][$moduleId] = array();
      }
    }
    return $this->cache['customData'][$moduleId];
  }

  /**
   * @param string $moduleId
   *
   * @return \Cms\Data\Modul
   * @throws Exceptions\ModuleDoesNotExists
   */
  protected function getModuleById($moduleId)
  {
    try {
      return $this->getModuleService()->getById($moduleId, $this->getWebsiteId());
    } catch (\Exception $e) {
      throw new ModuleDoesNotExists();
    }
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
    if (isset($manifest['config'])) {
      return $manifest['config'];
    }
    return array();
  }
}
