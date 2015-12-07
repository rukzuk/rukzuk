<?php


namespace Test\Render;

use Render\ModuleInterface;

class SimpleTestModule implements ModuleInterface
{
  protected $calls = array();

  public function provideModuleData($api, $moduleInfo)
  {
    $this->calls[] = array(__METHOD__, $api, $moduleInfo);
    return array('moduleId' => $moduleInfo->getId());
  }

  public function provideUnitData($api, $unit, $moduleInfo)
  {
    $this->calls[] = array(__METHOD__, $api, $unit, $moduleInfo);
    return array('unitId' => $unit->getId());
  }

  public function render($renderApi, $unit, $moduleInfo)
  {
    $this->calls[] = array(__METHOD__, $renderApi, $unit, $moduleInfo);
  }

  public function css($cssApi, $unit, $moduleInfo)
  {
    $this->calls[] = array(__METHOD__, $cssApi, $unit, $moduleInfo);
    // Nothing to do here
  }

  /**
   * @return array
   */
  public function getCalls()
  {
    return $this->calls;
  }
}