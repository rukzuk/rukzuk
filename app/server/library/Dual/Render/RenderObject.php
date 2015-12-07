<?php
namespace Dual\Render;

/**
 * Render-Objekt-Klasse
 *
 * @package      Dual
 * @subpackage   Render
 */
class RenderObject
{
  private $Id                 = '';
  private $Name               = '';
  private $Values             = array();
  protected $moduleFormValues   = array();
  protected $moduleAttributes   = array();
  private $ModuleId           = '';
  private $WebsiteId          = '';
  protected $RendererCode     = null;
  protected $CssCode          = null;
  protected $HeadCode         = null;
  private $Children           = array();
  private $ChildClassIds      = array();
  private $curCodeType        = null;
  private $internalFunctions  = array();
  private $parentUnit         = null;
  private $ghostContainer     = false;
  private $templateUnitId     = null;
  private $translationObject  = null;
  private $createdChildren    = null;

  const CODE_TYPE_HTML        = 'renderHtml';
  const CODE_TYPE_HEAD        = 'renderHead';
  const CODE_TYPE_CSS         = 'renderCss';


  /**
   * Konstruktor.
   */
  public function __construct()
  {
  }

  public function setParent(&$parentUnit)
  {
    $this->parentUnit = $parentUnit;
  }

  public function getParent()
  {
    return $this->parentUnit;
  }

  public function getMode()
  {
    return RenderContext::getMode();
  }

  protected function setCodeType($type)
  {
    $this->curCodeType = $type;
  }

  protected function getCodeType()
  {
    return $this->curCodeType;
  }

  public function isTemplate()
  {
    if (RenderContext::getContentType() == RenderContext::CONTENT_TEMPLATE) {
      return true;
    }
    return false;
  }

  public function isPage()
  {
    if (RenderContext::getContentType() == RenderContext::CONTENT_PAGE) {
      return true;
    }
    return false;
  }

  public function set($Key, $Value)
  {
    $this->Values[$Key] = $Value;
  }

  public function get($key)
  {
    if ($key == 'ID') {
      return $this->getId();
    }

    if (is_object($this->Values) && property_exists($this->Values, $key)) {
      return $this->Values->$key;
    }
    if (is_array($this->Values) && isset($this->Values[$key])) {
      return $this->Values[$key];
    }
    return;
  }

  public function getId()
  {
    return $this->Id;
  }

  public function getName()
  {
    return $this->Name;
  }

  public function isGhostContainer()
  {
    return $this->ghostContainer;
  }

  public function getTemplateUnitId()
  {
    return ( $this->isTemplate() ? $this->getId() : $this->templateUnitId );
  }

  public function getValues()
  {
    return $this->Values;
  }

  public function getModuleAttributes()
  {
    return $this->moduleAttributes;
  }

  public function getModuleAttribute($name)
  {
    return isset($this->moduleAttributes[$name])
            ? $this->moduleAttributes[$name]
            : null;
  }

  public function getModuleId()
  {
    return $this->ModuleId;
  }

  public function getModuleName()
  {
    return $this->getModuleAttribute('name');
  }

  public function getModuleType()
  {
    return $this->getModuleAttribute('moduleType');
  }

  public function getModuleVersion()
  {
    return $this->getModuleAttribute('version');
  }

  public function getWebsiteId()
  {
    return $this->WebsiteId;
  }

  public function setWebsiteId($websiteId)
  {
    $this->WebsiteId = $websiteId;
  }

  public function getChildren()
  {
    return $this->Children;
  }

  public function getRendererCode()
  {
    if (!isset($this->RendererCode)) {
      $moduleDataPath = $this->getModuleDataPath();
      $this->RendererCode = file_get_contents($moduleDataPath.'/moduleRenderer.php');
    }
    return $this->RendererCode;
  }

  public function getCssCode()
  {
    if (!isset($this->CssCode)) {
      $moduleDataPath = $this->getModuleDataPath();
      $this->CssCode = file_get_contents($moduleDataPath.'/moduleCss.php');
    }
    return $this->CssCode;
  }

  public function getHeadCode()
  {
    if (!isset($this->HeadCode)) {
      $moduleDataPath = $this->getModuleDataPath();
      $this->HeadCode = file_get_contents($moduleDataPath.'/moduleHeader.php');
    }
    return $this->HeadCode;
  }

  public function p($Key, $bHtmlEncode = false)
  {
    if ($bHtmlEncode) {
      echo htmlentities($this->get($Key), ENT_COMPAT, 'UTF-8');
    } else {
      echo $this->get($Key);
    }
  }

