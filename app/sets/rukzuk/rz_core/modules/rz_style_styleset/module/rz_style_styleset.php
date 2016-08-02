<?php
namespace Rukzuk\Modules;

class rz_style_styleset extends SimpleModule {

  public function htmlHeadUnit($api, $unit, $moduleInfo) {
    // enable event only if this extension unit is a direct child of default unit
    $parentUnit = $api->getParentUnit($unit);
    if ($api->getModuleInfo($parentUnit)->isExtension()) {
      if ($api->isEditMode()) {
        $i18n = new Translator($api, $moduleInfo);
        $msg = $i18n->translate('error.insideExtensionModule');
        $code = 'alert("' . addslashes($msg) . '");';
      }
      return "<script>".$code."</script>";
    }

  }

}
