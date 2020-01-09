<?php
namespace Cms\Reparser;

/**
 * UnitExtractor
 *
 * @package      Cms
 * @subpackage   Response
 */

class UnitExtractor
{
  /**
   * Extrahiert die einzelnen Units aus dem PageContent
   *
   * @param string $pageContent
   *
   * @return array
   */
  public static function getUnitsFromPageContent($pageContent)
  {
    $pageContentArr = \Seitenbau\Json::decode($pageContent);

    $units = array();

    $success = self::getUnitsFromContent($pageContentArr, $units, true, true);

    if (!$success) {
      return array();
    }

    return $units;
  }

  /**
   * Extrahiert die einzelnen Units aus dem TemplateContent
   *
   * @param string $templateContent
   *
   * @return array
   */
  public static function getUnitsFromTemplateContent($templateContent)
  {
    $templateContentArr = \Seitenbau\Json::decode($templateContent);
    
    $units = array();

    $success = self::getUnitsFromContent($templateContentArr, $units);

    if (!$success) {
      return array();
    }

    return $units;
  }

  /**
   * Extrahiert die einzelenen Units aus einem Content-Baum
   *
   * Die Units werden als flaches Array im Units-Parameter gespeichert, der Key
   * ist die ID der ContentUnit
   *
   * @param array $content
   * @param array $units
   * @param boolean $isPage
   * @param boolean $isRecursive
   *
   * @return  boolean true, wenn Units erfolgreich ermittelt werden konnten,
   *                  sonst false
   */
  static function getUnitsFromContent(
      &$content,
      &$units,
      $isPage = false,
      $isRecursive = true,
      $insideGhost = false
  ) {
    if (!isset($units['units'])) {
      $units['units'] = array();
    }

    if (is_array($content)) {
      foreach ($content as &$contentUnit) {
        // Flag: Unit innerhalb des Ghost-Bereichs
        $contentUnit['insideGhost'] = ($insideGhost ? true : false);
      
        // Unit aufnehmen
        $units['units'][$contentUnit['id']] =& $contentUnit;

        // Bei Pages auch die Units ueber die TemplateUnitId referenzieren
        if ($isPage && !empty($contentUnit['templateUnitId'])) {
          if (!isset($units['tunits'])) {
            $units['tunits'] = array();
          }

          if (!isset($units['tunits'][$contentUnit['templateUnitId']])
              || !is_array($units['tunits'][$contentUnit['templateUnitId']])) {
            $units['tunits'][$contentUnit['templateUnitId']] = array();
          }

          $units['tunits'][$contentUnit['templateUnitId']][$contentUnit['id']] =& $contentUnit;
        }

        // Sub-Units beachten
        if ($isRecursive
            && isset($contentUnit['children'])
            && is_array($contentUnit['children'])
        ) {
          if (!self::getUnitsFromContent($contentUnit['children'], $units, $isPage, true, $insideGhost)) {
            return false;
          }
        }

        // Bei Pages auch GhostUnits beachten
        if ($isRecursive
            && $isPage && isset($contentUnit['ghostChildren'])
            && is_array($contentUnit['ghostChildren'])
        ) {
          if (!self::getUnitsFromContent($contentUnit['ghostChildren'], $units, $isPage, true, true)) {
            return false;
          }
        }
      }
    }
    return true;
  }
}
