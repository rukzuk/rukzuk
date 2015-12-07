<?php
namespace Rukzuk\Modules;

use \Render\ModuleInterface;

/**
 * @package Rukzuk\Modules
 */
class rz_testing_002 implements ModuleInterface
{
  /**
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param \Render\Unit                 $unit
   * @param \Render\ModuleInfo           $moduleInfo
   */
  public function render($api, $unit, $moduleInfo)
  {
    echo "START-RENDER:" . $unit->getId() . "\n";
    $api->renderChildren($unit);
    echo "END-RENDER:" . $unit->getId() . "\n";
  }

  /**
   * @param \Render\APIs\APIv1\CssAPI $api
   * @param \Render\Unit              $unit
   * @param \Render\ModuleInfo        $moduleInfo
   */
  public function css($api, $unit, $moduleInfo)
  {
    echo "START-CSS:" . $unit->getId() . "\n";
    echo "END-CSS:" . $unit->getId() . "\n";
  }

  /**
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo         $moduleInfo
   *
   * @return array
   */
  public function provideModuleData($api, $moduleInfo)
  {
    return array(
      'method' => __METHOD__,
      'module' => $moduleInfo->getId(),
    );
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
    return array(
      'method' => __METHOD__,
      'module' => $moduleInfo->getId(),
      'unit' => $unit->getId(),
    );
  }

}
