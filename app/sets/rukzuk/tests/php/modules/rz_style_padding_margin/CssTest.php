<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH.'/rz_style_padding_margin/module/rz_style_padding_margin.php');

class rz_style_padding_margin_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_style_padding_margin';

  public function testPadding()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'padding_margin_plain',
      'formValues' => array(
        'cssEnablePadding' => true,
        'cssPaddingTop' => '1px',
        'cssPaddingRight' => '2px',
        'cssPaddingBottom' => '3px',
        'cssPaddingLeft' => '4px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#padding_margin_plain', array(
      'padding: 1px 2px 3px 4px;'
    ));
  }

  public function testMargin()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'padding_margin_plain',
      'formValues' => array(
        'cssEnableMargin' => true,
        'cssMarginTop' => '1px',
        'cssMarginRight' => '2px',
        'cssMarginBottom' => '3px',
        'cssMarginLeft' => '4px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#padding_margin_plain', array(
      'margin: 1px 2px 3px 4px;'
    ));
  }

  public function testNoPaddingAndMargin()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'padding_margin_plain',
      'formValues' => array(
        'cssEnablePadding' => false,
        'cssPaddingTop' => '1px',
        'cssPaddingRight' => '2px',
        'cssPaddingBottom' => '3px',
        'cssPaddingLeft' => '4px',
        'cssEnableMargin' => false,
        'cssMarginTop' => '1px',
        'cssMarginRight' => '2px',
        'cssMarginBottom' => '3px',
        'cssMarginLeft' => '4px'
      )
    ));
    // verify
    $this->assertEmptyCssBody($css, 'There should be no css output if "cssEnablePadding" and "cssEnableMargin" is FALSE');
  }

}
