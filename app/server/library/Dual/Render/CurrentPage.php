<?php
namespace Dual\Render;

/**
 * Render CurrentPage
 *
 * @package      Dual
 * @subpackage   Render
 */

class CurrentPage
{
  /**
   * Statische Variable mit der Instanz der aktuellen Webpage
   * @access private
   */
  private static $webpage = null;

  
  /**
   * Aktuelle Webpage setzen
   * @access public
   */
  public static function setPage(&$webpage)
  {
    // Webpage uebernehmen
    self::$webpage = $webpage;
  }
  
  /**
   * Attribute der aktuellen Webpage zurueckgeben
   * @param   string  $attribute    Name des Attributes welches zurueckgegeben werden soll
   * @access public
   */
  public static function get($attribute)
  {
    // Webpage-Instanz vorhanden?
    if (isset(self::$webpage) && is_object(self::$webpage)) {
    // Wert ermitteln und zurueckgeben
      return self::$webpage->get($attribute);
    }
    
    // Fehler -> Nichts zurueckgeben
    return;
  }

  /**
   * Globale Variable der aktuellen Webpage zurueckgeben
   * @param   string  $varName    Name der globalen Variable welche zurueckgegeben werden soll
   * @param   string  $index      Bestimmten Wert der globalen Variable zurueckgeben
   * @access public
   */
  public static function getGlobal($varName, $index = false)
  {
    // Webpage-Instanz vorhanden?
    if (isset(self::$webpage) && is_object(self::$webpage)) {
    // Wert ermitteln und zurueckgeben
      return self::$webpage->getGlobal($varName, $index);
    }

    // Fehler -> Nichts zurueckgeben
    return;
  }
}
