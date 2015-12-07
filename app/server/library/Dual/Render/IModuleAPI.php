<?php
namespace Dual\Render;

interface IModuleAPI
{
  public function getParent();

  public function getMode();

  public function isTemplate();

  public function isPage();

  public function set($Key, $Value);

  public function get($key);

  public function getId();

  public function getName();

  public function isGhostContainer();

  public function getTemplateUnitId();

  public function getValues();

  public function getModuleAttributes();

  public function getModuleAttribute($name);

  public function getModuleId();

  public function getModuleName();

  public function getModuleType();

  public function getModuleVersion();

  public function getWebsiteId();

  public function getChildren();

  public function getRendererCode();

  public function getCssCode();

  public function getHeadCode();

  public function p($Key, $bHtmlEncode = false);

  public function pEditable($fieldName, $tag, $attributes = '');

  public function getEditable($fieldName, $tag, $attributes = '');

  public function getCssUrl();

  public function getAssetUrl();

  public function getAssetPath();

  public function getModuleDataPath();

  public function insertJsApi();

  public function insertCss();  // ???

  public function insertHead();  // ??? is used

  public function renderHtml();

  public function renderCss(&$css);

  public function renderHead();

  public function &createChildren();

  public function renderChildren($config = null, $type = self::CODE_TYPE_HTML);

  public function renderChildrenCss(&$css); // ???

  public function renderChildrenHead(); // ???

  public function addFunction($method, $callback);

  public function __call($method, $args = array());

  public function getPreviewUrl();

  /**
   * Loads and returns the module translation. Returns null if no translation
   * was found.
   *
   * @return \Dual\Render\ModuleTranslationObject
   */
  public function i18n();
}
