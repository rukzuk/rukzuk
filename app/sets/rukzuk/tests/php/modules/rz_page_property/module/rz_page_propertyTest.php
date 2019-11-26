<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;

require_once(MODULE_PATH . '/rz_page_property/module/rz_page_property.php');

class rz_page_propertyTest_ApiMock extends RenderApiMock
{
  public function getMediaItem($id)
  {
    if (is_string($id)) {
      return new \Test\Rukzuk\MediaItemMock(array(
        'id' => 'MDB-KSHDJUIAKD-MDB',
        'url' => 'test-image.png',
        'wifixed testdth' => 100,
        'height' => 100,
        'title' => 'testImage'
      ));
    } else {
      throw new \Exception('Item not found');
    }
  }
}

class rz_page_propertyTest extends \Test\Rukzuk\ModuleTestCase
{

  /**
   * @var \Rukzuk\Modules\rz_page_property
   */
  private $obj = null;

  /**
   * @var \Test\Rukzuk\RenderApiMock
   */
  private $renderApi = null;

  /**
   * @var
   */
  private $unit = null;

  /**
   * @var ModuleInfo
   */
  private $moduleInfo = null;

  public function setup() : void
  {
    $this->obj = new \Rukzuk\Modules\rz_page_property();
    $this->renderApi = new rz_page_propertyTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'rz_page_property',
        'assetUrl' => 'rz_page_property/assets',
      ))
    ));
    $this->unit = $this->createUnit(array(
      'formValues' => array(
        'galleryImageIds' => array('test1', 'test2')
      )
    ));
    $this->moduleInfo = $this->createModuleInfo();
  }

  public function testGetHeadlineTag()
  {
    $content = 'Test Page Title';
    $url = '';

    $renderApi = new rz_page_propertyTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'rz_page_property',
        'assetUrl' => 'rz_page_property/assets',
      ))
    ));

    $unit = $this->createUnit(array(
      'formValues' => array(
        'headlineCharLimit' => 0,
        'enableHeadlineLink' => 0,
        'headlineHtmlElement' => 'h1'
      )
    ));
    $moduleInfo = $this->createModuleInfo();
    $result = $this->obj->getHeadlineTag($renderApi, $unit, $moduleInfo, $content, $url);
    $this->assertEquals('h1', $result->getTagName());
    $this->assertContains('Test Page Title', $result->toString());
  }

  public function testGetHeadlineTagLink()
  {
    $content = 'Test Page Title';
    $url = '';

    $renderApi = new rz_page_propertyTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'rz_page_property',
        'assetUrl' => 'rz_page_property/assets',
      ))
    ));

    $unit = $this->createUnit(array(
      'formValues' => array(
        'headlineCharLimit' => 0,
        'enableHeadlineLink' => 1,
        'headlineHtmlElement' => 'h1'
      )
    ));
    $moduleInfo = $this->createModuleInfo();
    $result = $this->obj->getHeadlineTag($renderApi, $unit, $moduleInfo, $content, $url);
    $this->assertEquals('h1', $result->getTagName());
    $this->assertContains('Test Page Title', $result->toString());
    $this->assertContains('</a>', $result->toString());
  }

  public function testGetHeadlineTagCharLimit()
  {
    $content = 'Test Page Title';
    $url = '';

    $renderApi = new rz_page_propertyTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'rz_page_property',
        'assetUrl' => 'rz_page_property/assets',
      ))
    ));

    $unit = $this->createUnit(array(
      'formValues' => array(
        'headlineCharLimit' => 5,
        'enableHeadlineLink' => 0,
        'headlineHtmlElement' => 'h1'
      )
    ));
    $moduleInfo = $this->createModuleInfo();
    $result = $this->obj->getHeadlineTag($renderApi, $unit, $moduleInfo, $content, $url);
    $this->assertEquals('h1', $result->getTagName());
    $this->assertContains('Test', $result->toString());
  }

  public function testGetTextTag()
  {
    $content = 'Test Page Description';
    $url = '';

    $renderApi = new rz_page_propertyTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'rz_page_property',
        'assetUrl' => 'rz_page_property/assets',
      ))
    ));

    $unit = $this->createUnit(array(
      'formValues' => array(
        'textCharLimit' => 0,
        'enableTextLink' => 0
      )
    ));
    $moduleInfo = $this->createModuleInfo();
    $result = $this->obj->getTextTag($renderApi, $unit, $moduleInfo, $content, $url);
    $this->assertEquals('p', $result->getTagName());
    $this->assertContains('Test Page Description', $result->toString());
  }

  public function testGetTextTagLink()
  {
    $content = 'Test Page Description';
    $url = '#';

    $renderApi = new rz_page_propertyTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'rz_page_property',
        'assetUrl' => 'rz_page_property/assets',
      ))
    ));

    $unit = $this->createUnit(array(
      'formValues' => array(
        'textCharLimit' => 0,
        'enableTextLink' => 1,
        'textLinkLabel' => 'more'
      )
    ));
    $moduleInfo = $this->createModuleInfo();
    $result = $this->obj->getTextTag($renderApi, $unit, $moduleInfo, $content, $url);
    $this->assertEquals('p', $result->getTagName());
    $this->assertContains('Test Page Description', $result->toString());
    $this->assertContains('<a href="#" class="teaserTextLink">more</a>', $result->toString());
  }

  public function testGetTextTagCharLimit()
  {
    $content = 'Test Page Description';
    $url = '';

    $renderApi = new rz_page_propertyTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'rz_page_property',
        'assetUrl' => 'rz_page_property/assets',
      ))
    ));

    $unit = $this->createUnit(array(
      'formValues' => array(
        'textCharLimit' => 5,
        'enableTextLink' => 0
      )
    ));

    $moduleInfo = $this->createModuleInfo();
    $result = $this->obj->getTextTag($renderApi, $unit, $moduleInfo, $content, $url);
    $this->assertEquals('p', $result->getTagName());
    $this->assertContains('Test', $result->toString());
  }

  public function testGetDateTag()
  {
    $content = '01/01/1970';

    $renderApi = new rz_page_propertyTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'rz_page_property',
        'assetUrl' => 'rz_page_property/assets',
      ))
    ));

    $unit = $this->createUnit(array(
      'formValues' => array()
    ));
    $moduleInfo = $this->createModuleInfo();
    $result = $this->obj->getDateTag($renderApi, $unit, $moduleInfo, $content);
    $this->assertEquals('time', $result->getTagName());
    $this->assertContains('1970-01-01', $result->toString());
  }

  public function testGetResponsiveImageBlank()
  {
    $mediaId = 'MDB-KSHDJUIAKD-MDB';
    $tag = $this->obj->getResponsiveImageTag($this->renderApi, $this->unit, $this->moduleInfo, $mediaId, 'test_page', '#');
    $tagChildren = $tag->getChildren();
    $imgTag = $tagChildren[0];
    $this->assertEquals('img', $imgTag->getTagName());
    $this->assertContains('imageBlank.png', $imgTag->get('src'));
  }

}


