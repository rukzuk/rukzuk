<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH.'/rz_style_border/module/rz_style_border.php');

class rz_style_border_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_style_border';

  public function testBorder()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'border_plain',
      'formValues' => array(
        'cssEnableBorder' => true,
        'cssBorderColor' => 'rgba(110, 40, 107, .75)',
        'cssBorderWidth' => '5px',
        'cssBorderStyle' => 'solid',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#border_plain', array(
      'border-color: rgba(110, 40, 107, .75);',
      'border-width: 5px;',
      'border-style: solid;'
    ));
  }

  public function testBorderDirection()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'border_plain',
      'formValues' => array(
        'cssEnableBorder' => true,
        'cssBorderColor' => 'rgba(110, 40, 107, .75)',
        'cssBorderWidth' => '5px',
        'cssBorderStyle' => 'solid',
        'cssEnableBorderLeft' => true,
        'cssBorderLeftColor' => 'rgba(0, 0, 0, 1)',
        'cssBorderLeftWidth' => '10px',
        'cssBorderLeftStyle' => 'solid',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#border_plain', array(
      'border-color: rgba(110, 40, 107, .75);',
      'border-width: 5px;',
      'border-style: solid;',
      'border-left-color: rgba(0, 0, 0, 1);',
      'border-left-width: 10px;',
      'border-left-style: solid;'
    ));
  }

  public function testNoBorder()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'border_plain',
      'formValues' => array(
        'cssEnableBorder' => false,
        'cssBorderColor' => 'rgba(110, 40, 107, .75)',
        'cssBorderWidth' => '5px',
        'cssBorderStyle' => 'solid',
      )
    ));
    // verify
    $this->assertEmptyCssBody($css, 'There should be no css output if "cssEnableBorder" is FALSE');
  }

}
