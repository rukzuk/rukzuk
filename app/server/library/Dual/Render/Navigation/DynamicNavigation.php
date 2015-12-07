<?php
namespace Dual\Render\Navigation;

use Dual\Render\Navigation\Base\Navigation as NavigationBase;
use Dual\Render\CurrentSite as CurrentSite;
use Dual\Render\RenderContext as RenderContext;

/**
 * Navigation base includieren
 *
 * @see Dual\Render\Navigation\Base\Navigation
 */
require_once __DIR__.'/Base/Navigation.php';

/**
 * Dynamische Navigation bei Rendering "on the fly"
 *
 * @package      Dual
 * @subpackage   Render
 */

class DynamicNavigation extends NavigationBase
{
  protected static function getNavigationTree($id)
  {
    // init
    $navigation = array();
    $structure  = array();

    // Navigation aus der aktuellen Website ermitteln
    $navigation = CurrentSite::get('navigation');
    if (is_string($navigation)) {
      $navigation = json_decode($navigation, true);
    }

    // Navigations-Array aufbauen
    return self::createNavigation($navigation, $structure);
  }

  protected static function fillWebpage(\Dual\Render\Webpage &$webpage, &$structure, &$node)
  {
    // Webpage-Daten ermitteln
    $data = $node;
    $globalVars = array();


    // Webpage ermitteln
    $cmsWebpage = RenderContext::getPageById($node['id']);

    // Webpage gefunden?
    if (is_object($cmsWebpage)) {
    // Daten uebernehmen
      $data = $cmsWebpage->toArray();
      $globalVars = $data['globalcontent'];
      if (is_string($globalVars)) {
        $globalVars = json_decode($globalVars, true);
      }

      // Einige Attribute entfernen
      unset($data['content']);
      unset($data['globalcontent']);
      unset($data['templateContent']);
    }

    // Url aufnehmen
    $data['url'] = RenderContext::getPageUrlById($node['id']);

    // Werte aufnehmen
    $webpage->setArray($data);
    $webpage->setGlobalArray($globalVars);
    
    // Erfoglreich
    return true;
  }
}
