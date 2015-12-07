<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH . '/rz_google_maps/module/rz_google_maps.php');

class rz_google_maps_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_google_maps';

  public function testCreateCss()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_google_maps',
      'formValues' => array(
        'cssHeight' => '75%'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#rz_google_maps > div', array(
      'padding-bottom: 75%;'
    ));
  }
}
