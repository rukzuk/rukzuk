<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH.'/rz_style_custom_css/module/rz_style_custom_css.php');

class rz_style_custom_css_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_style_custom_css';

  public function testCustomCssWithSelector()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'custom_css',
      'formValues' => array(
        'cssEnableCustomCss' => true,
        'cssCustomCss' => 'span { color: red; }'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#custom_css span', array(
      'color: red;'
    ));
  }

  public function testCustomCssNoSelector()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'custom_css_plain',
      'formValues' => array(
        'cssEnableCustomCss' => true,
        'cssCustomCss' => 'background-color: green;'."\n".'color: blue;'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#custom_css_plain', array(
      'background-color: green;',
      'color: blue;'
    ));
 }

  public function testCustomCssNotEnabled()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'custom_css_plain',
      'formValues' => array(
        'cssEnableCustomCss' => false,
        'cssCustomCss' => 'background-color: green;'."\n".'color: blue;'
      )
    ));
    // verify
    $this->assertEmptyCssBody($css, 'cssEnableCustomCss: false, should not generate css code');
  }

}
