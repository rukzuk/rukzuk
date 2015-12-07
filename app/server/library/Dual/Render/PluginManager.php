<?php
namespace Dual\Render;

/**
 * Render PluginManager
 *
 * @package      Dual
 * @subpackage   Render
 */

class PluginManager
{
  /**
   * Speichert alle Plugins
   * @var array
   */
  private static $plugins = array();

  /**
   * Setzt das Logger-Objekt.
   * @param Seitenbau_Logger $logger Logger-Objekt
   */
  public static function get($plugin)
  {
    if (!empty($plugin)) {
    // Sicherheit gewaehrleisten
      $plugin = str_replace('.', '', $plugin);

      // Plugin bereits geladen
      if (!isset(self::$plugins[$plugin]) || !is_object(self::$plugins[$plugin])) {
      // Plugin erstellen
        require_once('Plugin/'.$plugin.'.php');
        self::$plugins[$plugin] = new $plugin();
      }

      // Plugin zurueckgeben
      if (is_object(self::$plugins[$plugin])) {
        return self::$plugins[$plugin];
      }
    }

    // Plugin nicht vorhanden
    return;
  }
}
