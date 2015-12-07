<?php
namespace Rukzuk\Modules;

use Render\APIs\APIv1\CSSAPI;
use Render\ModuleInfo;
use \Render\ModuleInterface;

require_once(dirname(__FILE__) . '/ResponsiveImageBuilder.php');
require_once(dirname(__FILE__) . '/HtmlTagBuilder.php');
require_once(dirname(__FILE__) . '/Translator.php');
require_once(dirname(__FILE__) . '/CacheBuster.php');

/**
 * Class SimpleModule
 * @package Rukzuk\Modules
 */
class SimpleModule implements ModuleInterface
{

  /////////////////////////////////////////////////////////////////////////////
  //
  // public interface for regular modules
  //
  //

	private $cachebusterDisabled = false;
  /**
   * Returns the static module information
   * The recommended way tp influence the content is to override the methods
   * {@link #htmlHead}, {@link #htmlBottom}, {@link #htmlHeadUnit}
   *
   * @param \Render\APIs\APIv1\HeadAPI $api - reference to the module api
   * @param \Render\ModuleInfo $moduleInfo - reference an object which provides the general
   *    module information (e.g. module id, asset url, manifest data, ... etc.)
   *
   * @return array The set of static module information. By default this array
   *  has the following keys:
   *  <ul>
   *  <li>"htmlHead" - a string which will be included in the document's head</li>
   *  <li>"htmlBottom" - a string which will be included at the end of the document's body</li>
   *  <li>"jsModules" - an array of javascript assets which should be included as requireJS module</li>
   *  <li>"jsModulePaths" - </li>
   *
   *  <li>"jsScripts" - an array of javascript assets which should be included as script tags</li>
   *  </ul>
   */
  public function provideModuleData($api, $moduleInfo)
  {
    $i18n = $this->getI18n($api, $moduleInfo);

    return array(
      'htmlHead' => $this->htmlHead($api, $moduleInfo),
      'htmlBottom' => $this->htmlBottom($api, $moduleInfo),

      'jsModules' => $this->getJsModules($api, $moduleInfo),
      'jsModulePaths' => $this->getJsModulePaths($api, $moduleInfo),
      'jsScripts' => $this->getJsScripts($api, $moduleInfo),
      'cssAssets' => $this->getCssAssets($api, $moduleInfo),
      'i18n' => $i18n,
      'selectors' => $this->getSelectors($i18n, $moduleInfo),
      'assetUrl' => $moduleInfo->getAssetUrl()
    );
  }

  /**
   * Provide unit specific data
   *
   * @param \Render\APIs\APIv1\CSSAPI $api - reference to the module api
   * @param \Render\Unit $unit - an object which provides general unit specific
   *    data (e.g. unit id, form values, ... etc.)
   * @param \Render\ModuleInfo $moduleInfo - reference an object which provides
   *    the general module information (e.g. module id, asset url, manifest
   *    data, ... etc.)
   *
   * @return array
   */
  public function provideUnitData($api, $unit, $moduleInfo)
  {
    $arr = array(
      'redirect' => $this->httpRedirect($api, $unit, $moduleInfo),
      'htmlHead' => $this->htmlHeadUnit($api, $unit, $moduleInfo),
      'dyncss' => $this->getDynCSSConfig($api, $unit, $moduleInfo),
    );

    return $arr;
  }

  /**
   * Creates the css code for a single unit and writes it to output buffer.
   * In general it is not necessary to override this method because the root
   * module creates the css for all its children using the dynamic CSS
   * settings (see {@link provideModuleData} and {@link provideUnitData}).
   *
   * @param \Render\APIs\APIv1\CSSAPI $api - reference to the module api
   * @param \Render\Unit $unit - an object which provides general unit specific
   *    data (e.g. unit id, form values, ... etc.)
   * @param \Render\ModuleInfo $moduleInfo - reference an object which provides
   *    the general module information (e.g. module id, asset url, manifest
   *    data, ... etc.)
  */
  public function css($api, $unit, $moduleInfo) {}

