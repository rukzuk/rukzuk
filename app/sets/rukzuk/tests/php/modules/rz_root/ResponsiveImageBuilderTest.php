<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;
use \Test\Rukzuk\MediaItemMock;
use \Rukzuk\Modules\ResponsiveImageBuilder;

class ResponsiveImageBuilderTest_ApiMock extends RenderApiMock
{
}


class ResponsiveImageBuilderTest extends ModuleTestCase
{
  public function testBuildPlaceholder()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit();
    $responsiveImageBuilder = new ResponsiveImageBuilder($api, $unit);
    // execute
    $tag = $responsiveImageBuilder->getImageTag()->toString();
    // verify
    $this->assertContains('<img alt="" class="imgSize blankImgPlaceholder" src="root/assets/images/imageBlank.png">', $tag);
  }

  public function testBuildPlaceholderWithCustomClass()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit();
    $responsiveImageBuilder = new ResponsiveImageBuilder($api, $unit);
    // execute
    $tag = $responsiveImageBuilder->getImageTag(null, null, array(
      'class' => 'custom-class'
    ))->toString();
    // verify
    $this->assertContains('<img alt="" class="custom-class imgSize blankImgPlaceholder" src="root/assets/images/imageBlank.png">', $tag);
  }

  public function testBuildPlaceholderWithEncodedAltTag()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit();
    $responsiveImageBuilder = new ResponsiveImageBuilder($api, $unit);
    // execute
    $tag = $responsiveImageBuilder->getImageTag(null, null, array(
      'alt' => '<alt tag>'
    ))->toString();
    // verify
    $this->assertContains('<img alt="&lt;alt tag&gt;" class="imgSize blankImgPlaceholder" src="root/assets/images/imageBlank.png">', $tag);
  }

  public function testBuildImageTag()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit();
    $responsiveImageBuilder = new ResponsiveImageBuilder($api, $unit);
    $mediaItem = $this->createMediaItem(array(
      'url' => 'foo.png',
      'width' => 400,
      'height' => 42,
      'originalWidth' => 400,
      'originalHeight' => 42,
    ));
    // execute
    $tag = $responsiveImageBuilder->getImageTag($mediaItem->getImage())->toString();
    // verify
    $this->assertContains('/resizeScale(320)/quality(85)/foo.png', $tag);
    // default
    $this->assertContains('data-cms-origsrc=', $tag);
    $this->assertContains('src="/resizeScale(64)/quality(85)/foo.png"', $tag);
  }

  public function testBuildImageTag_crop()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit();
    $responsiveImageBuilder = new ResponsiveImageBuilder($api, $unit);
    $mediaItem = $this->createMediaItem(array(
      'url' => 'foo.png',
      'width' => 2000,
      'height' => 42,
      'originalWidth' => 2000,
      'originalHeight' => 42
    ));
    // execute
    $tag = $responsiveImageBuilder->getImageTag($mediaItem->getImage(), array(
      'crop' => array(
        'x' => 10,
        'y' => 20,
        'width' => 30,
        'height' => 40
      )
    ))->toString();
    // verify
    $this->assertContains('/crop(10,20,30,40)/resizeScale(320)/quality(85)/foo.png', $tag);
    $this->assertContains('/crop(10,20,30,40)/resizeScale(480)/quality(85)/foo.png', $tag);
    $this->assertContains('/crop(10,20,30,40)/resizeScale(768)/quality(85)/foo.png', $tag);
    $this->assertContains('/crop(10,20,30,40)/resizeScale(1024)/quality(85)/foo.png', $tag);
    $this->assertContains('/crop(10,20,30,40)/resizeScale(1280)/quality(85)/foo.png', $tag);
    $this->assertContains('/crop(10,20,30,40)/resizeScale(1440)/quality(85)/foo.png', $tag);
    $this->assertContains('/crop(10,20,30,40)/resizeScale(1600)/quality(85)/foo.png', $tag);
    $this->assertContains('/crop(10,20,30,40)/resizeScale(1920)/quality(85)/foo.png', $tag);
    $this->assertContains('src="/crop(10,20,30,40)/resizeScale(64)/quality(85)/foo.png"', $tag);
  }

  public function testBuildImageTag_resize()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit();
    $responsiveImageBuilder = new ResponsiveImageBuilder($api, $unit);
    $mediaItem = $this->createMediaItem(array(
      'url' => 'foo.png',
      'width' => 2000,
      'height' => 42,
      'originalWidth' => 2000,
      'originalHeight' => 42,
    ));
    // execute
    $tag = $responsiveImageBuilder->getImageTag($mediaItem->getImage(), array(
      'resize' => array(
        'width' => 10,
        'height' => 20
      )
    ))->toString();
    // verify
    $this->assertContains('/resizeCenter(10,20)/resizeScale(320)/quality(85)/foo.png', $tag);
    $this->assertContains('/resizeCenter(10,20)/resizeScale(480)/quality(85)/foo.png', $tag);
    $this->assertContains('/resizeCenter(10,20)/resizeScale(768)/quality(85)/foo.png', $tag);
    $this->assertContains('/resizeCenter(10,20)/resizeScale(1024)/quality(85)/foo.png', $tag);
    $this->assertContains('/resizeCenter(10,20)/resizeScale(1280)/quality(85)/foo.png', $tag);
    $this->assertContains('/resizeCenter(10,20)/resizeScale(1440)/quality(85)/foo.png', $tag);
    $this->assertContains('/resizeCenter(10,20)/resizeScale(1600)/quality(85)/foo.png', $tag);
    $this->assertContains('/resizeCenter(10,20)/resizeScale(1920)/quality(85)/foo.png', $tag);
    $this->assertContains('src="/resizeCenter(10,20)/resizeScale(64)/quality(85)/foo.png"', $tag);
  }

  public function testBuildImageTag_quality()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit();
    $responsiveImageBuilder = new ResponsiveImageBuilder($api, $unit);
    $mediaItem = $this->createMediaItem(array(
      'url' => 'foo.png',
      'width' => 2000,
      'height' => 42,
      'originalWidth' => 2000,
      'originalHeight' => 42,
    ));
    // execute
    $tag = $responsiveImageBuilder->getImageTag($mediaItem->getImage(), array(
      'quality' => 42
    ))->toString();
    // verify
    $this->assertContains('/resizeScale(320)/quality(42)/foo.png', $tag);
    $this->assertContains('/resizeScale(480)/quality(42)/foo.png', $tag);
    $this->assertContains('/resizeScale(768)/quality(42)/foo.png', $tag);
    $this->assertContains('/resizeScale(1024)/quality(42)/foo.png', $tag);
    $this->assertContains('/resizeScale(1280)/quality(42)/foo.png', $tag);
    $this->assertContains('/resizeScale(1440)/quality(42)/foo.png', $tag);
    $this->assertContains('/resizeScale(1600)/quality(42)/foo.png', $tag);
    $this->assertContains('/resizeScale(1920)/quality(42)/foo.png', $tag);
    $this->assertContains('src="/resizeScale(64)/quality(42)/foo.png"', $tag);
  }

  //
  //
  // helper
  //
  //

  protected function createMediaItem($cfg = array())
  {
    return new MediaItemMock(array_merge(array(
      'url' => 'foo.png',
      'width' => 42,
      'height' => 42,
      'originalWidth' => 42,
      'originalHeight' => 42,
    ), $cfg));
  }

  protected function createApiMock()
  {
    return new ResponsiveImageBuilderTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'assetUrl' => 'root/assets'
      ))
    ));
  }
}