  public function pEditable($fieldName, $tag, $attributes = '')
  {
    echo $this->getEditable($fieldName, $tag, $attributes);
  }

  public function getEditable($fieldName, $tag, $attributes = '')
  {
    // init
    $htmlOutput = '';
    $tag = (empty($tag) ? 'div' : $tag);

    // Start-Tag
    $htmlOutput .= '<'.$tag;

    // editable-Attribute im Edit-Modus
    if ($this->getMode() === RenderContext::MODE_EDIT) {
      $htmlOutput .= ' data-cms-editable="'.$fieldName.'"';
    }

    // Weitere Attribute
    if (!empty($attributes)) {
      $htmlOutput .= ' '.$attributes;
    }

    // Start-Tag-Ende
    $htmlOutput .= '>';

    // Eingegebener HTML-Code
    $fieldValue = $this->get($fieldName);
    $this->replaceLinks($fieldValue);
    $htmlOutput .= $fieldValue;

    // End-Tag
    $htmlOutput .= '</'.$tag.'>';

    // Gesammter HTML-Code zurueckgeben
    return $htmlOutput;
  }

  public function getPreviewUrl()
  {
    // DUMMY UNUSED FUNCTION
    return '#NoPreviewUrlFound';
  }

  public function getCssUrl()
  {
    return RenderContext::getCSSUrl();
  }

  public function getAssetUrl()
  {
    return RenderContext::getModuleAssetUrl($this->getModuleId());
  }

  public function getAssetPath()
  {
    return RenderContext::getModuleAssetPath($this->getModuleId());
  }

  public function setArray($newValues)
  {
    // Objekt in Array umwandeln
    if (is_object($newValues)) {
      $newValues = get_object_vars($newValues);
    }

    if (is_array($newValues)) {
      $this->Id               = $newValues['id'];
      $this->Name             = $newValues['name'];
      $this->ghostContainer   = ( isset($newValues['ghostContainer']) && $newValues['ghostContainer'] ? true : false );
      $this->templateUnitId   = ( isset($newValues['templateUnitId']) ? $newValues['templateUnitId'] : null );
      $this->Values           = $newValues['formValues'];
      $this->ModuleId         = $newValues['moduleId'];
      $this->Children         = ( isset($newValues['children']) ? $newValues['children'] : array() );
      $this->moduleAttributes = ( isset($newValues['moduleAttributes']) ? $newValues['moduleAttributes'] : array() );
    }

    // Falls die Values ein Objekt sind -> Array daraus erstellen
    if (is_object($this->Values)) {
      $this->Values = get_object_vars($this->Values);
    }

    // Default-Werte ermitteln
    $this->getBaseValues();

    // Werte zusammenfuehren
    $this->mergeFormValues();
  }

  public function insertJsApi()
  {
    if ($this->getMode() === RenderContext::MODE_EDIT) {
      $apiUrl = RenderContext::getJsApiUrl();
      echo '<script type="text/javascript" src="'.$apiUrl.'"></script>';
    }
  }

  public function insertCss()
  {
    // Rendern wir bereits das CSS?
    if ($this->getCodeType() != self::CODE_TYPE_CSS) {
    // Nein -> CSS-Rendern
      $root = RenderContext::getRoot();
      if (is_object($root)) {
        $css_code = '';
        $root->renderCss($css_code);
        echo $css_code;
      }
    }
  }

  public function insertHead()
  {
    // Rendern wir bereits den Head?
    if ($this->getCodeType() != self::CODE_TYPE_HEAD) {
    // Nein -> Head rendren
      $root = RenderContext::getRoot();
      if (is_object($root)) {
        $root->renderHead();
      }
    }
  }

  protected function getRenderModule()
  {
    return new RenderModule($this);
  }

  protected function html()
  {
    return $this->getRenderModule()->html();
  }

  protected function css(&$css)
  {
    return $this->getRenderModule()->css($css);
  }

  protected function head()
  {
    return $this->getRenderModule()->head();
  }

  public function renderHtml()
  {
    // Render-Typ merken
    $this->setCodeType(self::CODE_TYPE_HTML);

    // HTML rendern
    $this->html();
  }

  public function renderCss(&$css)
  {
    // Render-Typ merken
    $this->setCodeType(self::CODE_TYPE_CSS);

    // CSS-Rendern
    $this->css($css);
  }

