<?php
namespace Rukzuk\Modules;

class rz_html extends SimpleModule
{

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    echo $renderApi->getFormValue($unit, 'htmlCode');
    $renderApi->renderChildren($unit);
  }

  /**
   * Output content for HTML head
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   */
  public function htmlHeadUnit($api, $unit, $moduleInfo)
  {
    return $api->getFormValue($unit, 'headCode');
  }
}
