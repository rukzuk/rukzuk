<?php
namespace Rukzuk\Modules;

class rz_style_background_video extends SimpleModule {

  public function htmlHeadUnit($api, $unit, $moduleInfo) {
    // enable event only if this extension unit is a direct child of default unit
    $parentUnit = $api->getParentUnit($unit);
    if ($api->getModuleInfo($parentUnit)->isExtension() || ($api->getModuleInfo($parentUnit)->getId() == "rz_styleset")) {
      if ($api->isEditMode()) {
        $i18n = new Translator($api, $moduleInfo);
        $msg = $i18n->translate('error.insideExtensionModule');
        $code = 'alert("' . addslashes($msg) . '");';
      }
    } else {
      $parentUnitId  = $parentUnit->getId();
      $mp4 = $api->getFormValue($unit, 'cssMp4');
      if ($mp4 != '') {
        $mp4Url = $api->getMediaItem($api->getFormValue($unit, 'cssMp4'))->getUrl();
        $mute = $api->getFormValue($unit, 'cssMute');
        $loop = $api->getFormValue($unit, 'cssLoop');
        $playbackRate = $api->getFormValue($unit, 'cssSpeed');
        $code = "$(function() { $('#".$parentUnitId."').vide({mp4:'".$mp4Url."'},{playbackRate:".$playbackRate.",muted:".$mute.",loop:".$loop."}); });";
      }

    }
    return "<script>".$code."</script>";
  }

}
