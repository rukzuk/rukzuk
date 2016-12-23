<?php
namespace Rukzuk\Modules;

class rz_style_iconset extends SimpleModule {

  /**
   * Output content for HTML head
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @return string
   */
  public function htmlHead($api, $moduleInfo)
  {
	$html = '<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">';
    return $html;
  }
}
