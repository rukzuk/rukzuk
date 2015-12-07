<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH . '/rz_style_box_shadow/module/rz_style_box_shadow.php');

class rz_style_box_shadow_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_style_box_shadow';

  public function testCreateCss_success()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_style_box_shadow',
      'formValues' => array(
        // set form values here ...
        'cssEnableBoxShadow' => true,
        'cssBoxShadowColor' => 'rgba(1, 2, 3, 0.4)',
        'cssBoxShadowOffsetX' => 1,
        'cssBoxShadowOffsetY' => 2,
        'cssBoxShadowBlur' => 3,
        'cssBoxShadowSpread' => 4
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#rz_style_box_shadow', array(
      'box-shadow: 1 2 3 4 rgba(1, 2, 3, 0.4);'
    ));
  }

  public function testCreateCss_noOutput()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_style_box_shadow',
      'formValues' => array(
        // set form values here ...
        'cssEnableBoxShadow' => false,
        'cssBoxShadowColor' => 'rgba(1, 2, 3, 0.4)',
        'cssBoxShadowOffsetX' => 1,
        'cssBoxShadowOffsetY' => 2,
        'cssBoxShadowBlur' => 3,
        'cssBoxShadowSpread' => 4
      )
    ));
    // verify
    $this->assertEmptyCssBody($css, '#rz_style_box_shadow');
  }


  public function testCreateCss_inset()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_style_box_shadow',
      'formValues' => array(
        // set form values here ...
        'cssEnableBoxShadow' => true,
        'cssBoxShadowColor' => 'rgba(1, 2, 3, 0.4)',
        'cssBoxShadowOffsetX' => 1,
        'cssBoxShadowOffsetY' => 2,
        'cssBoxShadowBlur' => 3,
        'cssBoxShadowSpread' => 4,
        'cssBoxShadowInset' => true
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#rz_style_box_shadow', array(
      'box-shadow: 1 2 3 4 rgba(1, 2, 3, 0.4) inset;'
    ));
  }


  public function testCreateCss_noShadow()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_style_box_shadow',
      'formValues' => array(
        // set form values here ...
        'cssEnableBoxShadow' => true,
        'cssBoxShadowColor' => null,
        'cssBoxShadowOffsetX' => 1,
        'cssBoxShadowOffsetY' => 2,
        'cssBoxShadowBlur' => 3,
        'cssBoxShadowSpread' => 4
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#rz_style_box_shadow', array(
      'box-shadow: none;'
    ));
  }
  public function testCreateCss_multipleShadows()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'units' => array(
        'foo' => array(
          'children' => array(
            'shadow-1' => array(
              'moduleId' => 'rz_style_box_shadow',
              'formValues' => array(
                'cssEnableBoxShadow' => true,
                'cssBoxShadowColor' => 'rgba(1, 2, 3, 0.4)',
                'cssBoxShadowOffsetX' => 1,
                'cssBoxShadowOffsetY' => 2,
                'cssBoxShadowBlur' => 3,
                'cssBoxShadowSpread' => 4
              )
            ),
            'shadow-2' => array(
              'moduleId' => 'rz_style_box_shadow',
              'formValues' => array(
                'cssEnableBoxShadow' => true,
                'cssBoxShadowColor' => 'rgba(5, 6, 7, 0.8)',
                'cssBoxShadowOffsetX' => 5,
                'cssBoxShadowOffsetY' => 6,
                'cssBoxShadowBlur' => 7,
                'cssBoxShadowSpread' => 8,
                'cssBoxShadowInset' => true
              )
            )
          )
        )
      ),
      'modules' => array(
        'foo' => array(
          'ns' => '\Rukzuk\Modules',
          'class' => 'SimpleModule'
        ),
        'rz_style_box_shadow' => array(
          'ns' => '\Rukzuk\Modules',
          'class' => 'rz_style_box_shadow',
          'manifest' => array(
            'moduleType' => 'extension'
          )
        )
      )
    ));
    // execute
    $css = $this->createCssWithApi($api);
    // verify
    $this->assertContainsCssRule($css, '#foo', array(
      'box-shadow: 1 2 3 4 rgba(1, 2, 3, 0.4), 5 6 7 8 rgba(5, 6, 7, 0.8) inset;'
    ));
  }
}
