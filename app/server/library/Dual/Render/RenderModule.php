<?php
namespace Dual\Render;

use Dual\Render\RenderObject as RenderObject;
use Dual\Render\CMS as CMS;

/**
 * RenderModule
 *
 * @package      Dual
 * @subpackage   Render
 */


class RenderModule implements IModuleAPI, IRenderModule
{
  const CODE_TYPE_HTML        = 'renderHtml';
  const CODE_TYPE_HEAD        = 'renderHead';
  const CODE_TYPE_CSS         = 'renderCss';
  const CODE_PREFIX = 'namespace Dual\Render; ?>';

  public $renderObject = null;

  public function __construct(RenderObject $renderObject)
  {
    $this->renderObject = $renderObject;
  }

  public function html()
  {
    $moduleDataPath = $this->getModuleDataPath();

    // Starting an output buffer, so we can trim the output of this module
    ob_start();
    $self =& $this;
    include($moduleDataPath . '/moduleRenderer.php');
    // Storing the trimmed output, because the output buffer will catch an echo again.
    $strBuf = trim(ob_get_contents());
    // Closing the output buffer
    ob_end_clean();

    // Now we can echo the trimmed output
    echo $strBuf;
  }

  public function head()
  {
    // Wurde der Code dieses Modules bereits Ausgegeben
    if (!$this->checkRenderHeadState()) {
      $moduleDataPath = $this->getModuleDataPath();

      // Module Rendern
      $self =& $this;
      include($moduleDataPath . '/moduleHeader.php');

      // Head des Modul als gerender aufnehmen
      $this->addRenderHeadState();
    }

    // Auch den Head der Kindelemente rendern
    $this->renderChildrenHead();
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

  public function css(&$css)
  {
    $moduleDataPath = $this->getModuleDataPath();

    // CSS-Code ermitteln / Rendern
    $curCss = '';
    ob_start();
    try {
      $self =& $this;
      include($moduleDataPath . '/moduleCss.php');
      $curCss = ob_get_contents();
    } catch (\Exception $e) {
      ob_end_clean();
      throw new \Exception($e->getMessage());
    }
    ob_end_clean();

    if ($this->getMode() == CMS::RENDER_MODE_EDIT) {
      $curCss = "\n/* {CSS-UNIT:" . $this->getId() . "} */\n" .
          $curCss .
          "\n/* {/CSS-UNIT:" . $this->getId() . "} */\n";
    }

    // Aktuellen CSS-Code aufnehmen
    $css .= $curCss;

    // Auch den CSS der Kindelemente rendern
    $this->renderChildrenCss($css);
  }

  // ================================================================
  // === IModuleAPI Interface                                     ===
  // ================================================================

  public function getParent()
  {
    return $this->renderObject->getParent();
  }

  public function getMode()
  {
    return $this->renderObject->getMode();
  }

  public function isTemplate()
  {
    return $this->renderObject->isTemplate();
  }

  public function isPage()
  {
    return $this->renderObject->isPage();
  }

  public function isGhostContainer()
  {
    return $this->renderObject->isGhostContainer();
  }

  public function set($Key, $Value)
  {
    return $this->renderObject->set($Key, $Value);
  }

  public function get($key)
  {
    return $this->renderObject->get($key);
  }

  public function getId()
  {
    return $this->renderObject->getId();
  }

  public function getName()
  {
    return $this->renderObject->getName();
  }

  public function getTemplateUnitId()
  {
    return $this->renderObject->getTemplateUnitId();
  }

  public function getValues()
  {
    return $this->renderObject->getValues();
  }

  public function getModuleAttributes()
  {
    return $this->renderObject->getModuleAttributes();
  }

  public function getModuleAttribute($name)
  {
    return $this->renderObject->getModuleAttribute($name);
  }

  public function getModuleId()
  {
    return $this->renderObject->getModuleId();
  }

  public function getModuleName()
  {
    return $this->renderObject->getModuleName();
  }

  public function getModuleType()
  {
    return $this->renderObject->getModuleType();
  }

  public function getModuleVersion()
  {
    return $this->renderObject->getModuleVersion();
  }

  public function getWebsiteId()
  {
    return $this->renderObject->getWebsiteId();
  }

  public function getChildren()
  {
    return $this->renderObject->getChildren();
  }

  public function getRendererCode()
  {
    return $this->renderObject->getRendererCode();
  }

  public function getCssCode()
  {
    return $this->renderObject->getCssCode();
  }

  public function getHeadCode()
  {
    return $this->renderObject->getHeadCode();
  }

  public function p($Key, $bHtmlEncode = false)
  {
    return $this->renderObject->p($Key, $bHtmlEncode);
  }

  public function pEditable($fieldName, $tag, $attributes = '')
  {
    return $this->renderObject->pEditable($fieldName, $tag, $attributes);
  }

  public function getEditable($fieldName, $tag, $attributes = '')
  {
    return $this->renderObject->getEditable($fieldName, $tag, $attributes);
  }

  public function getCssUrl()
  {
    return $this->renderObject->getCssUrl();
  }

  public function getAssetUrl()
  {
    return $this->renderObject->getAssetUrl();
  }

  public function getAssetPath()
  {
    return $this->renderObject->getAssetPath();
  }

  public function insertJsApi()
  {
    return $this->renderObject->insertJsApi();
  }

  public function insertCss()
  {
    return $this->renderObject->insertCss();
  }

  public function insertHead()
  {
    return $this->renderObject->insertHead();
  }

  public function renderHtml()
  {
    return $this->renderObject->renderHtml();
  }

  public function renderCss(&$css)
  {
    return $this->renderObject->renderCss($css);
  }

  public function renderHead()
  {
    return $this->renderObject->renderHead();
  }

  public function &createChildren()
  {
    return $this->renderObject->createChildren();
  }

  public function renderChilds()
  {
    return $this->renderChildren();
  }

  public function renderChildren($config = null, $type = self::CODE_TYPE_HTML)
  {
    return $this->renderObject->renderChildren($config, $type);
  }

  public function renderChildrenCss(&$css)
  {
    return $this->renderObject->renderChildrenCss($css);
  }

  public function renderChildrenHead()
  {
    return $this->renderObject->renderChildrenHead();
  }
  
  public function addFunction($method, $callback)
  {
    return $this->renderObject->addFunction($method, $callback);
  }

  public function __call($method, $args = array())
  {
    return call_user_func_array(array($this->renderObject, $method), $args);
  }
  
  public function getPreviewUrl()
  {
    return $this->renderObject->getPreviewUrl();
  }

  /**
   * Loads and returns the module translation. Returns null if no translation
   * was found.
   *
   * @return \Dual\Render\ModuleTranslationObject
   */
  public function i18n()
  {
    return $this->renderObject->i18n();
  }

  public function getModuleDataPath()
  {
    return $this->renderObject->getModuleDataPath();
  }
}
