<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;

use \Rukzuk\Modules\rz_google_maps;

require_once(MODULE_PATH.'/rz_google_maps/module/rz_google_maps.php');

class rz_google_maps_RenderTest_rz_google_maps extends \Rukzuk\Modules\rz_google_maps
{

}

class rz_google_maps_RenderTest_ApiMock extends RenderApiMock
{
}

class rz_google_maps_RenderTest extends ModuleTestCase
{
  protected $moduleNS = '';
  protected $moduleClass = 'rz_google_maps_RenderTest_rz_google_maps';

  public function testRender_normal()
  {
    // prepare
    $unit = $this->createUnit(array(
      'formValues' => array(
        'address' => 'Bahnhofstrasse 1\n78462 Konstanz',
        'zoom' => '15',
        'maptype' => 'roadmap',
        'googleMapsApiKey' => '123key123'
      )
    ));

    // execute
    $html = $this->render(null, null, $unit);

    $expectedIframeUrl = 'https://www.google.com/maps/embed/v1/place?key=123key123'
                         . '&q=' . urlencode('Bahnhofstrasse 1\n78462 Konstanz')
                         . '&zoom=15'
                         . '&maptype=roadmap';

    // verify
    $matcher = array(
      'tag' => 'div',
      'child' => array(
        'tag' => 'iframe',
        'attributes' => array(
          'data-src' => $expectedIframeUrl
        )
      )
    );

    $this->assertTag($matcher, $html, 'google maps iframe tag not found in "'.$html. '"');
  }

}
