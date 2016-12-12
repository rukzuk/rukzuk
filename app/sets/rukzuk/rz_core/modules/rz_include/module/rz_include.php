<?php
namespace Rukzuk\Modules;

/**
 * Simple Include Module
 * @package Rukzuk\Modules
 */
class rz_include extends SimpleModule
{
  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $renderApi->renderChildren($unit);
  }
}
