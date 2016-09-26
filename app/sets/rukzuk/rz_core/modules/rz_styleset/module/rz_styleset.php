<?php
namespace Rukzuk\Modules;

class rz_styleset extends SimpleModule {

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

    $selector = $api->getFormValue($unit, 'cssStyleSet');
    if ($selector != '') {
      $result['selector'] = array('.' . $selector);
    } else {
      echo "hier";
    }

    return $result;
  }

}
