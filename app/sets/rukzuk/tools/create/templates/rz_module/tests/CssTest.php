<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH . '/<%= moduleId %>/module/<%= moduleId %>.php');

class <%= moduleId %>_CssTest extends CssTestCase
{
  protected $moduleClass = '<%= moduleId %>';

  public function testCreateCss()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => '<%= moduleId %>',
      'formValues' => array(
        // set form values here ...
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#<%= moduleId %>', array(
      // add expected css rules
    ));
  }
}
