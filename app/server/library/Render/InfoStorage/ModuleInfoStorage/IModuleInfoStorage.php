<?php


namespace Render\InfoStorage\ModuleInfoStorage;

interface IModuleInfoStorage
{
  const DEFAULT_MODULE_TYPE = '';

  /**
   * @param  string $moduleId
   *
   * @return string
   */
  public function getModuleMainClassFilePath($moduleId);

  /**
   * @param  string $moduleId
   *
   * @return string
   */
  public function getModuleClassName($moduleId);

  /**
   * @param  string $moduleId
   *
   * @return array
   */
  public function getModuleManifest($moduleId);

  /**
   * @param string $moduleId of the module
   *
   * @return string
   */
  public function getModuleApiType($moduleId);

  /**
   * @param string $moduleId of the module
   *
   * @return string
   */
  public function getModuleCodePath($moduleId);

  /**
   * @param string $moduleId of the module
   *
   * @return string
   */
  public function getModuleAssetPath($moduleId);

  /**
   * @param string $moduleId of the module
   *
   * @return string
   */
  public function getModuleAssetUrl($moduleId);

  /**
   * Returns the default form values of the specified module
   *
   * @param string $moduleId of the module
   *
   * @return array
   */
  public function getModuleDefaultFromValues($moduleId);

  /**
   * Returns the custom data of the specified module
   *
   * @param string $moduleId of the module
   *
   * @return mixed
   */
  public function getModuleCustomData($moduleId);
}
