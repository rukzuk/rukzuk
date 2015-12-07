<?php
namespace Test\Rukzuk;

require_once(TEST_PATH.'/Helper/Test/Rukzuk/MediaItemMock.php');

use \Render\APIs\APIv1\Navigation;
use \Render\InfoStorage\NavigationInfoStorage\ArrayBasedNavigationInfoStorage;
use \Test\Rukzuk\MediaItemMock;
use \Render\APIs\APIv1\MediaItemNotFoundException;

class RenderApiMock extends CssApiMock
{
  protected $allUnitData;

  public function __construct ($conf = null, \PHPUnit_Framework_TestCase $testCase = null)
  {
    parent::__construct($conf, $testCase);
    $this->allUnitData = $this->getValue('allUnitData', $conf, null);
  }

  public function getAllUnitData()
  {
    if (is_array($this->allUnitData)) {
      return $this->allUnitData;
    }

    $this->allUnitData = array();
    foreach ($this->unitNodes as $unitNode) {
      $unit = $unitNode->unit;
      $module = $this->createModule($unit->getModuleId());
      $modulInfo = $this->getModuleInfo($unit);
      $this->allUnitData[$unit->getId()] = $module->provideUnitData($this, $unit, $modulInfo);
    }

    if (is_object($this->unit)) {
      $unitId = $this->unit->getId();
      $module = $this->createModule($this->unit->getModuleId());
      $modulInfo = $this->getModuleInfo($this->unit);
      $this->allUnitData[$unitId] = $module->provideUnitData($this, $this->unit, $modulInfo);
    }
    return $this->allUnitData;
  }

  public function renderChildren($unit)
  {
    // do nothing
  }

  public function getEditableTag($unit, $key, $tag, $attributes = '')
  {
    return "<$tag $attributes>{$this->getFormValue($unit, $key)}</$tag>";
  }
}

