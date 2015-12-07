<?php
namespace Rukzuk\Modules;

class rz_headline extends SimpleModule
{

  public function modifyWrapperTag($tag, $renderApi, $unit, $moduleInfo)
  {
    $htmlElement = $renderApi->getFormValue($unit, 'htmlElement');
    $tag->setTagName($htmlElement);
    $tag->addClass('headline' . substr($htmlElement, -1));
  }

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $hNum = substr($renderApi->getFormValue($unit, 'htmlElement', '0'), -1);
    echo $renderApi->getEditableTag($unit, 'text', 'span', 'class="headline' . $hNum . 'Text"');
    $renderApi->renderChildren($unit);
  }
}
