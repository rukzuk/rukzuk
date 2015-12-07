<?php


namespace Render;

interface ModuleInterface
{
  public function provideModuleData($api, $moduleInfo);

  public function provideUnitData($api, $unit, $moduleInfo);

  public function render($renderApi, $unit, $moduleInfo);

  public function css($cssApi, $unit, $moduleInfo);
}
