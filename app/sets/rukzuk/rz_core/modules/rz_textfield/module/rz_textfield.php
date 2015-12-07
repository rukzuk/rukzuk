<?php
namespace Rukzuk\Modules;

class rz_textfield extends SimpleModule
{

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    echo $renderApi->getEditableTag($unit, 'text', 'div', 'class="text"');
  }
}