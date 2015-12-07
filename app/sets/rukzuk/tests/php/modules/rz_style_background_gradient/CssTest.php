<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH.'/rz_style_background_gradient/module/rz_style_background_gradient.php');

class rz_style_background_gradient_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_style_background_gradient';

  public function testBackgroundGradient()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_gradient_plain',
      'formValues' => array(
        'cssEnableBackgroundGradient' => true,
        'cssGradientType' => 'linear',
        'cssBackgroundGradientDirection' => 'left',
        'cssBackgroundGradientStartColor' => 'rgba(1,2,3, 1)',
        'cssBackgroundGradientEndColor' => 'rgba(4,5,6, 1)',

        // TODO remove these since they are default values of the module
        'cssBackgroundSizeWidth' => '100%',
        'cssBackgroundSizeHeight' => '100%',
        'cssEnableBackgroundPosition' => 'dynamic',
        'cssBackgroundPositionX' => 'top',
        'cssBackgroundPositionY' => 'left',
        'cssBackgroundAttachment' => false,
        'cssBackgroundRepeat' => 'no-repeat',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#background_gradient_plain', array(
      'background-image: linear-gradient(to bottom, rgba(1,2,3, 1) 0% , rgba(4,5,6, 1) 100%);',
      'background-image: -webkit-linear-gradient(top, rgba(1,2,3, 1) 0% , rgba(4,5,6, 1) 100%);',
      'background-image: -moz-linear-gradient(top, rgba(1,2,3, 1) 0% , rgba(4,5,6, 1) 100%);',
      'background-size: 100% 100%;',
      'background-position: top left;',
      'background-attachment: scroll;',
      'background-repeat: no-repeat;'
    ));
  }

  public function testBackgroundGradient_colorStops()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_gradient_plain',
      'formValues' => array(
        'cssEnableBackgroundGradient' => true,
        'cssGradientType' => 'linear',
        'cssBackgroundGradientDirection' => 'left',
        'cssBackgroundGradientStartColor' => 'rgba(1,2,3, 1)',
        'cssBackgroundGradientEndColor' => 'rgba(4,5,6, 1)',
        'cssColorStops' => true,
        'cssCustomColor1On' => true,
        'cssCustomColor1Color' => 'rgba(8,9,0, 1)',
        'cssCustomColor1Pos' => '20%',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#background_gradient_plain', array(
      'background-image: linear-gradient(to bottom, rgba(1,2,3, 1) 0% , rgba(8,9,0, 1) 20% , rgba(4,5,6, 1) 100%);'
    ));
  }

  public function testBackgroundGradient_rotation()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_gradient_plain',
      'formValues' => array(
        'cssEnableBackgroundGradient' => true,
        'cssGradientType' => 'linear',
        'cssLinearType' => 'custom',
        'cssRotationValue' => '43deg',
        'cssBackgroundGradientDirection' => 'left',
        'cssBackgroundGradientStartColor' => 'rgba(1,2,3, 1)',
        'cssBackgroundGradientEndColor' => 'rgba(4,5,6, 1)',
        'cssColorStops' => true,
        'cssCustomColor1On' => true,
        'cssCustomColor1Color' => 'rgba(8,9,0, 1)',
        'cssCustomColor1Pos' => '20%',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#background_gradient_plain', array(
      'background-image: linear-gradient(43deg, rgba(1,2,3, 1) 0% , rgba(8,9,0, 1) 20% , rgba(4,5,6, 1) 100%);',
      'background-image: -webkit-linear-gradient(47deg, rgba(1,2,3, 1) 0% , rgba(8,9,0, 1) 20% , rgba(4,5,6, 1) 100%);'
    ));
  }


  public function testBackgroundGradient_radial()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_gradient_radial',
      'formValues' => array(
        'cssEnableBackgroundGradient' => true,
        'cssGradientType' => 'radial',
        'cssRadialType' => 'ellipse',
        'cssBackgroundGradientDirection' => 'left', // ignored within radial
        'cssBackgroundGradientStartColor' => 'rgba(1,2,3, 1)',
        'cssBackgroundGradientEndColor' => 'rgba(4,5,6, 1)',
        'cssColorStops' => true,
        'cssCustomColor1On' => true,
        'cssCustomColor1Color' => 'rgba(8,9,0, 1)',
        'cssCustomColor1Pos' => '20%',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#background_gradient_radial', array(
      'background-image: radial-gradient(ellipse at center, rgba(1,2,3, 1) 0%, rgba(8,9,0, 1) 20% , rgba(4,5,6, 1) 100%);',
      'background-image: -webkit-radial-gradient(center ,ellipse, rgba(1,2,3, 1) 0%, rgba(8,9,0, 1) 20% , rgba(4,5,6, 1) 100%);'
    ));
  }

  public function testBackgroundGradient_disabledColorStops()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_gradient_plain',
      'formValues' => array(
        'cssEnableBackgroundGradient' => true,
        'cssGradientType' => 'linear',
        'cssBackgroundGradientDirection' => 'left',
        'cssBackgroundGradientStartColor' => 'rgba(1,2,3, 1)',
        'cssBackgroundGradientEndColor' => 'rgba(4,5,6, 1)',
        'cssColorStops' => false,
        'cssCustomColor1On' => true,
        'cssCustomColor1Color' => 'rgba(8,9,0, 1)',
        'cssCustomColor1Pos' => '20%',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#background_gradient_plain', array(
      'background-image: linear-gradient(to bottom, rgba(1,2,3, 1) 0% , rgba(4,5,6, 1) 100%);'
    ));
  }


  public function testBackgroundGradient_notEnabled()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'background_color_plain',
      'formValues' => array(
        'cssEnableBackgroundGradient' => false,
        'cssGradientType' => 'linear',
        'cssBackgroundGradientDirection' => 'diagonal_tl',
        'cssBackgroundGradientStartColor' => 'rgba(1,2,3, 1)',
        'cssBackgroundGradientEndColor' => 'rgba(4,5,6, 1)',
      )
    ));
    // verify
    $this->assertEmptyCssBody($css, 'There should be no css output if "cssEnableBackgroundColor" is FALSE');
  }

}
