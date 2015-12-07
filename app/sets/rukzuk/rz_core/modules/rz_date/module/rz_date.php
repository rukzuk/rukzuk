<?php
namespace Rukzuk\Modules;

/**
 * Simple Date Module
 * @package Rukzuk\Modules
 */
class rz_date extends SimpleModule
{
  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $timestamp = $renderApi->getFormValue($unit, 'date', 0);
    $dateFormat = $renderApi->getFormValue($unit, 'dateFormat');
    $datetime = strftime('%F', $timestamp);
    setlocale(LC_ALL, 0);
    $datetimeString = strftime($dateFormat, $timestamp);
    if ($timestamp > 0) {
      echo '<time datetime="' . $datetime . '">' . $datetimeString . '</time>'; //insert pubdate attribute?
    }

    $renderApi->renderChildren($unit);
  }
}
