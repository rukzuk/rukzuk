<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\MediaItemMock;
use \Test\Rukzuk\ModuleTestCase;

use \Rukzuk\Modules\rz_navigation;

require_once(MODULE_PATH.'/rz_navigation/module/rz_navigation.php');

class rz_navigaion_RenderTest_dummy extends \Rukzuk\Modules\rz_navigation
{
  public function getNavigationMarkup($renderApi, $unit, $navigation)
  {
    return parent::getNavigationMarkup($renderApi, $unit, $navigation);
  }
}


class rz_navigation_RenderTest_ApiMock extends RenderApiMock
{
}

class rz_navigation_RenderTest extends ModuleTestCase
{
  protected $moduleNS = '';
  protected $moduleClass = 'rz_navigaion_RenderTest_dummy';

  public function testRender_noNavItems()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit();
    $modulInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // execute
    $html = $module->getNavigationMarkup($api, $unit, $api->getNavigation());

    // verify
    $this->assertEmpty($html);
  }

  public function testRender_onePage()
  {
    // prepare
    $api = $this->createApiMock(array(
      'navigation' => array('page' => array('url' => 'www.example.org', 'inNavigation' => true))
    ));
    $unit = $this->createUnit(array(
      'formValues' => array(
        'enableLevel1' => true
      )
    ));
    $modulInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // execute
    $navMarkup = $module->getNavigationMarkup($api, $unit, $api->getNavigation());

    if (!is_null($navMarkup)) {
      $html =  $navMarkup->toString();
    } else {
      $html = '';
    }

    // verify
    $this->assertEquals('<ul class="navLevel1"><li class="navItem"><a class="navLink" href="www.example.org">page</a></li></ul>', $html);
  }

  public function testRender_multipleLevel()
  {
    // prepare
    $api = $this->createApiMock(array(
      'navigation' => array(
        'page1' => array(
          'url' => 'www.fage.org/page1.html',
          'inNavigation' => true,
          'children' => array(
            'page2' => array(
              'url' => 'www.fage.org/page2.html',
              'inNavigation' => true,
              'children' => array(
                'page3' => array(
                  'url' => 'www.fage.org/page3.html',
                  'inNavigation' => true,
                  'children' => array(
                  )
                )
              )
            )
          )
        )
      )
    ));
    $unit = $this->createUnit(array(
      'formValues' => array(
        'enableLevel1' => true,
        'enableLevel2' => true,
        'enableLevel3' => true
      )
    ));
    $modulInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // execute
    $navMarkup = $module->getNavigationMarkup($api, $unit, $api->getNavigation());
    if (!is_null($navMarkup)) {
      $html =  $navMarkup->toString();
    } else {
      $html = '';
    }

    // verify
    $this->assertEquals(implode('', array(
      '<ul class="navLevel1">',
        '<li class="navItem hasChildPages">',
          '<a class="navLink hasChildPages" href="www.fage.org/page1.html">page1</a>',
          '<ul class="navLevel2">',
            '<li class="navItem hasChildPages">',
              '<a class="navLink hasChildPages" href="www.fage.org/page2.html">page2</a>',
              '<ul class="navLevel3">',
                '<li class="navItem">',
                  '<a class="navLink" href="www.fage.org/page3.html">page3</a>',
                '</li>',
              '</ul>',
            '</li>',
          '</ul>',
        '</li>',
      '</ul>')), $html);
  }

  public function testRender_multipleLevel_skipOne()
  {
    // prepare
    $api = $this->createApiMock(array(
      'navigation' => array(
        'page1' => array(
          'url' => 'www.fage.org/page1.html',
          'inNavigation' => true,
          'children' => array(
            'page2' => array(
              'url' => 'www.fage.org/page2.html',
              'inNavigation' => true,
              'children' => array(
                'page3' => array(
                  'url' => 'www.fage.org/page3.html',
                  'inNavigation' => true,
                  'children' => array(
                  )
                )
              )
            )
          )
        )
      )
    ));
    $unit = $this->createUnit(array(
      'formValues' => array(
        'enableLevel1' => true,
        'enableLevel2' => false,
        'enableLevel3' => true
      )
    ));
    $modulInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // execute
    $navMarkup = $module->getNavigationMarkup($api, $unit, $api->getNavigation());
    if (!is_null($navMarkup)) {
      $html =  $navMarkup->toString();
    } else {
      $html = '';
    }


    // verify
    $this->assertEquals(implode('', array(
      '<ul class="navLevel1">',
        '<li class="navItem hasChildPages">',
          '<a class="navLink hasChildPages" href="www.fage.org/page1.html">page1</a>',
          '<ul class="navLevel2">',
            '<ul class="navLevel3">',
              '<li class="navItem">',
                '<a class="navLink" href="www.fage.org/page3.html">page3</a>',
              '</li>',
            '</ul>',
          '</ul>',
        '</li>',
      '</ul>')), $html);
  }

  //
  //
  // helper
  //
  //

  private function createApiMock($conf = null)
  {
    return new rz_navigation_RenderTest_ApiMock($conf);
  }

}
