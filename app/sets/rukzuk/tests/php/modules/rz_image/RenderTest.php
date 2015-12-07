<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\MediaItemMock;
use \Test\Rukzuk\ModuleTestCase;

use \Rukzuk\Modules\rz_image;

require_once(MODULE_PATH.'/rz_image/module/rz_image.php');

class rz_image_RenderTest_rz_image extends \Rukzuk\Modules\rz_image
{
  public function getResponsiveImageTag($api, $unit, $moduleInfo)
  {
    return parent::getResponsiveImageTag($api, $unit, $moduleInfo);
  }
}

class rz_image_RenderTest_ApiMock extends RenderApiMock
{
  public function getMediaItem($id)
  {
    if (is_string($id)) {
      return new MediaItemMock(array(
        'url' => 'test-image.png',
        'width' => 100,
        'height' => 100,
        'originalWidth' => 100,
        'originalHeight' => 100,
      ));
    } else {
      throw new \Exception('Item not found');
    }
  }
}

class rz_image_RenderTest extends ModuleTestCase
{
  protected $moduleNS = '';
  protected $moduleClass = 'rz_image_RenderTest_rz_image';

  public function testRender_noImage()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit(array(
      'formValues' => array(
        'imageTitle' => 'image title',
        'imageAlt' => 'image alt text'
      )
    ));
    $modulInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // execute
    $tag = $module->getResponsiveImageTag($api, $unit, $modulInfo);

    $tagChildren = $tag->getChildren();
    $imgTag = $tagChildren[0];

    // verify
    $this->assertEquals('img', $imgTag->getTagName());
    $this->assertEquals('image title', $imgTag->get('title'));
    $this->assertEquals('image alt text', $imgTag->get('alt'));
    $this->assertContains('imageModuleImg', $imgTag->get('class'));
    $this->assertContains('blankImgPlaceholder', $imgTag->get('class'));
  }

  public function testRender_withImage()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit(array(
      'formValues' => array(
        'imageTitle' => 'image title',
        'imageAlt' => 'image alt text',
        'imgsrc' => 'test-image'
      )
    ));
    $modulInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // execute
    $tag = $module->getResponsiveImageTag($api, $unit, $modulInfo);

    $tagChildren = $tag->getChildren();
    $imgTag = $tagChildren[0];

    // verify
    $this->assertEquals('div', $tag->getTagName());
    $this->assertEquals('image alt text', $imgTag->get('alt'));
    $this->assertEquals('image title', $imgTag->get('title'));
    $this->assertContains('test-image', $imgTag->get('src'));
  }

  public function testRender_withImageAndCropping()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit(array(
      'formValues' => array(
        'imgsrc' => 'test-image',
        'cropData' => '{"cropXRatio": 0.1, "cropYRatio": 0.2, "cropWidthRatio": 0.3, "cropHeightRatio": 0.4}'
      )
    ));
    $modulInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // execute
    $tag = $module->getResponsiveImageTag($api, $unit, $modulInfo);

    $tagChildren = $tag->getChildren();
    $imgTag = $tagChildren[0];

    // verify
    $this->assertContains('crop(10,20,30,30)', $imgTag->get('data-srcset'));
  }

  public function testRender_withImageAndQuality()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit(array(
      'formValues' => array(
        'imgsrc' => 'test-image',
        'enableImageQuality' => true,
        'imageQuality' => 42
      )
    ));
    $modulInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // execute
    $tag = $module->getResponsiveImageTag($api, $unit, $modulInfo);

    $tagChildren = $tag->getChildren();
    $imgTag = $tagChildren[0];

    // verify
    $this->assertContains('quality(42)', $imgTag->get('data-srcset'));
  }

  private function createApiMock()
  {
    return new rz_image_RenderTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'root-unit-id',
        'assetUrl' => 'root/assets'
      ))
    ));
  }

}
