<?php
namespace Dual\Render;

use Dual\Render\Navigation\Iface\Navigation as NavigationInterface;

/**
 * Navigation Interface includieren
 *
 * @see Dual\Render\Navigation\Iface\Navigation
 */
require_once __DIR__.'/Navigation/Iface/Navigation.php';

/**
 * Navigation
 *
 * @package      Dual
 * @subpackage   Render
 */

class Navigation implements NavigationInterface
{
  // Navigations Konstanten
  const CURRENT_PAGE  = 'NAVI_CURRENTWEBPAGEID_NAVI';
  const ROOT          = 'ROOT';

  /**
   * Name der verwendeten Navigationklasse
   * @access private
   */
  private static $navigationClassName = null;

  /**
   * Liste der geladenen Navigationsklassen
   * @access private
   */
  private static $_navigationClassesLoaded = array();


  /**
   * Render-Type setzen
   * @return string   $renderType   Render-Typ welcher verwendet werden soll
   * @access public
   */
  public static function setRenderType($renderType)
  {
    // Versuchen die neue Navigationsklasse  zu laden
    $navigationName = preg_replace('/[^A-Z0-9]/i', '', $renderType).'Navigation';
    if (self::loadClass($navigationName)) {
      self::$navigationClassName = 'Dual\\Render\\Navigation\\'.$navigationName;
      $className = self::$navigationClassName;
      $className::setRenderType($renderType);
    }
  }

  /**
   * Name der zu verwendenden Navigationsklasse zurueckgeben
   * @return string   Namen der Navigationsklasse welche verwendet werden soll
   * @access public
   */
  private static function getNavigationClassName()
  {
    // Muss der Klassennamen ermittelt werden
    if (!isset(self::$navigationClassName) || empty(self::$navigationClassName)) {
    // Navigationsklasse laden und Classennamen setzen
      $renderType = RenderContext::getRenderType();
      $navigationName = $renderType.'Navigation';
      self::loadClass($navigationName);
      self::$navigationClassName = 'Dual\\Render\\Navigation\\'.$navigationName;
      $className = self::$navigationClassName;
      $className::setRenderType($renderType);
    }
    
    // Klassennamen zurueckgeben
    return self::$navigationClassName;
  }

  /**
   * Laede eine Navigationsklasse
   */
  private static function loadClass($navigationClassName)
  {
    // Navigationsklasse bereits geladen
    if (isset(self::$_navigationClassesLoaded[$navigationClassName])) {
      return true;
    }

    // Navigationsklasse laden
    $navigationClassFile = __DIR__.'/Navigation/'.$navigationClassName.'.php';
    if (file_exists($navigationClassFile)) {
      include_once($navigationClassFile);
      self::$_navigationClassesLoaded[$navigationClassFile] = true;
      return true;
    }

    // Navigationsklasse konnte nicht geladen werden
    return false;
  }

  /**
   * Navigationswerte zurueckgsetzen
   * @access public
   */
  public static function reset()
  {
    // Eigentliche Klassenfunktion aufrufen
    $className = self::getNavigationClassName();
    return $className::reset();
  }

  /**
   * Zusaetzliche Daten setzen
   * @param array  $data      Referenz auf ein Daten-Array
   * @access public
   */
  public static function setAdditionalData(&$data)
  {
    // Eigentliche Klassenfunktion aufrufen
    $className = self::getNavigationClassName();
    return $className::setAdditionalData($data);
  }

  /**
   * Navigator-Array ermitteln
   * @param string  $pageId   ID der Page von welcher der Navigator ermittelt werden soll
   * @return array  Navigator-Array
   * @access public
   */
  public static function &getNavigator($pageId = null)
  {
    // Eigentliche Klassenfunktion aufrufen
    $className = self::getNavigationClassName();
    return $className::getNavigator($pageId);
  }

  /**
   * Array mit Kindepages ermitteln
   * @param string  $pageId       ID der Page von welcher die Kindpages ermittelt werden soll
   * @param string  $hiddenPages  Auch Pages aufnehen, welche in der Navigation ausgeblendet sind
   * @return array  Array mit den Kindpages
   * @access public
   */
  public static function &getChildren($pageId = null, $hiddenPages = false)
  {
    // Eigentliche Klassenfunktion aufrufen
    $className = self::getNavigationClassName();
    return $className::getChildren($pageId, $hiddenPages);

  }
  
  /**
   * Navigationsknoten ermitteln
   * @param string  $id           ID der Page welche der Navigationsknoten ermittelt werden soll
   * @return array  Array mit dem navigationsknoten
   * @access public
   */
  public static function &getNodeById($pageId)
  {
    // Eigentliche Klassenfunktion aufrufen
    $className = self::getNavigationClassName();
    return $className::getNodeById($pageId);
  }
}
