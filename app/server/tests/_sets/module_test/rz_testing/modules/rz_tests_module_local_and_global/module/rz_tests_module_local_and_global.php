<?php
namespace Rukzuk\Modules;

use \Render\ModuleInterface;

/**
 * module test default module
 *
 * @package Rukzuk\Modules
 */
class rz_tests_module_local_and_global implements ModuleInterface
{
  /**
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param \Render\Unit                 $unit
   * @param \Render\ModuleInfo           $moduleInfo
   */
  public function render($api, $unit, $moduleInfo)
  {
  }

  /**
   * @param \Render\APIs\APIv1\CssAPI $api
   * @param \Render\Unit              $unit
   * @param \Render\ModuleInfo        $moduleInfo
   */
  public function css($api, $unit, $moduleInfo)
  {
  }

  /**
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo         $moduleInfo
   *
   * @return array
   */
  public function provideModuleData($api, $moduleInfo)
  {
    return array();
  }

  /**
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\Unit               $unit
   * @param \Render\ModuleInfo         $moduleInfo
   *
   * @return array
   */
  public function provideUnitData($api, $unit, $moduleInfo)
  {
    return array();
  }

}
