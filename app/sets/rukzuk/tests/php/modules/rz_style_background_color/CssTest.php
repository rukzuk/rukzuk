<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH.'/rz_style_background_color/module/rz_style_background_color.php');

class rz_style_background_color_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_style_background_color';

  public function testBackgroundColor()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_color_plain',
      'formValues' => array(
        'cssEnableBackgroundColor' => true,
        'cssBackgroundColor' => 'rgba(110, 40, 107, .75)',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#background_color_plain', array(
      'background-color: rgba(110, 40, 107, .75);'
    ));
  }

  public function testTransparentBackground()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_color_plain',
      'formValues' => array(
        'cssEnableBackgroundColor' => true
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#background_color_plain', array(
      'background-color: transparent;'
    ));
  }

  public function testNoBackgroundColor()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_color_plain',
      'formValues' => array(
        'cssEnableBackgroundColor' => false,
        'cssBackgroundColor' => 'rgba(110, 40, 107, .75)',
      )
    ));
    // verify
    $this->assertEmptyCssBody($css, 'There should be no css output if "cssEnableBackgroundColor" is FALSE');
  }

}
