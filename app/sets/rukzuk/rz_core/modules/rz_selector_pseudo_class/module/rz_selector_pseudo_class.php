<?php
namespace Rukzuk\Modules;

class rz_selector_pseudo_class extends SimpleModule {

  public function htmlHeadUnit($api, $unit, $moduleInfo)
  {
    $parentUnit = $api->getParentUnit($unit);
    $selector = '#'.$parentUnit->getId();
    $stateName = $api->getFormValue($unit, 'pseudoClass');


    // enable animation only if this extension unit is a direct child of default unit
    if ((!$api->getModuleInfo($parentUnit)->isExtension()) && ($stateName != "")) {
      $code = '<script>$(function() { $("' . $selector . '").addClass("listen_' . $stateName . '")});</script>';
      return $code;
    }
  }

}
