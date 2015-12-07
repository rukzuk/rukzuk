<?php
namespace Rukzuk\Modules;

class rz_box extends SimpleModule
{

  /**
   * Returns a list of values and resolution Ids
   * @param $api
   * @param $unit
   * @param $key
   * @return array
   */
  private function getResponsiveValue($unit, $key, $api) {
    $fv = $api->getFormValue($unit, $key);

    // check if the formValue has responsive values
    if(is_array($fv) && isset($fv['type']) && $fv['type'] === 'bp') {
      $result = array();
      $resolutions = $api->getResolutions();

      // TODO: check if default should be part of the resolutions data
      $result[] = array('value' => $fv['default'] , 'id' => 'default');
      if ($resolutions['enabled']) {
        foreach($resolutions['data'] as $res) {
          if(isset($res['id']) && isset($fv[$res['id']])) {
            $result[] = array('value' => $fv[$res['id']] , 'id' => $res['id']);
          }
        }
      }
      return $result;
    }
    // TODO: maybe its better to return null in the case of non responsive value?
    return array(array('value' => $fv, 'id' => 'default'));
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function render($renderApi, $unit, $moduleInfo)
  {
    $classes = array($moduleInfo->getId(), 'isModule', 'isColumnBox', htmlspecialchars($unit->getHtmlClass(), ENT_QUOTES, 'UTF-8'));

    // show add module button in page mode if this is a ghost container
    if ($renderApi->isEditMode() && $renderApi->isPage() && $unit->isGhostContainer()) {
      $classes[] = 'showAddModuleButton';
    }

    // Dimensions, Grid, Responsive-Settings
    $i = 0;
    $resolutions = $renderApi->getResolutions();
    $childWidth = $this->getResponsiveValue($unit, 'cssChildWidth', $renderApi);
    $maxCols = 0;
    $cols = array();

    foreach ($childWidth as $widths) {
      $col = count(explode(' ', trim($widths['value'])));
      $cols[] = $col;

      if ($col > $maxCols) {
        $maxCols = $col;
      }
    }

    $allItems = $renderApi->getChildren($unit);
    $renderItems = array(); // normal units
    $nonRenderItems = array(); // extension units
    foreach ($allItems as $item) {
      if ($renderApi->getModuleInfo($item)->isExtension()) {
        // assume that extension modules (i.e. styles) render no html output
        $nonRenderItems[] = $item;
      } else {
        $renderItems[] = $item;
      }
    }

    $counter = max($maxCols, count($renderItems));
    $unitId = $unit->getId();
    $classesStr = implode(' ', $classes);

    echo "<div id='{$unitId}' class='{$classesStr}'><div class='isColumnBoxTable wkFixTableLayout'>";

    for ($i = 0; $i < $counter; $i++) {
      if (array_key_exists($i, $renderItems)) {
        // cell has an item
        echo '<div class="isColumnBoxCell">';
        $renderApi->renderUnit($renderItems[$i]);
        echo '</div>';

        if ($i === (count($renderItems) - 1)) {
          echo '<div class="boxSpacer boxSpacerLast"></div>';
        } else {
          echo '<div class="boxSpacer"></div>';
        }
      } else {
        // empty cell
        echo '<div class="boxPreview isColumnBoxCell">';
        if ($renderApi->isEditMode()) {
          echo '<div class="RUKZUKemptyBox"></div>';
        }
        echo '</div>';
        echo '<div class="boxSpacer boxPreviewSpacer"></div>';
      }
    }

    echo '</div></div>';
  }
}
