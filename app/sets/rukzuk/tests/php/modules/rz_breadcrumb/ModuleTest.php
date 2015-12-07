<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;

require_once(MODULE_PATH.'/rz_breadcrumb/module/rz_breadcrumb.php');

class rz_breadcrumb_ModuleTest_TestModule extends \Rukzuk\Modules\rz_breadcrumb
{
  // make methods to be tested public
  public function getBreadcrumbItemTag($page)
  {
    return parent::getBreadcrumbItemTag($page);
  }

  public function getBreadcrumbSpacerTag($spacer)
  {
    return parent::getBreadcrumbSpacerTag($spacer);
  }
}

class rz_breadcrumb_ModuleTest_ApiMock extends RenderApiMock
{
  // override api mock methods here...
}

class rz_breadcrumb_ModuleTest extends ModuleTestCase
{

  protected $moduleNS = '';
  protected $moduleClass = 'rz_breadcrumb_ModuleTest_TestModule';

  public function testGetBreadcrumpItemTag()
  {
    // prepare
    $module = $this->createModule();
    // execute
    $tag = $module->getBreadcrumbItemTag(array(
      'href' => 'http://www.example.org',
      'title' => '<b>foo</b>'
    ))->toString();
    // verify
    $this->assertEquals('<a href="http://www.example.org" class="breadcrumbNavLink">&lt;b&gt;foo&lt;/b&gt;</a>', $tag);
  }

  public function testGetBreadcrumpSpacerTag()
  {
    // prepare
    $module = $this->createModule();
    // execute
    $tag = $module->getBreadcrumbSpacerTag('\o/')->toString();
    // verify
    $this->assertEquals('<span class="breadcrumbNavSpacer">\o/</span>', $tag);
  }

  public function testRender_inPage()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'currentPageId' => 'page3',
      'navigation' => $this->getNavigationData()
    ));
    // execute
    $html = $this->render(null, $api);
    // verify
    $this->assertContains('div', $html);
    $this->assertContains('Page 1', $html);
    $this->assertContains('href="www.fage.org/page1.html"', $html);
    $this->assertContains('Page 2', $html);
    $this->assertContains('href="www.fage.org/page2.html"', $html);
    $this->assertContains('Page 3', $html);
    $this->assertContains('href="www.fage.org/page3.html"', $html);
  }

  public function testRender_inPageEditMode()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'mode' => 'edit',
      'currentPageId' => 'page3',
      'navigation' => $this->getNavigationData()
    ));
    // execute
    $html = $this->render(null, $api, array('id' => 'testUnitId'));
    // verify
    $this->assertTag(array(
      'tag' => 'div',
      'id' => 'testUnitId',
      'child' => array(
        'tag' => 'a',
        'attributes' => array('href' => 'javascript:void(0)'),
      ),
      'children' => array('count' => 5)
    ), $html, 'Malformed html output: ' . $html);
    $this->assertNotTag(array(
      'child' => array(
        'attributes' => array('href' => 'www.fage.org/page1.html'),
      ),
    ), $html, 'Malformed html output: ' . $html);
  }

  public function testRender_inPageWithNavStart()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'currentPageId' => 'page3',
      'navigation' => $this->getNavigationData()
    ));
    $unit = $this->createUnit(array(
      'id' => 'testUnitId',
      'formValues' => array('navStart' => 'page1')
    ));
    // execute
    $html = $this->render(null, $api, $unit);
    // verify
    $this->assertTag(array(
      'child' => array(
        'tag' => 'a',
        'attributes' => array('href' => 'www.fage.org/page2.html'),
      ),
      'children' => array('count' => 3)
    ), $html, 'Malformed html output: ' . $html);
    $this->assertNotTag(array(
      'child' => array(
        'attributes' => array('href' => 'www.fage.org/page1.html'),
      ),
    ), $html, 'Malformed html output: ' . $html);
  }

  public function testRender_withSpace()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'currentPageId' => 'page3',
      'navigation' => $this->getNavigationData()
    ));
    $unit = $this->createUnit(array(
      'id' => 'testUnitId',
      'formValues' => array('spacer' => '\o/')
    ));
    // execute
    $html = $this->render(null, $api, $unit);
    // verify
    $this->assertTag(array(
      'child' => array(
        'tag' => 'span',
        'content' => '\o/'
      )
    ), $html, 'Malformed html output: ' . $html);
  }

  public function testRender_dummyDataInEditMode()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'isTemplate' => true,
      'mode' => 'edit'
    ));
    $unit = $this->createUnit(array(
      'id' => 'testUnitId'
    ));
    $moduleInfo = $this->createModuleInfo(array(
      'custom' => array(
        'i18n' => array(
          'testdata.page' => array('en' => 'TEST PAGE')
        )
      )
    ));
    // execute
    $html = $this->render(null, $api, $unit, $moduleInfo);
    // verify
    $this->assertTag(array(
      'child' => array(
        'tag' => 'a',
        'content' => 'TEST PAGE 3'
      )
    ), $html, 'Malformed html output: ' . $html);
  }

  private function getNavigationData()
  {
    return array(
      'page1' => array(
        'name' => 'Page 1',
        'url' => 'www.fage.org/page1.html',
        'children' => array(
          'page2' => array(
            'name' => 'Page 2',
            'url' => 'www.fage.org/page2.html',
            'children' => array(
              'page3' => array(
                'name' => 'Page 3',
                'url' => 'www.fage.org/page3.html',
                'children' => array(
                )
              )
            )
          )
        )
      )
    );
  }
}
