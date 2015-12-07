<?php
namespace Dual\Render\Navigation\Base;

use Dual\Render\Navigation\Iface\Navigation as NavigationInterface;
use Dual\Render\Navigation as RenderNavigation;
use Dual\Render\CurrentPage;
use Dual\Render\WebPage;

/**
 * Navigation Interface includieren
 *
 * @see Dual\Render\Navigation\Iface\Navigation
 */
require_once  __DIR__.'/../Iface/Navigation.php';

/**
 * Navigation
 *
 * @package      Dual
 * @subpackage   Render
 */
abstract class Navigation implements NavigationInterface
{
  /**
   * Statische Variable mit dem aktullen Render-Typ
   * @access private
   */
  protected static $renderType = null;

  /**
   * Statische Variable mit der Referenz auf das Navigations-Index-Array
   * @access priprotectedvate
   */
  protected static $navigationIndex = array();

  /**
   * Statische Variable mit der Startseiten
   * @access protected
   */
  protected static $rootChildren = null;

  /**
   * Zusaetzliche Daten, welche in den einzelenen Navigationsauspraegungen
   * benoetigt werden
   * @access protected
   */
  protected static $additionalData = array();

  /**
   * Statische Variable mit dem "Navigation komplett eingelesen" Status
   * @access priprotectedvate
   */
  protected static $navigationComplete = false;

  /**
   * Render-Type setzen
   * @param   string    $renderType   Aktueller Render-Typ
   * @access  public
   */
  public static function setRenderType($renderType)
  {
    self::$renderType = $renderType;
  }

  /**
   * Render-Typ zurueckgeben
   * @return  string    Aktueller Render-Typ
   * @access  public
   */
  public static function getRenderType()
  {
    return self::$renderType;
  }

  /**
   * Navigationswerte zurueckgsetzen
   * @access public
   */
  public static function reset()
  {
    self::$navigationIndex = array();
    self::$rootChildren = null;
    self::$additionalData = array();
    self::$navigationComplete = false;
  }

  /**
   * Zusaetzliche Daten setzen
   * @param array  $data      Referenz auf ein Daten-Array
   * @access public
   */
  public static function setAdditionalData(&$data)
  {
    self::$additionalData = $data;
  }

  public static function &getNavigator($pageId = null)
  {
    // Aktuelle Webpage ermitteln?
    if (!isset($pageId) || empty($pageId) || $pageId == RenderNavigation::CURRENT_PAGE) {
    // Aktuelle ermitteln
      $pageId  = CurrentPage::get('id');
    }
    
    // Navigator der uebergebenen Seite ermitteln
    $navigator = array();
    self::getNavigatorNode($pageId, $navigator);
    
    return $navigator;
  }

  protected static function getNavigatorNode($pageId, &$navigator)
  {
    if (!empty($pageId) && $pageId != RenderNavigation::ROOT) {
    // Knoten ermitteln
      $node = self::getNodeById($pageId);
      if (isset($node) && isset($node['id'])) {
      // Parent zuerst ermitteln
        if (isset($node['parent'])) {
          self::getNavigatorNode($node['parent'], $navigator);
        }
    
        // Knoten aufnehmen
        $navigator[$node['id']] = $node;
      }
    } else {
      // Navigation bereits ermittelt?
      if (self::$navigationComplete === false || !isset(self::$rootChildren)) {
      // Navigations-Baum ermitteln?
        static::getNavigationTree(RenderNavigation::ROOT);
      }
    }
  }

  public static function & getChildren($pageId = null, $hiddenPages = false)
  {
    // init
    $children = array();
    $allChildren = array();
  
    // Aktuelle Webpage ermitteln?
    if (!isset($pageId) || empty($pageId) || $pageId == RenderNavigation::CURRENT_PAGE) {
    // Aktuelle ermitteln
      $pageId  = CurrentPage::get('id');
    }
    
    // Auf dem ROOT-Konten direkt die Startseiten zurueckgeben
    if ($pageId == RenderNavigation::ROOT) {
    // Bereits ermittelt?
      if (self::$navigationComplete === false || !isset(self::$rootChildren)) {
      // Navigations-Baum ermitteln?
        static::getNavigationTree(RenderNavigation::ROOT);
      }
      
      // ROOT-Children vorhanden
      if (isset(self::$rootChildren) && is_array(self::$rootChildren)) {
      // Kindelemente uebernehmen
        $allChildren = self::$rootChildren;
      }
    } else {
      // Knoten ermitteln
      $node = self::getNodeById($pageId);
      if (isset($node['children']) && is_array($node['children'])) {
        $allChildren = $node['children'];
      }
    }

    // Alle Seiten aufnehmen
    if ($hiddenPages === true) {
      $children = $allChildren;
    } // Nur Seiten aufnehmen, welche in der Navigation angezeigt werden sollen
    else {
      foreach ($allChildren as $key => &$nextChild) {
        if (isset($nextChild['data']) && $nextChild['data']->get('inNavigation')) {
          $children[$key] =& $nextChild;
        }
      }
    }

    // Kindelemente zurueckgeben
    return $children;
  }
  
  public static function &getNodeById($id)
  {
    // Aktuelle Webpage ermitteln?
    if (!isset($id) || empty($id) || $id == RenderNavigation::CURRENT_PAGE) {
    // Aktuelle ermitteln
      $id = CurrentPage::get('id');
    }

    // Id bereits im Navigations-Array vorhanden
    if (isset(self::$navigationIndex[$id])) {
    // Knoten zurueckgeben
      return self::$navigationIndex[$id];
    }

    if (self::$navigationComplete === false) {
    // Navigations-Baum ermitteln?
      static::getNavigationTree($id);

      // Id jetzt im Navigations-Array vorhanden?
      if (isset(self::$navigationIndex[$id])) {
      // Knoten zurueckgeben
        return self::$navigationIndex[$id];
      }
    }

    // Fehler (!! return by reference !!)
    $error = false;
    return $error;
  }
 
  protected static function createNavigation(&$navi, &$structure)
  {
    // Navigation ermitteln
    if (self::createNavigationInternal($navi, $structure, self::$rootChildren)) {
      self::$navigationComplete = true;
    }
  }

  protected static function createNavigationInternal(&$navi, &$structure, &$children, $parentId = RenderNavigation::ROOT)
  {
    // Navigations-Array vorhanden
    if (isset($navi) && is_array($navi)) {
    // init
      $children = array();
    
      // Knoten durchlaufen
      foreach ($navi as $node) {
        try {
          // Webpage ermittel
          $webpage = new Webpage();

          // Webpage ermittel
          static::fillWebpage($webpage, $structure, $node);
        } catch (Exception $e) {
        // no error handling
        }

        // Knoten in den Index aufnehmen
        self::$navigationIndex[$node['id']] = array(  'id'      => $node['id']
                                                    , 'data'    => $webpage
                                                    , 'parent'  => $parentId
                                                    );
        
        // Diesen Knoten als Children aufnehmen
        $children[] =& self::$navigationIndex[$node['id']];

        // Kindelemente durchlaufen
        if (isset($node['children']) && is_array($node['children'])) {
          self::$navigationIndex[$node['id']]['children'] = null;
          if (!self::createNavigationInternal($node['children'], $structure, self::$navigationIndex[$node['id']]['children'], $node['id'])) {
          // Fehler -> Abbrechen
            return false;
          }
        }
      }
    }
    
    // Erfolgreich
    return true;
  }
}
