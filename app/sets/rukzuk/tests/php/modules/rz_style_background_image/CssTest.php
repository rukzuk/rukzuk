<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH.'/rz_style_background_image/module/rz_style_background_image.php');

// TODO add missing Test\Rukzuk\RenderApiMock::getMediaItem()

/*
class rz_style_background_image_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_style_background_image';

  public function testMissingBackgroundImage()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_image',
      'formValues' => array(
        'cssEnableBackgroundImage' => true,
        'cssBackgroundImage' => 'MDB-fake-id-MDB'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#background_image', array(
      'background-image: none;'
    ));
  }

  public function testDisabledBackgroundImage()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_image',
      'formValues' => array(
        'cssEnableBackgroundColor' => false,
        'cssBackgroundImage' => 'MDB-fake-id-MDB',
      )
    ));
    // verify
    $this->assertEmptyCssBody($css, 'There should be no css output if "cssEnableBackgroundImage" is FALSE');
  }


}
*/