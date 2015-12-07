<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH.'/rz_style_position/module/rz_style_position.php');

class rz_style_position_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_style_position';

  // public function testPadding()
  // {
  //   // prepare
  //   // execute
  //   $css = $this->createCss(array(
  //     'id' => 'padding_margin',
  //     'formValues' => array(
  //       'cssEnablePadding' => true,
  //       'cssPaddingTop' => '1px',
  //       'cssPaddingRight' => '2px',
  //       'cssPaddingBottom' => '3px',
  //       'cssPaddingLeft' => '4px'
  //     )
  //   ));
  //   // verify
  //   $this->assertContainsCssRule($css, '#padding_margin_plain', array(
  //     'padding: 1px 2px 3px 4px;'
  //   ));
  // }

  // public function testMargin()
  // {
  //   // prepare
  //   // execute
  //   $css = $this->createCss(array(
  //     'id' => 'padding_margin_plain',
  //     'formValues' => array(
  //       'cssEnableMargin' => true,
  //       'cssMarginTop' => '1px',
  //       'cssMarginRight' => '2px',
  //       'cssMarginBottom' => '3px',
  //       'cssMarginLeft' => '4px'
  //     )
  //   ));
  //   // verify
  //   $this->assertContainsCssRule($css, '#padding_margin_plain', array(
  //     'margin: 1px 2px 3px 4px;'
  //   ));
  // }

  public function testCreateCss_positionRelative()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssPositionType' => 'relative',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'position: relative;',
      'top: 20px;',
      'bottom: auto;',
      'left: 10px;',
      'right: auto;'
    ));
  }

  public function testCreateCss_positionAbsolute_TopLeft()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssAbsoluteOrigin' => 'Top Left',
        'cssPositionType' => 'absolute',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'position: absolute;',
      'top: 20px;',
      'bottom: auto;',
      'left: 10px;',
      'right: auto;'
    ));
  }

  public function testCreateCss_positionAbsolute_TopRight()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssAbsoluteOrigin' => 'Top Right',
        'cssPositionType' => 'absolute',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'position: absolute;',
      'top: 20px;',
      'bottom: auto;',
      'left: auto;',
      'right: 10px;'
    ));
  }

  public function testCreateCss_positionAbsolute_BottomLeft()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssAbsoluteOrigin' => 'Bottom Left',
        'cssPositionType' => 'absolute',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'position: absolute;',
      'top: auto;',
      'bottom: 20px;',
      'left: 10px;',
      'right: auto;'
    ));
  }
  public function testCreateCss_positionAbsolute_BottomRight()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssAbsoluteOrigin' => 'Bottom Right',
        'cssPositionType' => 'absolute',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'position: absolute;',
      'top: auto;',
      'bottom: 20px;',
      'left: auto;',
      'right: 10px;'
    ));
  }

  public function testCreateCss_positionFixed_TopLeft()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssFixedOrigin' => 'Top Left',
        'cssPositionType' => 'fixed',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'position: fixed;',
      'top: 20px;',
      'bottom: auto;',
      'left: 10px;',
      'right: auto;'
    ));
  }



  public function testCreateCss_positionFixed_TopRight()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssFixedOrigin' => 'Top Right',
        'cssPositionType' => 'fixed',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'position: fixed;',
      'top: 20px;',
      'bottom: auto;',
      'left: auto;',
      'right: 10px;'
    ));
  }

  public function testCreateCss_positionFixed_BottomLeft()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssFixedOrigin' => 'Bottom Left',
        'cssPositionType' => 'fixed',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'position: fixed;',
      'top: auto;',
      'bottom: 20px;',
      'left: 10px;',
      'right: auto;'
    ));
  }
  public function testCreateCss_positionFixed_BottomRight()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssFixedOrigin' => 'Bottom Right',
        'cssPositionType' => 'fixed',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'position: fixed;',
      'top: auto;',
      'bottom: 20px;',
      'left: auto;',
      'right: 10px;'
    ));
  }

  public function testCreateCss_ZIndex()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'pos',
      'formValues' => array(
        'cssEnablePosition' => true,
        'cssZindex' => '42'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#pos', array(
      'z-index: 42;'
    ));
  }
  public function testCreateCss_noOutput()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'papos',
      'formValues' => array(
        'cssEnablePosition' => false,
        'cssPositionType' => 'relative',
        'cssShiftX' => '10px',
        'cssShiftY' => '20px'
      )
    ));
    // verify
    $this->assertEmptyCssBody($css, 'There should be no css output if "cssEnablePadding" is FALSE');
  }

}