  public function renderHead()
  {
    // Render-Typ merken
    $this->setCodeType(self::CODE_TYPE_HEAD);

    // HTML rendern
    $this->head();
  }

  public function &createChildren()
  {
    if ($this->createdChildren instanceof RenderList) {
      return $this->createdChildren;
    }
    $this->createdChildren = new RenderList();
    $this->createdChildren->setParent($this);
    $this->createdChildren->setTree($this->Children);
    return $this->createdChildren;
  }

  protected function renderChilds($type = self::CODE_TYPE_HTML)
  {
    $this->renderChildren();
  }

  public function renderChildren($config = null, $type = self::CODE_TYPE_HTML)
  {
    // Rendern
    $oChildren = $this->createChildren();
    return $oChildren->$type($config);
  }

  public function renderChildrenCss(&$css)
  {
    // Rendern
    $oChildren = $this->createChildren();
    $oChildren->renderCss($css);
  }

  public function renderChildrenHead()
  {
    // Rendern
    $oChildren = $this->createChildren();
    $oChildren->renderHead();
  }

  protected function getBaseValues()
  {
    // Basiswerte ermitteln (nur OnTheFly)
    if (RenderContext::getRenderType() !== RenderContext::RENDER_STATIC) {
    // Hier z.B. den Render-und Css-Code aus dem Modul ermitteln
      $this->moduleAttributes = array();
      $this->moduleFormValues = array();
      try {
        // Modul ermitteln
        $modul = RenderContext::getModuleById($this->ModuleId);

        // Modul gefunden?
        if (is_object($modul)) {
        // Form-Werte uebernhemen
          $moduleFormValues = $modul->getFormValues();
          if (isset($moduleFormValues) && is_object($moduleFormValues)) {
            $moduleFormValues = get_object_vars($modul->getFormValues());
          }
          $this->moduleFormValues = $moduleFormValues;

          // Modul-Attribute ermitteln
          $this->moduleAttributes = array(
            'id'              => $modul->getId(),
            'name'            => $modul->getName(),
            'moduleType'      => $modul->getModuletype(),
            'version'         => $modul->getVersion(),
          );
        }
      } catch (Doctrine_Record_Exception $e) {
      // no error handling
      }
    }
  }

  protected function mergeFormValues()
  {
    // Form-Werte zusammenfuehren
    if (is_array($this->moduleFormValues) && count($this->moduleFormValues) > 0) {
      if (is_array($this->Values)) {
        // Module-Form-Defaultwerte und Modul-Instanz-Werte zusammenfuegen
        $aNewValue = array_replace_recursive($this->moduleFormValues, $this->Values);
        if (is_array($aNewValue)) {
          $this->Values = $aNewValue;
        } else {
          // No Error handling
        }
      } else {
        // Nur Module-Form-Defaultwerte uebernehmen
        $this->Values = $this->moduleFormValues;
      }
    }
  }

  protected function checkRenderHeadState()
  {
    // Wurde der Head dieses Modul (nicht Unit) bereits gerendert
    return RenderContext::checkRenderState(self::CODE_TYPE_HEAD, $this->getModuleId());
  }
  protected function addRenderHeadState()
  {
    // Head-Render Status dieses Modul (nicht Unit) setzen
    return RenderContext::addRenderState(self::CODE_TYPE_HEAD, $this->getModuleId(), true);
  }


  /*
   * Hilfsfunktionen fuer den HTML/CSS/HEAD-Renderer
   */

