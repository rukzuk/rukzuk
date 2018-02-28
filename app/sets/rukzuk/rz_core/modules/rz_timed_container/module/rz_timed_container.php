<?php
namespace Rukzuk\Modules;

class rz_timed_container extends SimpleModule
{

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {

    $start = 0;
    if ($renderApi->getFormValue($unit, 'enableDateStart')) {
      $start = intval($renderApi->getFormValue($unit, 'dateStart')) + (intval($renderApi->getFormValue($unit, 'hourStart')) * 3600) + (intval($renderApi->getFormValue($unit, 'minuteStart')) * 60);

    }

    $end = 9999999999;
    if ($renderApi->getFormValue($unit, 'enableDateEnd')) {
      $end = intval($renderApi->getFormValue($unit, 'dateEnd')) + (intval($renderApi->getFormValue($unit, 'hourEnd')) * 3600) + (intval($renderApi->getFormValue($unit, 'minuteEnd')) * 60);

    }
    $currentDate = getdate();
    $showChildren = false;

    if (($start < $currentDate[0]) && ($end > $currentDate[0])) {
      if ($renderApi->getFormValue($unit, 'enableWeekday')) {
        $currentWeekday = strtolower($currentDate['weekday']);
        if ($renderApi->getFormValue($unit, $currentWeekday)) {
          $showChildren = true;
        }
      } else {
        $showChildren = true;
      }
    }

    if ($showChildren) {
      $renderApi->renderChildren($unit);
    }
  }
}
