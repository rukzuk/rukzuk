<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;

require_once(MODULE_PATH . '/rz_thumbnail_gallery/module/rz_thumbnail_gallery.php');


class rz_thumbnail_gallery_Mock extends \Rukzuk\Modules\rz_thumbnail_gallery
{
  public function getResponsiveImageTag($api, $unit, $moduleInfo)
  {
    return parent::getResponsiveImageTag($api, $unit, $moduleInfo);
  }
}

class rz_thumbnail_galleryTest_ApiMock extends RenderApiMock
{
  public function getMediaItem($id)
  {
    if (is_string($id) && in_array($id, array('test1', 'test2'))) {
      return new \Test\Rukzuk\MediaItemMock(array(
        'url' => $id . '-image.png',
        'title' => $id . 'Image',
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

/**
 * @method rz_thumbnail_gallery_Mock createModule
 */
class rz_thumbnail_gallery_RenderTest extends \Test\Rukzuk\ModuleTestCase
{
  protected $moduleNS = '';
  protected $moduleClass = 'rz_thumbnail_gallery_Mock';


  public function testRenderContent()
  {
    // ARRANGE
    $expectedIds = array('test1', 'test2');
    $api = $this->createApiMock();
    $unit = $this->createUnit(array('formValues' => array('galleryImageIds' => $expectedIds)));
    $moduleInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // ACT
    ob_start();
    $module->renderContent($api, $unit, $moduleInfo);
    $echo = ob_get_contents();
    ob_end_clean();

    // ASSERT
    $this->assertContains('<ul><li>', $echo);
    foreach ($expectedIds as $expectedId) {
      $this->assertContains($expectedId . '-image.png', $echo);
    }
    $this->assertContains('</li></ul>', $echo);
  }

  public function testGetImageIds()
  {
    // ARRANGE
    $expectedIds = array('test1', 'test2');
    $notExistingIds = array(false, null, '', 0);
    $api = $this->createApiMock();
    $unit = $this->createUnit(array(
      'formValues' => array(
        'galleryImageIds' => array_merge($expectedIds, $notExistingIds)
      )
    ));
    $module = $this->createModule();

    // ACT
    $imageIds = $module->getImageIds($api, $unit);

    // ASSERT
    $this->assertEquals($expectedIds, $imageIds);
  }

  public function test_RenderContent_shouldIgnoreImageExceptions()
  {
    // ARRANGE
    $existingIds = array('test1', 'test2');
    $notExistingIds = array('not_exists', null);
    $api = $this->createApiMock();
    $unitWithNotExistingImages = $this->createUnit(array(
      'formValues' => array(
        'galleryImageIds' => array_merge($existingIds, $notExistingIds)
      )
    ));
    $moduleInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // ACT
    ob_start();
    $module->renderContent($api, $unitWithNotExistingImages, $moduleInfo);
    $echo = ob_get_contents();
    ob_end_clean();

    // ASSERT
    $this->assertContains('<ul><li>', $echo);
    $this->assertContains('</li></ul>', $echo);
    foreach ($existingIds as $expectedId) {
      $this->assertContains($expectedId . '-image.png', $echo);
    }
    preg_match_all('/<img.*?>/i', $echo, $matches, PREG_SET_ORDER);
    $this->assertCount(2, $matches);

  }

  /**
   * @return rz_thumbnail_galleryTest_ApiMock
   */
  protected function createApiMock()
  {
    return new rz_thumbnail_galleryTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'rz_thumbnail_gallery',
        'assetUrl' => 'rz_thumbnail_gallery/assets'
      ))
    ));
  }

}
