<?php

use \Test\Rukzuk\CssTestCase;
use \Rukzuk\Modules\rz_navigatiorz_navigation;


require_once(MODULE_PATH.'/rz_navigation/module/rz_navigation.php');

class rz_navigation_CssTest extends CssTestCase
{
  protected $moduleClass = 'rz_navigation';

  public function testCreateCss_noLevelEnabled()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'nav',
      'formValues' => array()
    ));
    // verify
    $this->assertEmptyCssBody($css);
  }

  public function testCreateCss_oneLevel_horizontalSpread()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'nav',
      'formValues' => array(
        'enableLevel1' => true,
        'cssLevel1Distribution' => 'horizontal',
        'cssLevel1Space' => '42px',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#nav .navLevel1 > li', array(
      'display: inline-block;',
      'padding-left: 42px;',
      'padding-top: 0;'
    ));
  }

  public function testCreateCss_oneLevel_horizontalFullSpread()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'nav',
      'formValues' => array(
        'enableLevel1' => true,
        'cssLevel1Distribution' => 'horizontal_full',
        'cssLevel1Space' => '42px',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#nav .navLevel1 > li', array(
      'display: table-cell;',
      'padding-left: 42px;',
      'padding-top: 0;'
    ));
    $this->assertContainsCssRule($css, '#nav > ul', array(
      'display: table;',
      'width: 100%;'
    ));
  }

  public function testCreateCss_oneLevel_verticalSpread()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'nav',
      'formValues' => array(
        'enableLevel1' => true,
        'cssLevel1Distribution' => 'vertical',
        'cssLevel1Space' => '42px',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#nav .navLevel1 > li', array(
      'display: block;',
      'padding-left: 0;',
      'padding-top: 42px;'
    ));
  }

  public function testCreateCss_oneLevel_textAlign()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'nav',
      'formValues' => array(
        'enableLevel1' => true,
        'cssLevel1Align' => 'center',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#nav .navLevel1', array(
      'text-align: center;'
    ));
  }

  public function testCreateCss_ultimateAllLevel()
  {
    // prepare
    // execute
    $css = $this->createCss(array(
      'id' => 'nav',
      'formValues' => array(
        'enableLevel1' => true,
        'cssLevel1Distribution' => 'horizontal_full',
        'cssLevel1Space' => '1px',
        'cssLevel1Align' => 'left',

        'enableLevel2' => true,
        'cssLevel2Distribution' => 'horizontal',
        'cssLevel2Space' => '2px',
        'cssLevel2Align' => 'center',

        'enableLevel3' => true,
        'cssLevel3Distribution' => 'vertical',
        'cssLevel3Space' => '3px',
        'cssLevel3Align' => 'right',

        'enableLevel4' => true,
        'cssLevel4Distribution' => 'horizontal',
        'cssLevel4Space' => '4px',
        'cssLevel4Align' => 'center',

        'enableLevel5' => true,
        'cssLevel5Distribution' => 'vertical',
        'cssLevel5Space' => '5px',
        'cssLevel5Align' => 'left',
      )
    ));
    // verify
    $this->assertContainsCssRule($css, '#nav .navLevel1 > li', array(
      'display: table-cell;',
      'padding-left: 1px;',
      'padding-top: 0;'
    ));
    $this->assertContainsCssRule($css, '#nav .navLevel1', array(
      'text-align: left;'
    ));
    $this->assertContainsCssRule($css, '#nav > ul', array(
      'display: table;',
      'width: 100%;'
    ));


    $this->assertContainsCssRule($css, '#nav .navLevel2 > li', array(
      'display: inline-block;',
      'padding-left: 2px;',
      'padding-top: 0;'
    ));
    $this->assertContainsCssRule($css, '#nav .navLevel2', array(
      'text-align: center;'
    ));

    $this->assertContainsCssRule($css, '#nav .navLevel3 > li', array(
      'display: block;',
      'padding-left: 0;',
      'padding-top: 3px;'
    ));
    $this->assertContainsCssRule($css, '#nav .navLevel3', array(
      'text-align: right;'
    ));

    $this->assertContainsCssRule($css, '#nav .navLevel4 > li', array(
      'display: inline-block;',
      'padding-left: 4px;',
      'padding-top: 0;'
    ));
    $this->assertContainsCssRule($css, '#nav .navLevel4', array(
      'text-align: center;'
    ));

    $this->assertContainsCssRule($css, '#nav .navLevel5 > li', array(
      'display: block;',
      'padding-left: 0;',
      'padding-top: 5px;'
    ));
    $this->assertContainsCssRule($css, '#nav .navLevel5', array(
      'text-align: left;'
    ));

  }
}
