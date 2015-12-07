<?php
namespace Dual\Render;

/**
 * WebsiteColor
 *
 * @see WebsiteColor
 */
require_once __DIR__.'/WebsiteColor.php';

/**
 * Render CurrentSite
 *
 * @package      Dual
 * @subpackage   Render
 */

class CurrentSite extends WebsiteColor
{
  /**
   * Statische Variable mit der Instanz der aktuellen Website
   * @access private
   */
  private static $website = null;

  
  /**
   * Aktuelle Website ermitteln
   * @access public
   */
  public static function setSite(&$website)
  {
    // Website uebernehmen
    self::$website = $website;
  }

  /**
   * Attribute der aktuellen Website zurueckgeben
   * @param   string  $attribute    Name des Attributes welches zurueckgegeben werden soll
   * @access public
   */
  public static function get($attribute)
  {
    // Website-Instanz vorhanden?
    if (isset(self::$website) && is_object(self::$website)) {
    // Wert ermitteln und zurueckgeben
      return self::$website->get($attribute);
    }
    
    // Fehler -> Nichts zurueckgeben
    return;
  }

  /**
   * Farbe aus dem Farbschema der aktuellen Website zurueckgeben
   * @param   string  $colorId    Id der gewuenschten Farbe
   * @param   null|string  $type       Typ der gewuenschten Farbe
   * @access public
   */
  public static function getColorById($colorId, $type = null)
  {
    // Website-Instanz vorhanden?
    if (isset(self::$website) && self::$website instanceof \Dual\Render\Website) {
    // Farbe ermitteln und zurueckgeben
      return self::$website->getColorById($colorId, $type);
    }

    // Fehler -> Nichts zurueckgeben
    return;
  }

  /**
   * Aufloesungen der Website zurueckgeben
   * @return array
   * @access public
   */
  public static function getResolutions()
  {
    return self::$website->get('resolutions');
  }
}
