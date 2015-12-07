<?php
namespace Dual\Render;

use Cms\Service\Iface\Marker as Service;

/**
 * @package    Dual
 * @subpackage Render
 */

class RenderContext
{
  // Render-Status
  const MODE_EDIT             = CMS::RENDER_MODE_EDIT;
  const MODE_PREVIEW          = CMS::RENDER_MODE_PREVIEW;
  const MODE_SHOW             = CMS::RENDER_MODE_SHOW;

  // Umgebung des Renders
  const CONTENT_TEMPLATE      = 'TEMPLATE';
  const CONTENT_PAGE          = 'PAGE';

  // Umgebung des Renders
  const RENDER_DYNAMIC        = 'Dynamic';
  const RENDER_STATIC         = 'Static';
  const RENDER_HTML_CREATOR   = 'HtmlCreator';

  // Services
  const SERVICE_MODUL         = 'Modul';
  const SERVICE_PAGE          = 'Page';


  /**
   * Speichert das Seiten-ROOT-Objekt.
   * @var object
   */
  private static $siteRoot;

  /**
   * Speichert den Render-Status
   *  (welcher Code bereits ausgefuehrt wurde. Z.B. HTML, CSS, ...)
   * @var array
   */
  private static $renderState = array();

  /**
   * Aktuelle WebsiteId
   * @var string
   */
  private static $websiteId = null;

  /**
   * Aktuelle id der Page oder des Templates
   * @var string
   */
  private static $id = null;

  /**
   * Speichert den aktuellen Modus
   *  EDIT|PREVIEW|SHOW
   * @var string
   */
  private static $mode = self::MODE_SHOW;

  /**
   * Speichert den aktuellen Inhalts-Typ
   *  PAGE|TEMPLATE
   * @var string
   */
  private static $contentType = self::CONTENT_PAGE;

  /**
   * Speichert den aktuellen Render-Typ
   * @var string
   */
  private static $renderType = self::RENDER_DYNAMIC;

  /**
   * New Render Context
   * @var \Render\RenderContext
   */
  private static $newRenderContext = null;

  /**
   * Konstruktor.
   */
  private function __construct()
  {
    // Ist nur eine Statische Klasse
  }

  /**
   * Complete init of this Class
   * @param \Render\RenderContext $newRenderContext
   * @param string $websiteId
   * @param string $pageOrTemplateId
   */
  public static function init($newRenderContext, $websiteId, $pageOrTemplateId)
  {
    self::reset();
    self::setNewRenderContext($newRenderContext);

    // mode
    switch ($newRenderContext->getRenderMode()) {

      case $newRenderContext::RENDER_MODE_EDIT:
        self::setMode(self::MODE_EDIT);
            break;
      case $newRenderContext::RENDER_MODE_PREVIEW:
        self::setMode(self::MODE_PREVIEW);
            break;

      default:
        self::setMode(self::MODE_SHOW);
    };

    // map content type
    if ($newRenderContext->getRenderType() === $newRenderContext::RENDER_TYPE_TEMPLATE) {
      self::setContentType(self::CONTENT_TEMPLATE);
    } else {
      self::setContentType(self::CONTENT_PAGE);
    }

    // old render type means which impl is used (assume SHOW == Live Server)
    if ($newRenderContext->getRenderMode() === $newRenderContext::RENDER_MODE_LIVE) {
      self::setRenderType(self::RENDER_STATIC);
    } else {
      self::setRenderType(self::RENDER_DYNAMIC);
    }

    self::setWebsiteId($websiteId);
    self::setId($pageOrTemplateId);

    // init other static global classes
    self::initCurrentSite();
    self::initCurrentPage();
  }

  /**
   * @param \Render\RenderContext $newRenderContext
   */
  public static function setNewRenderContext($newRenderContext)
  {
    self::$newRenderContext = $newRenderContext;
  }


  /**
   * Setzt die statischen Werte zurueck
   * @param boolean   $resetService   Auch die Liste der gespeicherten Services leeren
   */
  public static function reset($resetService = false)
  {
    // init
    self::$mode             = self::MODE_SHOW;
    self::$contentType      = self::CONTENT_PAGE;
    self::$renderType       = self::RENDER_DYNAMIC;
    self::$siteRoot         = null;
    self::$renderState      = array();
    if ($resetService) {
      self::$newRenderContext = null;
    }
  }

  /**
   * Setzt den aktuellen Modus
   * @param string  $mode   neuer Modus
   */
  public static function setMode($mode = self::MODE_SHOW)
  {
    self::$mode = $mode;
  }
  /**
   * Gibt den aktuellen Modus zurueck.
   * @return string
   */
  public static function getMode()
  {
    return self::$mode;
  }

  /**
   * Setzt den aktuellen Inhalts-Typ
   * @param string  $contentType   neuer Modus
   */
  public static function setContentType($contentType = self::CONTENT_PAGE)
  {
    self::$contentType = $contentType;
  }
  /**
   * Gibt den aktuellen Inhalts-Typ zurueck.
   * @return string
   */
  public static function getContentType()
  {
    return self::$contentType;
  }

