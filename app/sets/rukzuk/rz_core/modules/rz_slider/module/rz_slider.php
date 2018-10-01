<?php
namespace Rukzuk\Modules;

// namespace Dual\Render;

class rz_slider extends SimpleModule
{

  /**
   * @param HtmlTagBuilder $tag
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function modifyWrapperTag($tag, $api, $unit, $moduleInfo)
  {

    $sliderConfig = array(
      'mode' => $api->getFormValue($unit, 'sliderMode'),
      'speed' => $api->getFormValue($unit, 'sliderSpeed') ? (int)str_replace('ms', '', $api->getFormValue($unit, 'sliderSpeed')) : 500,
      'controls' => $api->getFormValue($unit, 'enableDirectionNav') ? true : false,
      'pager' => $api->getFormValue($unit, 'enableControlNav') ? true : false,
      'adaptiveHeight' => $api->getFormValue($unit, 'enableSmoothHeight') ? true : false,
      'auto' => ($api->getFormValue($unit, 'enableSlideshow') && !$api->isEditMode()) ? true : false,
      'autoHover' => $api->getFormValue($unit, 'enableAutoStopOnHover') ? true : false,
      'pause' => (int)str_replace('s', '', $api->getFormValue($unit, 'slideshowSpeed')) * 1000,
      'infiniteLoop' => ($api->getFormValue($unit, 'enableInfiniteLoop') && !$api->isEditMode()) ? true : false,
      /* display buttons (in edit mode) if infinite loop is enabled */
      'hideControlOnEnd' => !$api->getFormValue($unit, 'enableInfiniteLoop'),
      /* font based icons break support for hidden text on icons */
      'nextText' => '',
      'prevText' => '',
      /* as we inject <style> elements everywhere in edit-mode this setting is important! */
      'slideSelector' => 'li.slide',
      'unitId' => $unit->getId(),
      /* disable css animations in edit mode (required to use handles, eg. multi column box - translate3d breaks position:fixed! */
      'useCSS' => $api->isEditMode(),
      'keyboardEnabled' => ($api->getFormValue($unit, 'enableKeyboard') && !$api->isEditMode()) ? true : false,
      'touchEnabled' => !$api->isEditMode() ? true : false
    );

    $startSlide = $api->getFormValue($unit, 'startSlide');
    if ($startSlide === 'random') {
      $sliderConfig['randomStart'] = true;
    } else {
      $sliderConfig['startSlide'] = (int)$startSlide - 1;
    }

    $tag->set('data-sliderconfig', json_encode($sliderConfig, JSON_HEX_APOS));
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {

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

    // render children (non extensions)
    if (count($renderItems) > 0) {
      echo '<ul class="slides">';
      $i = 0;
      foreach ($renderItems as  $nextUnit) {
        if ($i == 0) {
          echo '<li class="slide slideActive">';
        } else {
          echo '<li class="slide">';
        }
        $i++;
        $renderApi->renderUnit($nextUnit);
        echo '</li>';
      }
      echo '</ul>';
    } else {
      $this->insertMissingInputHint($renderApi, $unit);
    }

    // render extensions
    foreach ($nonRenderItems as $item) {
      $renderApi->renderUnit($item);
    }
  }

  /**
   * Allow loading of require modules in live mode
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getJsModulePaths($api, $moduleInfo)
  {
    $paths = parent::getJsModulePaths($api, $moduleInfo);
    if (is_null($paths)) {
      $paths = array();
    }
    $paths[$moduleInfo->getId()] = $moduleInfo->getAssetUrl();
    return $paths;
  }

}
