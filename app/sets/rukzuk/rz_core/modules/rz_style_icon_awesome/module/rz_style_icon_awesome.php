<?php
namespace Rukzuk\Modules;

class rz_style_icon_awesome extends SimpleModule { 

  /**
   * Output content for HTML head
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   */
  public function htmlHeadUnit($api, $unit, $moduleInfo)
  {
	$html = '<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">';
    return $html;
  }
}