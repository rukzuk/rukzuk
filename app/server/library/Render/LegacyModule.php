<?php


namespace Render;

use Render\APIs\LegacyModuleAPI;
use Dual\Render\RenderContext as LegacyRenderContext;

final class LegacyModule
{
  const CODE_TYPE_HTML = 'renderHtml';
  const CODE_TYPE_HEAD = 'renderHead';
  const CODE_TYPE_CSS = 'renderCss';

  private $api;

  public function head(&$api)
  {
    $this->api = $api;
    $moduleDataPath = $this->getModuleDataPath();
    $self =& $this;
    include($moduleDataPath . '/moduleHeader.php');
  }

  public function html(&$api)
  {
    $this->api = $api;
    $moduleDataPath = $api->getModuleDataPath();

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

  public function css(LegacyModuleAPI &$api)
  {
    $this->api = $api;
    $moduleDataPath = $this->getModuleDataPath();

    // CSS-Code ermitteln / Rendern
    $self =& $this;

    if ($this->getMode() == LegacyRenderContext::MODE_EDIT) {
      echo "\n/* {CSS-UNIT:" . $api->get('ID') . "} */\n";
    }

    include($moduleDataPath . '/moduleCss.php');

    if ($this->getMode() == LegacyRenderContext::MODE_EDIT) {
      echo "\n/* {/CSS-UNIT:" . $api->get('ID') . "} */\n";
    }
  }

  public function getParent()
  {
    return $this->api->getParent();
  }

  public function getMode()
  {
    return $this->api->getMode();
  }

  public function isTemplate()
  {
    return $this->api->isTemplate();
  }

  public function isPage()
  {
    return $this->api->isPage();
  }

  public function isGhostContainer()
  {
    return $this->api->isGhostContainer();
  }

  public function set($Key, $Value)
  {
    return $this->api->set($Key, $Value);
  }

  public function get($key)
  {
    return $this->api->get($key);
  }

  public function getId()
  {
    return $this->api->getId();
  }

  public function getName()
  {
    return $this->api->getName();
  }

  public function getTemplateUnitId()
  {
    return $this->api->getTemplateUnitId();
  }

  public function getValues()
  {
    return $this->api->getValues();
  }

  public function getModuleAttributes()
  {
    return $this->api->getModuleAttributes();
  }

  public function getModuleAttribute($name)
  {
    return $this->api->getModuleAttribute($name);
  }

  public function getModuleId()
  {
    return $this->api->getModuleId();
  }

  public function getModuleName()
  {
    return $this->api->getModuleName();
  }

  public function getModuleType()
  {
    return $this->api->getModuleType();
  }

  public function getModuleVersion()
  {
    return $this->api->getModuleVersion();
  }

  public function getWebsiteId()
  {
    return $this->api->getWebsiteId();
  }

  public function getChildren()
  {
    return $this->api->getChildren();
  }

  public function getRendererCode()
  {
    return $this->api->getRendererCode();
  }

  public function getCssCode()
  {
    return $this->api->getCssCode();
  }

  public function getHeadCode()
  {
    return $this->api->getHeadCode();
  }

  public function p($Key, $bHtmlEncode = false)
  {
    return $this->api->p($Key, $bHtmlEncode);
  }

  public function pEditable($fieldName, $tag, $attributes = '')
  {
    return $this->api->pEditable($fieldName, $tag, $attributes);
  }

  public function getEditable($fieldName, $tag, $attributes = '')
  {
    return $this->api->getEditable($fieldName, $tag, $attributes);
  }

  public function getCssUrl()
  {
    return $this->api->getCssUrl();
  }

  public function getAssetUrl()
  {
    return $this->api->getAssetUrl();
  }

  public function getAssetPath()
  {
    return $this->api->getAssetPath();
  }

  public function insertJsApi()
  {
    return $this->api->insertJsApi();
  }

  public function insertCss()
  {
    $oldApi = $this->api;
    $result = $this->api->insertCss();
    $this->api = $oldApi;
    return $result;
  }

  public function insertHead()
  {
    $oldApi = $this->api;
    $result = $this->api->insertHead();
    $this->api = $oldApi;
    return $result;
  }

  public function renderHtml()
  {
    return $this->api->renderHtml();
  }

  public function renderCss(&$css)
  {
    return $this->api->renderCss($css);
  }

  public function renderHead()
  {
    return $this->api->renderHead();
  }

  public function &createChildren()
  {
    $children = $this->api->createChildren();
    return $children;
  }

  public function renderChilds()
  {
    return $this->renderChildren();
  }

  public function renderChildren($config = null, $type = self::CODE_TYPE_HTML)
  {
    return $this->api->renderChildren($config, $type);
  }

  public function renderChildrenCss(&$css)
  {
    return $this->api->renderChildrenCss($css);
  }

  public function renderChildrenHead()
  {
    return $this->api->renderChildrenHead();
  }

  public function addFunction($method, $callback)
  {
    return $this->api->addFunction($method, $callback);
    ;
  }

  public function __call($method, $args = array())
  {
    return call_user_func_array(array($this->api, $method), $args);
  }
  
  public function getPreviewUrl()
  {
    return $this->api->getPreviewUrl();
  }

  /**
   * Loads and returns the module translation. Returns null if no translation
   * was found.
   *
   * @return \Dual\Render\ModuleTranslationObject
   */
  public function i18n()
  {
    return $this->api->i18n();
  }

  public function getModuleDataPath()
  {
    return $this->api->getModuleDataPath();
  }
}
