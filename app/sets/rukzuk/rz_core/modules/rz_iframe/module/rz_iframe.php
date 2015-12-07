<?php
namespace Rukzuk\Modules;

class rz_iframe extends SimpleModule
{

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $src = $renderApi->getFormValue($unit, 'iframeSrc', '');
    if (preg_match('/^http[s]?:\/\//', $src)) {
      $htb = new HtmlTagBuilder('iframe', array(
        'data-src' => $src,
        'class' => 'lazyload'
      ));
      echo $htb->toString();
    }

    $renderApi->renderChildren($unit);
  }
}
