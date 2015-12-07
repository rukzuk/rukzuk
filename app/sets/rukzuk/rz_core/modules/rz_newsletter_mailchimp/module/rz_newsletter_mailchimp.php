<?php
namespace Rukzuk\Modules;

class rz_newsletter_mailchimp extends SimpleModule
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
}
