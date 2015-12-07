<?php

use \Test\Rukzuk\CssTestCase;
use \Rukzuk\Modules\rz_box;


require_once(MODULE_PATH.'/rz_box/module/rz_box.php');

class rz_box_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_box';

  public function testSpacerWidth()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'box',
      'formValues' => array(
        'cssHSpace' => '10px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#box > .isColumnBoxTable > .boxSpacer:nth-child(1n)', array(
      'width: 10px;'
    ));
  }

  public function testContentAlignment()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'box',
      'formValues' => array(
        'cssVerticalAlign' => 'middle'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#box > .isColumnBoxTable > .isColumnBoxCell', array(
      'vertical-align: middle;'
    ));
 }

  public function testContentAlignmentDefaults()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'box'
    ));
    // verify
    $this->assertContainsCssRule($css, '#box > .isColumnBoxTable > .isColumnBoxCell', array(
      'vertical-align: top;'
    ));
  }

  public function testModuleAlignment()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'box',
      'formValues' => array(
        'cssEnableHorizontalAlign' => true,
        'cssHorizontalAlign' => 'center'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#box', array(
      'margin-left: auto !important;',
      'margin-right: auto !important;'
    ));
  }
}
