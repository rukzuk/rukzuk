<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;

require_once(MODULE_PATH.'/rz_anchor_navigation/module/rz_anchor_navigation.php');

class rz_anchor_navigation_ModuleTest_TestModule extends \Rukzuk\Modules\rz_anchor_navigation
{
  // override module class methods here...
}

class rz_anchor_navigation_ModuleTest_ApiMock extends RenderApiMock
{
  // override api mock methods here...
}

class rz_anchor_navigation_ModuleTest extends ModuleTestCase
{

  protected $moduleNS = '';
  protected $moduleClass = 'rz_anchor_navigation_ModuleTest_TestModule';

  public function testRender()
  {
    // prepare
    $unit = $this->createUnit(array(
      'id' => 'test-anchor-navigation',
      'formValues' => array(
        'scrollSpeed' => '42ms',
        'scrollEasing' => 'foo'
      )
    ));
    // execute
    $html = $this->render(null, null, $unit);
    // verify
    $this->assertTag(array(
      'tag' => 'div',
      'attributes' => array(
        'id' => 'test-anchor-navigation',
        'data-scroll-speed' => '42ms',
        'data-scroll-easing' => 'foo'
      )
    ), $html);
  }

  public function testRender_fromAllUnitData()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'allUnitData' => array(
        array('anchor' => array(
          'name'  => 'Test Anchor 1',
          'id'    => 'test-anchor-1',
        )),
        array('anchor' => array(
          'name'  => 'Test Anchor 2',
          'id'    => 'test-anchor-2',
        )),
        array('anchor' => array(
          'name'  => 'Test Anchor 3',
          'id'    => 'test-anchor-3'
        )),
      )
    ));
    $unit = $this->createUnit(array(
      'id' => 'test-anchor-navigation'
    ));
    // execute
    $html = $this->render(null, $api, $unit);
    // verify
    $this->assertTag(array(
      'tag' => 'div',
      'attributes' => array('id' => 'test-anchor-navigation'),
      'child' => array(
        'tag' => 'ul',
        'attributes' => array('class' => 'anchorList'),
        'children' => array('count' => 3),
        'child' => array(
          'tag' => 'li',
          'attributes' => array('class' => 'anchorItem'),
          'child' => array(
            'tag' => 'a',
            'attributes' => array(
              'class' => 'anchorLink',
              'href' => '#test-anchor-3',
            ),
            'content' => 'Test Anchor 3'
          )
        )
      )
    ), $html, 'Malformed HTML: ' . $html);
  }
}
