<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH . '/rz_style_transformation/module/rz_style_transformation.php');

class rz_style_transformation_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_style_transformation';

  public function testTransformation_rotate()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'transformation_rotate',
      'formValues' => array(
        'cssEnableTransformRotate' => true,
        'cssTransformRotate' => '20deg',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#transformation_rotate', array(
      'transform: rotate(20deg);',
      '-moz-transform: rotate(20deg);',
      '-webkit-transform: rotate(20deg);'
    ));
  }

  public function testTransformation_scale()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'transformation_scale',
      'formValues' => array(
        'cssEnableTransformScale' => true,
        'cssTransformScale' => '25%',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#transformation_scale', array(
      'transform: scale(0.25);',
      '-moz-transform: scale(0.25);',
      '-webkit-transform: scale(0.25);'
    ));
  }

  public function testTransformation_scaleAndRotate()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'transformation_scaleAndRotate',
      'formValues' => array(
        'cssEnableTransformScale' => true,
        'cssTransformScale' => '25%',
        'cssEnableTransformRotate' => true,
        'cssTransformRotate' => '20deg',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#transformation_scaleAndRotate', array(
      'transform: rotate(20deg) scale(0.25);',
      '-moz-transform: rotate(20deg) scale(0.25);',
      '-webkit-transform: rotate(20deg) scale(0.25);'
    ));
  }

  public function testTransformation_treeDimensional()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'transformation_3d',
      'formValues' => array(
        'cssEnable3D' => true,
        'cssPerspective' => '55px',
        'cssTranslateX' => '14px',
        'cssTranslateY' => '17px',
        'cssTranslateZ' => '22px',
        'cssRotateX' => '30deg',
        'cssRotateY' => '21deg',
        'cssRotateZ' => '51deg'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#transformation_3d', array(
      'transform: perspective(55px) translateX(14px) translateY(17px) translateZ(22px) rotateX(30deg) rotateY(21deg) rotateZ(51deg);',
      '-moz-transform: perspective(55px) translateX(14px) translateY(17px) translateZ(22px) rotateX(30deg) rotateY(21deg) rotateZ(51deg);',
      '-webkit-transform: perspective(55px) translateX(14px) translateY(17px) translateZ(22px) rotateX(30deg) rotateY(21deg) rotateZ(51deg);'
    ));
  }

}