  // Ersetzen von Links des HTML-Editors
  protected function replaceLinks(&$data)
  {
    // entsprechende Link-Tags suchen und ersetzen
    $data = preg_replace_callback('/(<\s*a\s+)([^>]*?)(data-cms-link-type\s*=\s*["\'](internalPage|internalMedia|internalMediaDownload|mail|external)["\'])([^>]*?)(>)/i', array($this, 'replaceLinkTagCallback'), $data);
  }
  private function replaceLinkTagCallback($linkCode)
  {
    // init
    $output   = '';
    $linkType = (isset($linkCode[4]) ? strtolower($linkCode[4]) : 'default');
    $replaceCmsDataAttributes = ($this->getMode() === RenderContext::MODE_EDIT
                                  ? false : true);

    // Wichtige Link-Daten ermitteln
    $cmsDataAttributes = array();
    $this->findCmsDataAttributes(
        $linkCode[2],
        $cmsDataAttributes,
        $replaceCmsDataAttributes
    );
    $this->findCmsDataAttributes(
        $linkCode[5],
        $cmsDataAttributes,
        $replaceCmsDataAttributes
    );

    // interner Link oder Media und Link-Daten vorhanden
    if (($linkType == 'internalpage' || $linkType == 'internalmedia' || $linkType == 'internalmediadownload' )
         && isset($cmsDataAttributes['link']) ) {
      $newHref = '';

      // Neuer Href ermitteln
      if ($linkType == 'internalpage') {
      // Url der Page ermitteln
        $page = Navigation::getNodeById($cmsDataAttributes['link']);
        if (is_array($page) && isset($page['data']) && is_object($page['data'])) {
          $newHref = $page['data']->get('url');
        }
      } elseif ($linkType == 'internalmediadownload') {
      // Download-Url des MediaDB Items ermitteln
        $newHref = MediaDb::getDownloadUrl($cmsDataAttributes['link']);
      } elseif ($linkType == 'internalmedia') {
      // Download-Url des MediaDB Items ermitteln
        $newHref = MediaDb::getUrl($cmsDataAttributes['link']);
      }

      // evtl. Anker anhaengen
      if (isset($cmsDataAttributes['link-anchor'])
          && !empty($cmsDataAttributes['link-anchor'])) {
        $newHref .= $cmsDataAttributes['link-anchor'];
      }

      if (empty($newHref)) {
        $newHref = '#';
      }

      // Html-encodieren
      $newHref = htmlentities($newHref, ENT_COMPAT, 'UTF-8');

      // Neuer Href ermittelt -> ersetzen
      $this->replaceLinkHref($linkCode[2], $newHref);
      $this->replaceLinkHref($linkCode[5], $newHref);
    }

    // Ausherhalb des Edit-Modus das 'data-cms-link' Attribut entfernen
    if ($replaceCmsDataAttributes) {
      $output = $linkCode[1].$linkCode[2].$linkCode[5].$linkCode[6];
    } // Im Edit-Modus den Link-Tag neu erstellen
    else {
      $output = $linkCode[1].$linkCode[2].$linkCode[3].$linkCode[5].$linkCode[6];
    }

    return $output;
  }
  private function findCmsDataAttributes(&$code, &$cmsData, $replace = true)
  {
    // cms-Data Attribute ermitteln
    $findRegExp = '/(\A| )data-cms-(link|link-type|link-anchor)\s*=\s*("([^"]*)"|\'([^\']*)\')/i';
    if (preg_match_all($findRegExp, $code, $treffer, PREG_SET_ORDER)) {
    // cms-data Attribute aufnehmen
      foreach ($treffer as $nextAttribute) {
        $cmsData[strtolower($nextAttribute[2])] ='';
        if (isset($nextAttribute[4])) {
          $cmsData[strtolower($nextAttribute[2])] .= $nextAttribute[4];
        }
        if (isset($nextAttribute[5])) {
          $cmsData[strtolower($nextAttribute[2])] .= $nextAttribute[5];
        }
      }

      // ggf. cms-data Attribute entfernen
      if ($replace) {
        $code = preg_replace($findRegExp, '', $code);
      }
    }
  }
  private function replaceLinkHref(&$code, $newHref)
  {
    $code = preg_replace('/(\A| )href\s*=\s*("[^"]*"|\'[^\']*\')/i', ' href="'.$newHref.'"', $code);
  }

  // Eigene Funktionen fuer das Modul/Unit aufnehmen
  public function addFunction($method, $callback)
  {
    $this->internalFunctions[$method] =& $callback;
  }

  // Eigene Funktionen ausfuehren
  public function __call($method, $args = array())
  {

    if (isset($this->internalFunctions[$method])
        &&
        is_callable($this->internalFunctions[$method])
      ) {
      return call_user_func_array($this->internalFunctions[$method], $args);
    }
  }

  public function getModuleDataPath()
  {
    return RenderContext::getModuleDataPath($this->getModuleId());
  }

  /**
   * Loads and returns the module translation. Returns null if no translation
   * was found.
   *
   * @return \Dual\Render\ModuleTranslationObject
   */
  public function i18n()
  {
    if ($this->translationObject === null) {
      $modulePath = $this->getModuleDataPath();
      if ($modulePath) {
        try {
          $this->translationObject = new ModuleTranslationObject($modulePath);
        } catch (\Exception $e) {
          // no error handling
        }
      }
    }
    return $this->translationObject;
  }
}