  /**
   * Creates the html code for a single unit and writes it to output buffer.
   * This is an empty implementation and should be overridden if you want
   * to output any html code.
   *
   * @param \Render\APIs\APIv1\RenderAPI $renderApi - reference to the module api
   * @param \Render\Unit $unit - an object which provides general unit specific
   *    data (e.g. unit id, form values, ... etc.)
   * @param \Render\ModuleInfo $moduleInfo - reference an object which provides
   *    the general module information (e.g. module id, asset url, manifest
   *    data, ... etc.)
   */
  public function render($renderApi, $unit, $moduleInfo)
  {

    if ($moduleInfo->isExtension()) {
      return;
    }

    $tag = new HtmlTagBuilder('div');
    $tag->set('id', $unit->getId());
    $tag->addClass($moduleInfo->getId());
    $tag->addClass('isModule');
    $tag->addClass($unit->getHtmlClass());

    // call hook
    $this->modifyWrapperTag($tag, $renderApi, $unit, $moduleInfo);

    echo $tag->getOpenString();
    $this->renderContent($renderApi, $unit, $moduleInfo);
    echo $tag->getCloseString();
  }

  /**
   * Wrapper Tag Hook, you can modify the default wrapper tag attributes, classes etc.
   * @see render()
   * @param HtmlTagBuilder $tag
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  protected function modifyWrapperTag($tag, $renderApi, $unit, $moduleInfo)
  {

  }

  /**
   * Render Module Content which is wrapped by wrapper
   *
   * @see $tag
   * @see $classes
   * @see $attributes
   *
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  protected function renderContent($renderApi, $unit, $moduleInfo)
  {
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param \Render\Unit $unit
   */
  protected function insertMissingInputHint($api, $unit)
  {

    // only available in edit mode
    if (!$api->isEditMode()) {
      return;
    }

    $isPage = $api->isPage();
    $isGhostContainer = $unit->isGhostContainer();
    $isEmpty = true;

    // check if this module contains any other module which is not a extension module
    // if we have children, search for non extension modules
    $children = $api->getChildren($unit);
    foreach ($children as $nextUnit) {
      if (!$api->getModuleInfo($nextUnit)->isExtension()) {
        $isEmpty = false;
        break;
      }
    }

    $html = '';
    // module has no children and we are not in page mode
    if ($isEmpty && !$isPage) {
      $html = '<div class="RUKZUKemptyBox"></div>';
    } // page mode and the module is a ghost container
    else if ($isPage && $isGhostContainer) {
      $id = $unit->getId();
      $i18n = new Translator($api, $api->getModuleInfo($unit));
      $title = $i18n->translate('button.missingInputHintTitle', 'Click to insert module');

      $html .= '<div class="' . ($isEmpty ? ' RUKZUKemptyBox' : 'RUKZUKaddModuleBox') . '">';
      $html .= '	<div class="RUKZUKmissingInputHint">';
      $html .= '	  <div>';
      $html .= '		<button class="add" onclick="javascript:CMS.openInsertWindow(\'' . $id . '\', 0);" title="' . $title . '"></button>';
      $html .= '	  </div>';
      $html .= '	</div>';
      $html .= '</div>';
    }

    echo $html;
  }

  /////////////////////////////////////////////////////////////////////////////
  //
  // protected methods
  // (Intended to be override by module implementations)
  //
  //

  /**
   * HTML Head - insert custom tags in <head> of the page
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   * @noinspection PhpUnusedParameterInspection
   */
  protected function htmlHead($api, $moduleInfo)
  {
    return '';
  }

  /**
   * HTML Bottom - insert custom tags right before </body>
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   */
  protected function htmlBottom($api, $moduleInfo)
  {
    return '';
  }


  /**
   * HTML Head - insert custom tags in <head> of the page
   * This method is called for each unit.
   * NOTE: You need to make sure to insert the same <link> or <script> only once
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   * @noinspection PhpUnusedParameterInspection
   */
  protected function htmlHeadUnit($api, $unit, $moduleInfo)
  {
    return '';
  }

