<?php
namespace Rukzuk\Modules;

// namespace Dual\Render;

class rz_slider_images extends SimpleModule
{

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
      'keyboardEnabled' => ($api->getFormValue($unit, 'enableKeyboard') && !$api->isEditMode()) ? true : false
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

    $responsiveImage = new ResponsiveImageBuilder($renderApi, $unit, $moduleInfo);

    $imageIds = $renderApi->getFormValue($unit, 'sliderImageIds', array());

    $imageQuality = null;
    if ($renderApi->getFormValue($unit, 'enableImageQuality')) {
      $imageQuality = $renderApi->getFormValue($unit, 'imageQuality');
    }

    $globalHeightPercent = str_replace('%', '', $renderApi->getFormValue($unit, 'imgHeight'));

    // render children (non extensions)
    if (count($imageIds) > 0) {
      echo '<ul class="slides">';
      $i = 0;
      foreach ($imageIds as  $imageId) {

        // image
		try {
			$image = $renderApi->getMediaItem( $imageId )->getImage();
			if( $globalHeightPercent == 0 ) {
				$heightPercent = $image->getHeight() / $image->getWidth() * 100;
			} else {
				$heightPercent = $globalHeightPercent;
			}
			$cropHeight = ( $image->getWidth() * $heightPercent ) / 100;
			// slides
			if( $i == 0 ) {
				echo '<li class="slide slideActive">';
			} else {
				echo '<li class="slide">';
			}
			$i++;
			// image tag
			$imgTag = $responsiveImage->getImageTag( $image, array( 'resize' => array( 'width' => $image->getWidth(), 'height' => $cropHeight ), 'quality' => $imageQuality ) );
			if(isset($imgTag)) {
				echo $imgTag->toString();
			}
			echo '</li>';
		}catch(\Exception $e){
		}
      }
      echo '</ul>';
    } else if ($renderApi->isEditMode()) {
      // missing input hint
      $i18n = new Translator($renderApi, $moduleInfo);

      echo '<div class="RUKZUKmissingInputHint">';
      echo '<div>';
      echo '<button onclick="javascript:CMS.openFormPanel(\'sliderImageIds\');">';
      echo $i18n->translate('button.missingInputHint', 'Choose images');
      echo '</button>';
      echo '</div>';
      echo '</div>';
    }

    $renderApi->renderChildren($unit);

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
