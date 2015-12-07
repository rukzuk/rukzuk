<?php
namespace Rukzuk\Modules;

class rz_style_animation_scroll extends SimpleModule {

  /**
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   */
  protected function htmlHead($api, $moduleInfo) {
    return "<script>window.rz_style_animation_scroll = [];</script>";
  }

  /**
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   */
  protected function htmlHeadUnit($api, $unit, $moduleInfo) {
    $parentUnit = $api->getParentUnit($unit);

    // enable animation only if this extension unit is a direct child of default unit
    if (!$api->getModuleInfo($parentUnit)->isExtension()) {
      $selector = '#' . $parentUnit->getId();
      return "<script>window.rz_style_animation_scroll.push('" . $selector . "');</script>";
    } else {
      if ($api->isEditMode()) {
        $i18n = new Translator($api, $moduleInfo);
        $msg = $i18n->translate('error.insideExtensionModule');
        return '<script>alert("' . addslashes($msg) . '");</script>';
      }
    }
  }

  /**
   * Allow loading of require modules in live mode
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getJsModulePaths($api, $moduleInfo) {
    $paths = parent::getJsModulePaths($api, $moduleInfo);
    if (is_null($paths)) {
      $paths = array();
    }
    $paths[$moduleInfo->getId()] = $moduleInfo->getAssetUrl();
    return $paths;
  }
}
