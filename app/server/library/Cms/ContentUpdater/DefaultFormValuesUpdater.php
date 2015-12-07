<?php


namespace Cms\ContentUpdater;

use Cms\Dao\Module\ModuleDoesNotExistsException;
use Cms\Service\Modul as ModuleService;
use Seitenbau\Registry;
use Seitenbau\Log as SbLog;

/**
 * @package Cms\ContentUpdater
 */
class DefaultFormValuesUpdater
{
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var ModuleService
   */
  private $moduleService;
  /**
   * @var array
   */
  private $defaultFormValues = array();
  /**
   * @var array
   */
  private $loggedNotExistingModuleIds = array();

  /**
   * @param string        $websiteId
   * @param ModuleService $moduleService
   */
  public function __construct($websiteId, $moduleService)
  {
    $this->websiteId = $websiteId;
    $this->moduleService = $moduleService;
  }

  /**
   * @param array $content
   */
  public function updateDefaultFormValues(&$content)
  {
    foreach ($content as &$unit) {
      $this->updateDefaultFormValuesOfUnit($unit);
      if (property_exists($unit, 'children') && is_array($unit->children)) {
        $this->updateDefaultFormValues($unit->children);
      }
      if (property_exists($unit, 'ghostChildren') && is_array($unit->ghostChildren)) {
        $this->updateDefaultFormValues($unit->ghostChildren);
      }
    }
  }

  /**
   * @param object $unit
   */
  protected function updateDefaultFormValuesOfUnit($unit)
  {
    if (!property_exists($unit, 'moduleId')) {
      return;
    }
    $defaultFormValuesAsArray = $this->getDefaultFormValuesOfModule($unit->moduleId);
    if (!property_exists($unit, 'formValues')) {
      $unit->formValues = (object)$defaultFormValuesAsArray;
      return;
    }
    if (is_object($unit->formValues)) {
      $unitFormValues = (array)$unit->formValues;
    } elseif (is_array($unit->formValues)) {
      $unitFormValues = $unit->formValues;
    } else {
      $unit->formValues = (object)$defaultFormValuesAsArray;
      return;
    }
    $unit->formValues = (object)array_replace($defaultFormValuesAsArray, $unitFormValues);
  }

  /**
   * @param string $moduleId
   *
   * @return array
   */
  protected function getDefaultFormValuesOfModule($moduleId)
  {
    if (isset($this->defaultFormValues[$moduleId])) {
      return $this->defaultFormValues[$moduleId];
    }
    try {
      $module = $this->getModuleService()->getById($moduleId, $this->websiteId);
      $defaultFormValues = $module->getFormvalues();
      if (!is_object($defaultFormValues)) {
        return array();
      }
    } catch (ModuleDoesNotExistsException $logOnly) {
      if (!isset($this->loggedNotExistingModuleIds[$moduleId])) {
        $this->loggedNotExistingModuleIds[$moduleId] = true;
        Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::NOTICE);
      }
      return array();
    } catch (\Exception $logOnly) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::WARN);
      return array();
    }
    $this->defaultFormValues[$moduleId] = (array)$defaultFormValues;
    return $this->defaultFormValues[$moduleId];
  }

  /**
   * @return ModuleService
   */
  protected function getModuleService()
  {
    return $this->moduleService;
  }
}
