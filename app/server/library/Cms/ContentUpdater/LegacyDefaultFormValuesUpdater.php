<?php


namespace Cms\ContentUpdater;

use Cms\Service\Modul as ModuleService;
use Seitenbau\FileSystem as FS;

/**
 * @package Cms\ContentUpdater
 */
class LegacyDefaultFormValuesUpdater extends DefaultFormValuesUpdater
{
  /**
   * @var array
   */
  private $legacyDefaultFormValues;

  /**
   * @param string $moduleId
   *
   * @return array
   */
  protected function getDefaultFormValuesOfModule($moduleId)
  {
    if (!is_array($this->legacyDefaultFormValues)) {
      $this->initLegacyDefaultFormValues();
    }
    if (!isset($this->legacyDefaultFormValues[$moduleId])) {
      return array();
    }
    return (array)$this->legacyDefaultFormValues[$moduleId];
  }

  /**
   * loads the legacy data
   */
  protected function initLegacyDefaultFormValues()
  {
    try {
      $legacyDataFile = FS::joinPath(__DIR__, 'legacyDefaultFormValues.json');
      if (!file_exists($legacyDataFile)) {
        $this->legacyDefaultFormValues = array();
        return;
      }
      $this->legacyDefaultFormValues = json_decode(FS::readContentFromFile($legacyDataFile));
      if (is_object($this->legacyDefaultFormValues)) {
        $this->legacyDefaultFormValues = (array)$this->legacyDefaultFormValues;
      }
      if (!is_array($this->legacyDefaultFormValues)) {
        $this->legacyDefaultFormValues = array();
        return;
      }
    } catch (\Exception $doNothing) {
      $this->legacyDefaultFormValues = array();
      return;
    }
  }
}
