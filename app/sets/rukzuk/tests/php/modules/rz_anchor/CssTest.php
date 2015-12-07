<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH . '/rz_anchor/module/rz_anchor.php');

class rz_anchor_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_anchor';

  public function testCreateCss()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_anchor',
      'formValues' => array(
        'cssVisualHelperValign' => 'center'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#rz_anchor > .anchor', array(
      'text-align: center;'
    ));
  }
}
