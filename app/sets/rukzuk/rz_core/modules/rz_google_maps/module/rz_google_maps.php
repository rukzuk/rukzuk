<?php
namespace Rukzuk\Modules;

class rz_google_maps extends SimpleModule {

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo) {

    $apiKey = $renderApi->getFormValue($unit, 'googleMapsApiKey');
    // use rukzuk key if user didn't enter own key
    if (empty($apiKey)) {
      $apiKey = 'AIzaSyBRaLr53MrNTKCGqdf7b2VpJNITysGPpPw';
    }

    $googleMapsUrl = 'https://www.google.com/maps/embed/v1/place?key=' . $apiKey
                   . '&q=' . urlencode($renderApi->getFormValue($unit, 'address'))
                   . '&zoom=' . $renderApi->getFormValue($unit, 'zoom')
                   . '&maptype=' . $renderApi->getFormValue($unit, 'maptype');

    $htb = new HtmlTagBuilder('div', null, array( // extra wrapper needed for rz_style_padding_margin
      new HtmlTagBuilder('iframe', array(
        'data-src' => $googleMapsUrl,
        'class' => 'lazyload'
      )))
    );
    echo $htb->toString();

    $renderApi->renderChildren($unit);
  }

}