  /**
   * Setzt den aktuellen Render-Typ
   * @param string  $renderType   neuer Modus
   */
  public static function setRenderType($renderType = self::RENDER_DYNAMIC)
  {
    self::$renderType = $renderType;
  }
  /**
   * Gibt den aktuellen Render-Typ zurueck.
   * @return string
   */
  public static function getRenderType()
  {
    return self::$renderType;
  }

  /**
   * Setzt die Id der aktuelle Website
   * @param string  $websiteId    aktuelle Website ID
   */
  public static function setWebsiteId($websiteId)
  {
    self::$websiteId = $websiteId;
  }
  /**
   * Gibt den Id der aktuellen Website zurueck.
   * @return string
   */
  public static function getWebsiteId()
  {
    return self::$websiteId;
  }

  /**
   * Setzt die Id der aktuelle page oder des Templates
   * @param string  $id    aktuelle Id
   */
  public static function setId($id)
  {
    self::$id = $id;
  }
  /**
   * Gibt den Id der aktuellen Page oder des Templates zurueck.
   * @return string
   */
  public static function getId()
  {
    return self::$id;
  }

  /**
   * Setzt das Page-Root-Object
   * @param object $root Page-Root-Object
   */
  public static function setRoot(&$root)
  {
    self::$siteRoot = $root;
  }
  /**
   * Gibt das Page-Root-Object zurueck.
   * @return object   Page-Root-Object
   */
  public static function getRoot()
  {
    return self::$siteRoot;
  }

  /**
   *
   * @param string $name
   * @param Service $service
   *
   * @deprecated DO NOT USE THIS FUNCTION
   */
  public static function addService($name, Service $service)
  {
    // do nothing
  }
  /**
   * @deprecated DO NOT USE THIS FUNCTION
   * @throws \Exception
   */
  public static function getService($name = '')
  {
    throw new \Exception("Dual RenderContext::getService called!", $name);
  }

  /**
   * Setzt fuer eine bestimmte Id und Code-Type den uebergebenen Status
   *
   * @param string    $type     Code-Type, welcher ueberpreuft werden soll
   * @param string    $id       Id, welcher ueberpreuft werden soll
   * @param           $state    Status oder false
   * @return mixed              Status oder false
   */
  public static function addRenderState($type, $id, $state)
  {
    // Typ vorhanden
    if (!isset(self::$renderState[$type]) || !is_array(self::$renderState[$type])) {
    // Typ aufnehmen
      self::$renderState[$type] = array();
    }

    // Id aufnehmen
    self::$renderState[$type][$id] = $state;

    // Erfolgreich
    return true;
  }
  /**
   * Ueberprueft ob fuer eine bestimmte Id der angegebene Code-Type
   * bereits gerendert wurde
   *
   * @param string    $type     Code-Type, welcher ueberpreuft werden soll
   * @param string    $id       Id, welcher ueberpreuft werden soll
   * @return mixed    Status oder false
   */
  public static function checkRenderState($type, $id)
  {
    // Wurde der Typ und eine ID angegeben
    if (!empty($type) && !empty($id)) {
    // Typ vorhanden
      if (isset(self::$renderState[$type]) && is_array(self::$renderState[$type])) {
      // Wurde diese ID bereits gerendert
        if (isset(self::$renderState[$type][$id])) {
        // Ja, status zurueckgeben
          return self::$renderState[$type][$id];
        }
      }
    }

    // Nein
    return false;
  }

  ///////////////
  /////////////// Adapter functions for Legacy Code, they replaces direct calls to Services
  ///////////////

  /**
   * Module Data as Fake DAO
   * @param {string} $mid
   * @return FakeBean
   */
  public static function getModuleById($moduleId)
  {

    $manifest = self::$newRenderContext->getModuleInfoStorage()->getModuleManifest($moduleId);
    $formValues = self::$newRenderContext->getModuleInfoStorage()->getModuleDefaultFromValues($moduleId);

    $bean = new FakeBean(array(
      'getId' => $moduleId,
      'getName' => $manifest['name'],
      'getModuletype' => $manifest['moduleType'],
      'getVersion' => $manifest['version'],
      'getFormValues' => $formValues
    ));

    return $bean;
  }

  /**
   * @param $moduleId
   * @return string
   */
  public static function getModuleDataPath($moduleId)
  {
    return self::$newRenderContext->getModuleInfoStorage()->getModuleCodePath($moduleId);
  }

  /**
   * @param $moduleId
   * @return string
   */
  public static function getModuleAssetUrl($moduleId)
  {
    return self::$newRenderContext->getModuleInfoStorage()->getModuleAssetUrl($moduleId);
  }

  /**
   * @param $moduleId
   * @return string
   */
  public static function getModuleAssetPath($moduleId)
  {
    return self::$newRenderContext->getModuleInfoStorage()->getModuleAssetPath($moduleId);
  }

  /**
   * @param $pageId
   * @return string
   */
  public static function getPageUrlById($pageId)
  {
    return self::$newRenderContext->getNavigationInfoStorage()->getPageUrl($pageId, array(), false);
  }

