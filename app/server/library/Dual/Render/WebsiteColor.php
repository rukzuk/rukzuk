<?php
namespace Dual\Render;

/**
 * WebsiteColor
 *
 * @package      Dual
 * @subpackage   Render
 */

class WebsiteColor
{
  const COLOR_RGBA = 'RGBA';
  const COLOR_RGB = 'RGB';

  const ID_PREFIX = 'COLOR-';
  const ID_SUFFIX = '-COLOR';

  private function __construct()
  {
  }

  /**
   * RGBA Farbe in ein anderes Format umwandeln
   *
   * @param   string $color RGBA Farbestring
   * @param   string $type  Typ der gewuenschten Farbe
   *
   * @return string   Umgewandelte Farbe
   * @access public
   */
  public static function convertColorTo($color, $type = self::COLOR_RGBA)
  {
    // Farbtyp beachten
    switch ($type) {
      // als RGB zurueckgeben
      case self::COLOR_RGB:
        // RGB aus RGBA ermittelt
            return preg_replace(
                '/^\s*rgba\(([0-9]*),([0-9]*),([0-9]*),([0-9\.]*)\)\s*$/i',
                'rgb($1,$2,$3)',
                $color
            );
        break;

      // wieder als RGBA zurueckgeben
      default:
            return $color;
        break;
    }

    // Nichts zum Zurueckgeben
    return;
  }

  public static function getBaseIdFromColorId($colorId)
  {
    $searchNewId = '/(\-\-).*?(\-' . preg_quote(self::ID_SUFFIX, '/') . ')$/';
    $count = 0;
    $colorId = preg_replace($searchNewId, '${1}000000000000${2}', $colorId, -1, $count);
    if ($count == 0) {
      $searchOldId = '/([^-])(' . preg_quote(self::ID_SUFFIX, '/') . ')$/';
      $colorId = preg_replace($searchOldId, '${1}--000000000000-${2}', $colorId);
    }

    return $colorId;
  }

  public static function extendIdWithFallbackColor($colorId, $colorValue)
  {
    $fallbackColor = self::createFallbackColor($colorValue);
    $baseId = self::getBaseIdFromColorId($colorId);

    $search = '/(\-\-).*?(\-' . preg_quote(self::ID_SUFFIX, '/') . ')$/';
    $replace = '${1}' . $fallbackColor . '${2}';
    return preg_replace($search, $replace, $baseId);
  }

  public static function getFallbackColorFormColorId($colorId)
  {
    $search = '/\-\-(.*?)\-' . preg_quote(self::ID_SUFFIX, '/') . '$/';
    if (preg_match($search, $colorId, $matches)) {
      return self::getColorFromFallbackColor($matches[1]);
    }
    return;
  }

  protected static function getColorFromFallbackColor($fallbackColor)
  {
    switch (substr($fallbackColor, 0, 1)) {
      case 1:
        $rValue = intval(substr($fallbackColor, 1, 2), 16);
        $gValue = intval(substr($fallbackColor, 3, 2), 16);
        $bValue = intval(substr($fallbackColor, 5, 2), 16);
        $aValue = intval(substr($fallbackColor, 7, 2), 16) / 100;
        if ($aValue > 1) {
          $aValue = 1;
        }

            return sprintf('rgba(%d,%d,%d,%.2f)', $rValue, $gValue, $bValue, $aValue);
        break;
    }

    // no fallback color
    return;
  }

  protected static function createFallbackColor($colorValue)
  {
    $fallbackColor = '';

    // convert rgba to fallback rgba hexcolor
    if (preg_match('/^\s*rgba\(([0-9]*),([0-9]*),([0-9]*),([0-9\.]*)\)\s*$/i', $colorValue, $matches)) {
      $fallbackType = 1;
      $fallbackColor = sprintf(
          '%d%02x%02x%02x%02x',
          $fallbackType,
          $matches[1],
          $matches[2],
          $matches[3],
          $matches[4] * 100
      );
    }

    // extends fallback color to 12 characters
    return str_pad($fallbackColor, 12, '0', STR_PAD_RIGHT);
  }
}
