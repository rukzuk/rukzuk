<?php
namespace Rukzuk\Modules;

class rz_image extends SimpleModule
{
  protected function getResponsiveImageTag($api, $unit, $moduleInfo)
  {
    $mediaId = $api->getFormValue($unit, 'imgsrc');
    $image = null;
    $modifications = array();

    if (!empty($mediaId)) {
      try {
        $image = $api->getMediaItem($mediaId)->getImage();
        $modifications = $this->getImageModifications($api, $unit, $image);
      } catch (\Exception $doNothing) {
        $image = null;
        $modifications = array();
      }
    }

    $responsiveImageBuilder = new ResponsiveImageBuilder($api, $unit, $moduleInfo);
    $attr = array(
      'class' => 'imageModuleImg',
      'alt' => $api->getFormValue($unit, 'imageAlt')
    );
    if ($api->getFormValue($unit, 'imageTitle', '') != '') {
      $attr['title'] = $api->getFormValue($unit, 'imageTitle');
    }
    return $responsiveImageBuilder->getImageTag($image, $modifications, $attr);
  }

  protected function renderContent($api, $unit, $moduleInfo)
  {
    echo $this->getResponsiveImageTag($api, $unit, $moduleInfo)->toString();
    if ($api->isEditMode()) {
      $i18n = new Translator($api, $moduleInfo);
      $title = $i18n->translate('button.cropIconTitle');
      echo '<div class="cropIcon" title="' . $title . '"></div>';
    }

    $api->renderChildren($unit);
  }

  private function getImageModifications($api, $unit, $image)
  {
    $modifications = array();
    $width = $image->getWidth();
    $height = $image->getHeight();

    // apply cropping
    $cropData = $this->getCropData($api, $unit);
    $cropWidth = $width * $cropData['widthRatio'];
    $orgCropHeight = $height * $cropData['heightRatio'];

    $heightPercent = (int)str_replace('%', '', $api->getFormValue($unit, 'imgHeight', '0%'));
    if ($heightPercent === 0) {
      $heightPercent = $height / $width * 100;
    }

    $cropX = $width * $cropData['xRatio'];
    $cropY = $height * $cropData['yRatio'];
    $cropHeight = $cropWidth * ($heightPercent / 100);

    // resize image if crop data make no sense
    if ($cropX == 0 && $cropY == 0 && $width == $cropWidth && $height == $orgCropHeight) {
      $modifications['resize'] = array(
        'width' => $cropWidth,
        'height' => $cropHeight,
      );
    } else {
      $modifications['crop'] = array(
        'x' => $cropX,
        'y' => $cropY,
        'width' => $cropWidth,
        'height' => $cropHeight,
      );
    }

    // apply quality
    if ($api->getFormValue($unit, 'enableImageQuality')) {
      $modifications['quality'] = $api->getFormValue($unit, 'imageQuality');
    }

    return $modifications;
  }

  private function getCropData($api, $unit)
  {
    $cropData = array(
      'xRatio' => 0,
      'yRatio' => 0,
      'widthRatio' => 1,
      'heightRatio' => 1,
    );

    $orgCropData = json_decode($api->getFormValue($unit, 'cropData'), true);
    if (is_array($orgCropData)) {
      if (array_key_exists('cropXRatio', $orgCropData) && !empty($orgCropData['cropXRatio'])) {
        $cropData['xRatio'] = $orgCropData['cropXRatio'];
      }
      if (array_key_exists('cropYRatio', $orgCropData) && !empty($orgCropData['cropYRatio'])) {
        $cropData['yRatio'] = $orgCropData['cropYRatio'];
      }
      if (array_key_exists('cropWidthRatio', $orgCropData) && !empty($orgCropData['cropWidthRatio'])) {
        $cropData['widthRatio'] = $orgCropData['cropWidthRatio'];
      }
      if (array_key_exists('cropHeightRatio', $orgCropData) && !empty($orgCropData['cropHeightRatio'])) {
        $cropData['heightRatio'] = $orgCropData['cropHeightRatio'];
      }
    }

    return $cropData;
  }
}