  /**
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getJsModules($api, $moduleInfo)
  {
    return $this->getJsAssets($api, 'module', $moduleInfo);
  }

  /**
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getJsScripts($api, $moduleInfo)
  {
    return $this->getJsAssets($api, 'script', $moduleInfo);
  }

  /**
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getJsModulePaths($api, $moduleInfo)
  {
    $paths = null;

    if ($api->isEditMode()) {
      $paths = array();
      $paths[$moduleInfo->getId().'/notlive'] = $moduleInfo->getAssetUrl().'/notlive';
    }
    return $paths;
  }

  /**
   * Default implementation for CSS Assets reads them from the manifest xm (extended meta) section
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getCssAssets($api, $moduleInfo)
  {
	$cachebuster = new CacheBuster();
	$cachebuster->setModuleManifest( $moduleInfo->getManifest() );
    $customData = $moduleInfo->getCustomData();
    $cssAssets = $this->getValue('assets.css', $customData, array());
    $result = array();
    foreach ($cssAssets as $asset) {
      $mode = $this->getValue('mode', $asset, '');

      if ($this->isAvailableInMode($mode, $api)) {
		  if(!$this->cachebusterDisabled){
			  $cachebuster = new CacheBuster();
			  $cachebuster->setModuleManifest( $moduleInfo->getManifest() );
			  $result[] = $moduleInfo->getAssetUrl($cachebuster->suffix($this->getValue('file', $asset)));
		  }else{
			  $result[] = $moduleInfo->getAssetUrl($this->getValue('file', $asset));
		  }
      }
    }
    return $result;
  }

  /**
   * Default implementation of i18n strings (loaded from xm section of manifest)
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getI18n($api, $moduleInfo)
  {
    $customData = $moduleInfo->getCustomData();
    $i18nList = $this->getValue('i18n', $customData, array());
    return $i18nList;
  }

  /**
   * Default implementation
   * @param array $i18n - list of key => replaced with i18n strings
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getSelectors($i18n, $moduleInfo)
  {
    $customData = $moduleInfo->getCustomData();
    $selectors = $this->getValue('selectors', $customData);
    if (is_array($selectors)) {
      $this->translateSelectors($selectors, $i18n);
    }
    return $selectors;
  }

  /**
   * Build the DynCSS config array for this unit
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getDynCSSConfig($api, $unit, $moduleInfo)
  {
    // performance improvement as we cache the
    // css in live mode so skip gathering of data
    if ($api->isLiveMode()) {
      return array();
    }

    // selector
    $result = array(
      'isExtension' => $moduleInfo->isExtension()
    );

    if (!$moduleInfo->isExtension()) {
      // use ID as selector if not extension module
      $result['selector'] = $this->getDynCSSSelectorContext($api, $unit);
    }

    // add additional selector
    $additionalSelectorFormValue = $api->getFormValue($unit, 'additionalSelector');
    if (!is_null($additionalSelectorFormValue)) {
        $result['dynamicSelector'] = 'additionalSelector';
    }

    // provide data for the dyn css generate code (plugin)
    $result['plugin'] = $this->getDynCSSPlugin($moduleInfo);

    // add formValues if required (is a selector or has dyn css plugin)
    if (isset($result['dynamicSelector']) ||  $result['plugin']) {
      $result['formValues'] = $unit->getFormValues();
    }

    return $result;
  }


  /////////////////////////////////////////////////////////////////////////////
  //
  // helper
  //
  //

  /**
   * Retrieves a value from a given array
   *
   * @param string $keyPath The key to the value or the path within a nested
   *    array separated by "." (e.g. "foo.bar.baz")
   * @param array $dataArray The data array
   * @param mixed $defaultValue Optional. A default value which will be returned
   *    if the value was not found
   * @return mixed The found value
   */
  protected function getValue($keyPath, $dataArray, $defaultValue = null)
  {
    $keys = explode('.', $keyPath);
    $result = $dataArray;

    while (count($keys) > 0) {
      $key = array_shift($keys);
      if (is_array($result) && array_key_exists($key, $result)) {
        $result = $result[$key];
      } elseif (is_object($result) && isset($result->$key)) {
        $result = $result->$key;
      } else {
        $result = $defaultValue;
        break;
      }
    }

    return $result;
  }

  /**
   * Checks if the given mode matches the current execution context (determined
   * by the current module API), e.g. "edit" is available if and only if
   * <code>$api->isEditMode === TRUE</code>
   *
   * @param string $mode The requested mode (e.g. "edit", "live", ...)
   * @param \Render\APIs\APIv1\HeadAPI $api The current module api
   * @return boolean TRUE if and only if the requested mode is available
   */
  protected function isAvailableInMode($mode, $api)
  {
    if ($mode === 'edit') {
      return $api->isEditMode();
    } else if ($mode === 'live') {
      return !$api->isEditMode();
    } else {
      return TRUE;
    }
  }

