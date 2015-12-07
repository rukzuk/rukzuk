<?php
namespace Rukzuk\Modules;

use \Render\ModuleInterface;

/**
 * creator test global package default module
 *
 * @package Rukzuk\Modules
 */
class rz_tests_global_package_module implements ModuleInterface
{
  /**
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param \Render\Unit                 $unit
   * @param \Render\ModuleInfo           $moduleInfo
   */
  public function render($api, $unit, $moduleInfo)
  {
    echo "START-RENDER:" . $unit->getId() . "\n";

    echo "ASSET-PATH:" . $moduleInfo->getAssetPath('assetPath') . "\n";
    echo "ASSET-URL:" . $moduleInfo->getAssetUrl('assetUrl') . "\n";

    try {
      $url = $api->getMediaItem('ITEM-NOT-EXISTS')->getUrl();
    } catch (\Exception $ignore) {
      $url = '#exception';
    }
    echo "NOT-EXISTS-MEDIA-URL:" . $url . "\n";

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

    echo "ASSET-PATH:" . $moduleInfo->getAssetPath('assetPath') . "\n";
    echo "ASSET-URL:" . $moduleInfo->getAssetUrl('assetUrl') . "\n";

    try {
      $url = $api->getMediaItem('ITEM-NOT-EXISTS')->getUrl();
    } catch (\Exception $ignore) {
      $url = '#exception';
    }
    echo "NOT-EXISTS-MEDIA-URL:" . $url . "\n";

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
