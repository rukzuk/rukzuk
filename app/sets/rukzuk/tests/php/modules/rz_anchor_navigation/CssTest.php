<?php

use \Test\Rukzuk\CssTestCase;


require_once(MODULE_PATH . '/rz_anchor_navigation/module/rz_anchor_navigation.php');

class rz_anchor_navigation_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_anchor_navigation';

  public function testCreateCss_align()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_anchor_navigation',
      'formValues' => array(
        'cssAlign' => 'center'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#rz_anchor_navigation > ul', array(
      'text-align: center;'
    ));
  }

  public function testCreateCss_distributionHorizontal()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_anchor_navigation',
      'formValues' => array(
        'cssDistribution' => 'horizontal',
        'cssSpace' => '42px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#rz_anchor_navigation > ul > li', array(
      'display: inline-block;',
      'padding-left: 42px;',
      'padding-top: 0;'
    ));
  }

  public function testCreateCss_distributionHorizontalFull()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_anchor_navigation',
      'formValues' => array(
        'cssDistribution' => 'horizontal_full',
        'cssSpace' => '42px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#rz_anchor_navigation > ul', array(
      'width: 100%;',
      'display: table;'
    ));
    $this->assertContainsCssRule($css, '#rz_anchor_navigation > ul > li', array(
      'display: table-cell;',
      'padding-left: 42px;',
      'padding-top: 0;'
    ));
  }

  public function testCreateCss_distributionElse()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'rz_anchor_navigation',
      'formValues' => array(
        'cssSpace' => '42px'
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#rz_anchor_navigation > ul > li', array(
      'display: block;',
      'padding-left: 0;',
      'padding-top: 42px;'
    ));
  }
}