  /**
   * @param string $key
   * @param Array[] $i18nList array('key' => array('de' => 'val', 'en' => 'val2'))
   * @return array|string
   */
  private function i18nGetArrayByKey($key, $i18nList)
  {
    if (isset($i18nList[$key])) {
      // translation found
      return $i18nList[$key];
    }
    return $key;
  }

  private function i18nMacroKeyTranslate($macroKey, $i18nList)
  {
    $macro = '__i18n_';
    // macro key needs to start with macro
    if (strpos($macroKey, $macro)  === 0) {
      // remove macro from key and lookup i18n list
      $key = substr($macroKey, strlen($macro));
      return $this->i18nGetArrayByKey($key, $i18nList);
    }
    // just return macro key if we haven't found any translation
    return $macroKey;
  }

  private function translateSelectors(array &$selectors, $i18n)
  {
    foreach($selectors as &$sel) {
      $sel['title'] = $this->i18nMacroKeyTranslate($sel['title'], $i18n);
      if (isset($sel['items'])) {
        $this->translateSelectors($sel['items'], $i18n);
      }
    }
  }

  /**
   * Returns all parent unit Ids until root is reached (including root)
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   * @return array
   */
  private function getAllParentUnitIds($api, $unit)
  {
    $unitIdPath = array();
    do {
      $unitIdPath[] = $unit->getId();
    } while ($unit = $api->getParentUnit($unit));
    return array_reverse($unitIdPath);
  }

  /**
   * Get array with complete selector context (i.e. all module ids to root)
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   * @return array
   */
  private function getDynCSSSelectorContext($api, $unit)
  {
    $unitIdPath = $this->getAllParentUnitIds($api, $unit);
    return array_map(function ($item) {
      return '#' . $item;
    }, $unitIdPath);
  }

  /**
   * DynCSS Plugin
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  private function getDynCSSPlugin($moduleInfo)
  {
    if (!isset($this->_dynCssPlugin)) {
      // look for a dynCSS file (this can be in an extension/style as well as an ordinary module)
      $dynCssFile = 'notlive/css.js';
      $dynCssAssetPath = $moduleInfo->getAssetPath($dynCssFile);
      if (is_file($dynCssAssetPath)) {
        $result = array();
        $result['path'] = $dynCssAssetPath;
        $result['url'] = $moduleInfo->getAssetUrl($dynCssFile);
        $result['name'] = $moduleInfo->getId();
        $result['mtime'] = filemtime($dynCssAssetPath);
      } else {
        $result = null;
      }
      $this->_dynCssPlugin = $result;
    }
    return $this->_dynCssPlugin;
  }

  /**
   * Returns the available js assets defined in the module manifest dependent
   * on a given type ("module" or "script") and the current mode of execution
   * @private
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param string $assetType
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  private function getJsAssets($api, $assetType, $moduleInfo)
  {
    $customData = $moduleInfo->getCustomData();
    $jsAssets = $this->getValue('assets.js', $customData, array());
    $result = array();
    foreach ($jsAssets as $asset) {
      $mode = $this->getValue('mode', $asset, '');
      $type = $this->getValue('type', $asset, 'script');

      if ($type === $assetType && $this->isAvailableInMode($mode, $api)) {
		  if(!$this->cachebusterDisabled){
			  $cachebuster = new CacheBuster();
			  $cachebuster->setModuleManifest( $moduleInfo->getManifest() );
			  $result[] = $moduleInfo->getAssetUrl($cachebuster->suffix($this->getValue('file', $asset)));
		  }else{
			  $result[] = $moduleInfo->getAssetUrl($this->getValue('file', $asset));
		  }
      }
    }
    return $result;
  }

  public function disableCacheBuster(){
	$this->cachebusterDisabled = true;
  }

  /**
   * HTTP Header based Redirect support.
   * This should return an array with the key 'url'
   *
   * @param \Render\APIs\APIv1\CSSAPI $api - reference to the module api
   * @param \Render\Unit $unit - an object which provides general unit specific
   *    data (e.g. unit id, form values, ... etc.)
   * @param \Render\ModuleInfo $moduleInfo - reference an object which provides
   *    the general module information (e.g. module id, asset url, manifest
   *    data, ... etc.)
   */
  protected function httpRedirect($api, $unit, $moduleInfo)
  {
    return;
  }
}