  /**
   * @param $pageId
   * @return FakeBean
   */
  public static function getPageById($pageId)
  {
    try {
      $pageNavItem = self::$newRenderContext->getNavigationInfoStorage()->getItem($pageId);
      $pageGlobals = self::$newRenderContext->getNavigationInfoStorage()->getPageGlobals($pageId);

      $pageArray = array(
        'id' => $pageNavItem->getPageId(),
        'websiteid' => self::getWebsiteId(),
        'templateId' => "",
        'name' => $pageNavItem->getTitle(),
        'description' => $pageNavItem->getDescription(),
        'date' => $pageNavItem->getDate(),
        'inNavigation' => (int)$pageNavItem->showInNavigation(),
        'navigationTitle' => $pageNavItem->getNavigationTitle(),
        'content' => "",
        'templateContent' => "",
        'templatecontentchecksum' => "",
        'globalcontent' => $pageGlobals,
        'lastupdate' => ""
      );

    } catch (\Exception $_e) {
      $pageArray = array();
    }

    return new FakeBean(array(
      'toArray' => $pageArray
    ));
  }

  /**
   * @return null|string
   */
  public static function getJsApiUrl()
  {
    return self::$newRenderContext->getJsApiUrl();
  }

  /**
   * @return string
   */
  public static function getCSSUrl()
  {
    return self::$newRenderContext->getNavigationInfoStorage()->getCurrentCssUrl();
  }

  /**
   * @return string
   */
  public static function getLocale()
  {
    return self::$newRenderContext->getInterfaceLocaleCode();
  }

  protected static function initCurrentSite()
  {

    // resolutions
    $res = self::$newRenderContext->getResolutions();
    $resolutionJsonString = json_encode($res);

    // color scheme
    $colorInfoStorage = self::$newRenderContext->getColorInfoStorage();
    $colorScheme = array();
    foreach ($colorInfoStorage->getColorIds() as $cid) {
      $colorScheme[] = array('id' => $cid, 'value' => $colorInfoStorage->getColor($cid), 'name' => '');
    }
    $colorSchemeJsonString = json_encode($colorScheme);

    // navigation
    $navigationInfoStorage = self::$newRenderContext->getNavigationInfoStorage();
    $rootIds = $navigationInfoStorage->getRootChildrenIds();

    $fillNav = function ($ids) use ($navigationInfoStorage, &$fillNav) {
      $nav = array();
      foreach ($ids as $id) {
        $item = array('id' => $id, 'children' => array());
        $childrenIds = $navigationInfoStorage->getChildrenIds($id);
        if (!empty($childrenIds)) {
          $item['children'] = $fillNav($childrenIds);
        }
        $nav[] = $item;
      }

      return $nav;
    };

    $navigation = json_encode($fillNav($rootIds));

    $websiteArray = array(
      'id' => self::$websiteId,
      'name' => 'WEBSITE',
      'description' => '',
      'navigation' => $navigation,
      'publish' => '',
      'colorscheme' => $colorSchemeJsonString,
      'resolutions' => $resolutionJsonString,
      'version' => '',
      'home' => '',
      'creationmode' => '',
      'ismarkedfordeletion' => false,
      'lastupdate' => '',
    );

    $website = new \Dual\Render\Website();
    $website->setArray($websiteArray);
    \Dual\Render\CurrentSite::setSite($website);

  }

  protected static function initCurrentPage()
  {
    /**
     * CurrentPage mit Daten befuellen
     */
    try {
      $navigationInfoStorage = self::$newRenderContext->getNavigationInfoStorage();
      $currentPageId = $navigationInfoStorage->getCurrentPageId();
      $globalContent = $navigationInfoStorage->getPageGlobals($currentPageId);
    } catch (\Exception $ignore) {
      // no error handling
      $currentPageId = null;
      $globalContent = array();
    }

    $page = self::getPageById($currentPageId);

    $dualWebpage = new \Dual\Render\Webpage();
    $dualWebpage->setArray($page->toArray());
    $dualWebpage->setGlobalArray($globalContent);
    \Dual\Render\CurrentPage::setPage($dualWebpage);
  }

  /**
   * @return \Render\MediaContext
   */
  public static function getMediaContext()
  {
    return self::$newRenderContext->getMediaContext();
  }

  /**
   * @return array
   */
  public static function getMediaIdsByAlbumId()
  {
    try {
      $infoStorage = self::getMediaContext()->getMediaInfoStorage();
      return $infoStorage->getIdsByAlbumIds();
    } catch (\Exception $e) {
      return array();
    }
  }
}

/**
 * Class FakeBean
 * Fakes methods of a class based on array data - simple mocking
 * @package Dual\Render
 */
class FakeBean
{

  private $data = array();

  public function __construct($data)
  {
    $this->data = $data;
  }

  public function __call($name, $args)
  {
    if (isset($this->data[$name])) {
      return $this->data[$name];
    } else {
      return null;
    }
  }
}
