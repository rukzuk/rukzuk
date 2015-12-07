<?php
namespace Rukzuk\Modules;

class rz_svg extends SimpleModule
{

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $imgClass = '';
    $svgUrl = $moduleInfo->getAssetUrl('imageBlank.svg');
    $svgMedia = $renderApi->getFormValue($unit, 'svg');
    if ($svgMedia == '') {
      $imgClass = 'blankImgPlaceholder';
    } else {
      try {
        $svgUrl = $renderApi->getMediaItem($svgMedia)->getUrl();
      } catch (\Exception $e) {

      }
    }

    $svgImgTag = new HtmlTagBuilder('img', array(
      'src' => $svgUrl,
      'alt' => $renderApi->getFormValue($unit, 'svgAlt')
    ));

    if (!empty($imgClass)) {
      $svgImgTag->set('class', $imgClass);
    }

    $svgTitle = $renderApi->getFormValue($unit, 'svgTitle');
    if (!empty($svgTitle)) {
      $svgImgTag->set('title', $svgTitle);
    }

    echo $svgImgTag->toString();

    $renderApi->renderChildren($unit);
  }
}
