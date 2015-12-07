<?php
namespace Dual\Render\Navigation\Iface;

/**
 * Navigation Interface
 *
 * @package      Dual
 * @subpackage   Render
 */

interface Navigation
{
  /**
   * Navigationswerte zurueckgsetzen
   * @access public
   */
  public static function reset();

  /**
   * Zusaetzliche Daten setzen
   * @param array  $data      Referenz auf ein Daten-Array
   * @access public
   */
  public static function setAdditionalData(&$data);

  /**
   * Navigator-Array ermitteln
   * @param string  $pageId   ID der Page von welcher der Navigator ermittelt werden soll
   * @return array  Navigator-Array
   * @access public
   */
  public static function &getNavigator($pageId = null);

  /**
   * Array mit Kindepages ermitteln
   * @param string  $pageId       ID der Page von welcher die Kindpages ermittelt werden soll
   * @param string  $hiddenPages  Auch Pages aufnehen, welche in der Navigation ausgeblendet sind
   * @return array  Array mit den Kindpages
   * @access public
   */
  public static function &getChildren($pageId = null, $hiddenPages = false);

  /**
   * Navigationsknoten ermitteln
   * @param string  $id           ID der Page welche der Navigationsknoten ermittelt werden soll
   * @return array  Array mit dem navigationsknoten
   * @access public
   */
  public static function &getNodeById($id);
}
