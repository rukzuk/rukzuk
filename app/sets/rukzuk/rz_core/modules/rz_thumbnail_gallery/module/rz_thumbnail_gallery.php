<?php
namespace Rukzuk\Modules;

class rz_thumbnail_gallery extends SimpleModule
{

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit                 $unit
   * @param \Render\ModuleInfo           $moduleInfo
   *
   * @return ResponsiveImageBuilder
   */
  public function getResponsiveImage($renderApi, $unit, $moduleInfo)
  {
    return new ResponsiveImageBuilder($renderApi, $unit, $moduleInfo);
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit                 $unit
   *
   * @return array
   */
  public function getImageIds($renderApi, $unit)
  {
    $ids = $renderApi->getFormValue($unit, 'galleryImageIds', array());
    if (!is_array($ids)) {
      return array();
    }
    return array_filter($ids);
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit                 $unit
   * @param \Render\ModuleInfo           $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $imageIds = $this->getImageIds($renderApi, $unit);
    $imageQuality = null;
    if ($renderApi->getFormValue($unit, 'enableImageQuality')) {
      $imageQuality = $renderApi->getFormValue($unit, 'imageQuality');
    }
    $globalHeightPercent = str_replace('%', '', $renderApi->getFormValue($unit, 'imgHeight'));

    // render images
    if (count($imageIds) > 0) {
      echo '<ul>';
      foreach ($imageIds as $imageId) {
        try {
          // image
          $image = $renderApi->getMediaItem($imageId)->getImage();
          if ($globalHeightPercent == 0) {
            $heightPercent = $image->getHeight() / $image->getWidth() * 100;
          } else {
            $heightPercent = $globalHeightPercent;
          }
          $cropHeight = ($image->getWidth() * $heightPercent) / 100;

          if ($renderApi->getFormValue($unit, 'showImageTitles')) {
            $name = $renderApi->getMediaItem($imageId)->getName();
            $attributes = array('title' => $name);
          } else {
            $attributes = null;
          }

          // image tag
          $imgTag = $this->getResponsiveImage($renderApi, $unit, $moduleInfo)->getImageTag($image, array('resize' => array('width' => $image->getWidth(), 'height' => $cropHeight), 'quality' => $imageQuality), $attributes);
          echo '<li>' . $imgTag->toString() . '</li>';
        } catch (\Exception $doNothing) {
        }
      }
      echo '</ul>';
    } else if ($renderApi->isEditMode()) {
      // missing input hint
      $i18n = new Translator($renderApi, $moduleInfo);
      echo '<div class="RUKZUKmissingInputHint">';
      echo '<div>';
      echo '<button onclick="javascript:CMS.openFormPanel(\'galleryImageIds\');">';
      echo $i18n->translate('button.missingInputHint', 'Choose images');
      echo '</button>';
      echo '</div>';
      echo '</div>';
    }

    $renderApi->renderChildren($unit);

  }

}
