<?php
namespace Cms\Render\InfoStorage;

use Render\InfoStorage\ColorInfoStorage\ArrayBasedColorInfoStorage;

/**
 * Class ServiceBasedColorInfoStorageHelper
 * Static Helper to init an Array based Color info storage with service data
 *
 * @package Cms\Render\InfoStorage
 */
class ServiceBasedColorInfoStorageHelper
{

  
  /**
   * @param $websiteService
   * @param string $websiteId
   *
   * @return ArrayBasedColorInfoStorage
   */
  public static function getColorInfoStorage($websiteService, $websiteId)
  {
    $colors = self::getColorsFromWebsite($websiteService, $websiteId);
    return new ArrayBasedColorInfoStorage($colors);
  }

  /**
   * @param $websiteService
   * @param string $websiteId
   *
   * @return array
   */
  protected static function getColorsFromWebsite($websiteService, $websiteId)
  {
    $colorScheme = self::getColorSchemeFromWebsite(
        $websiteService,
        $websiteId
    );
    $colors = array();
    if (is_array($colorScheme)) {
      foreach ($colorScheme as $color) {
        $colors[$color['id']] = $color;
      }
    }
    return $colors;
  }

  /**
   * @param $websiteService
   * @param string $websiteId
   *
   * @return mixed
   */
  protected static function getColorSchemeFromWebsite($websiteService, $websiteId)
  {
    $website = $websiteService->getById($websiteId);
    $colorScheme = $website->getColorscheme();
    if (is_string($colorScheme)) {
      $colorScheme = json_decode($colorScheme, true);
      return $colorScheme;
    }
    return $colorScheme;
  }
}
