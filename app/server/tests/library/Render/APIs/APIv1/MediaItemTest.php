<?php


namespace Render\APIs\APIv1;

use Render\IconHelper\SimpleIconHelper;
use Render\InfoStorage\MediaInfoStorage\ArrayBasedMediaInfoStorage;
use Test\Render\TestMediaUrlHelper;

class MediaItemTestArrayBasedMediaInfoStorage extends ArrayBasedMediaInfoStorage
{
  private $calls = array();

  public function getItem($mediaID)
  {
    $return = parent::getItem($mediaID);
    $this->calls[$mediaID] = array($mediaID, 'return' => $return);
    return $return;
  }

  public function getCalls()
  {
    return $this->calls;
  }
}


class MediaItemTest extends \PHPUnit_Framework_TestCase
{

  const URL_PREFIX = 'http://example.com';

  /**
   * Checks that a getId call does not call the info storage
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getId()
  {
    // ARRANGE
    $item1 = $this->generateMediaInfoStorageItem(
      'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA',
      15, 1392029937);
    $itemMap = array($item1['id'] => $item1);
    $mediaContext = $this->getMediaContext($itemMap);
    $mediaId = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT
    $returnId = $mediaItem->getId();
    // ASSERT
    $this->assertEquals($mediaId, $returnId);
  }

  /**
   * Checks that a getName call does not call the info storage
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getName()
  {
    // ARRANGE
    $id = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $name = 'Name of ' . $id;
    $filePath = '/file/path/of/' . $id;
    $size = 15;
    $lastModified = time()-3600;
    $item1 = array('id' => $id, 'name' => $name, 'filePath' => $filePath,
      'size' => $size, 'lastModified' => $lastModified);
    $itemMap = array($item1['id'] => $item1);
    $mediaContext = $this->getMediaContext($itemMap);
    $mediaId = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT
    $returnName = $mediaItem->getName();
    // ASSERT
    $this->assertEquals($name, $returnName);
  }

  /**
   * Checks that a getSize call does not call the info storage
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getSize()
  {
    // ARRANGE
    $id = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $name = 'Name of ' . $id;
    $filePath = '/file/path/of/' . $id;
    $size = 15;
    $lastModified = time()-3600;
    $item1 = array('id' => $id, 'name' => $name, 'filePath' => $filePath,
      'size' => $size, 'lastModified' => $lastModified);
    $itemMap = array($item1['id'] => $item1);
    $mediaContext = $this->getMediaContext($itemMap);
    $mediaId = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT &  ASSERT
    $this->assertEquals($size, $mediaItem->getSize());
  }

  /**
   * Checks that a getLastModified returns expected time
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getLastModified()
  {
    // ARRANGE
    $id = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $name = 'Name of ' . $id;
    $filePath = '/file/path/of/' . $id;
    $size = 15;
    $lastModified = time()-3600;
    $item1 = array('id' => $id, 'name' => $name, 'filePath' => $filePath,
      'size' => $size, 'lastModified' => $lastModified);
    $itemMap = array($item1['id'] => $item1);
    $mediaContext = $this->getMediaContext($itemMap);
    $mediaId = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT &  ASSERT
    $this->assertEquals($lastModified, $mediaItem->getLastModified());
  }

  /**
   * Checks that a getFilePath call does not call the info storage
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getFilePath()
  {
    // ARRANGE
    $id = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $name = 'Name of ' . $id;
    $filePath = '/file/path/of/' . $id;
    $size = 15;
    $lastModified = time()-3600;
    $item1 = array('id' => $id, 'name' => $name, 'filePath' => $filePath,
      'size' => $size, 'lastModified' => $lastModified);
    $itemMap = array($item1['id'] => $item1);
    $mediaContext = $this->getMediaContext($itemMap);
    $mediaId = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT &  ASSERT
    $this->assertEquals($filePath, $mediaItem->getFilePath());
  }

  /**
   * Checks that the info storage is only called once
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_callInfoStorageOnce()
  {
    // ARRANGE
    $item1 = $this->generateMediaInfoStorageItem(
      'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA',
      15, 1392029937);
    $itemMap = array($item1['id'] => $item1);
    $mediaContext = $this->getMediaContext($itemMap);
    $mediaId = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT
    $returnId = $mediaItem->getId();
    $returnName = $mediaItem->getName();
    $returnSize = $mediaItem->getSize();
    // ASSERT
    $this->assertEquals($mediaId, $returnId);
    $this->assertEquals('Name of ' . $mediaId, $returnName);
    $this->assertEquals(15, $returnSize);
    $this->assertEquals(1, count($mediaContext->getMediaInfoStorage()->getCalls()));
  }

  /**
   * Checks what happens when the mediaID is wrong
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @expectedException \Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItemDoesNotExists
   */
  public function test_wrongMediaId()
  {
    // ARRANGE
    $item1 = $this->generateMediaInfoStorageItem(
      'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA',
      15, 1392029937);
    $itemMap = array($item1['id'] => $item1);
    $mediaContext = $this->getMediaContext($itemMap);
    $mediaId = 'MEDIA-4100188d-xxxx-xxxx-xxxx-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT
    $returnId = $mediaItem->getId();
    // ASSERT
    $this->assertEquals($mediaId, $returnId); // That should work (no lookup)
    $mediaItem->getName(); // Must throw an exception!
  }

  /**
   * Checks the Url generation
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function test_getUrl()
  {
    // ARRANGE
    $item1 = $this->generateMediaInfoStorageItem(
      'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA',
      15, 1392029937);
    $itemMap = array($item1['id'] => $item1);
    $urlHelper = new TestMediaUrlHelper(self::URL_PREFIX);
    $mediaContext = $this->getMediaContext($itemMap, $urlHelper);
    $mediaId = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT
    $returnUrl = $mediaItem->getUrl();
    // ASSERT
    $this->assertEquals(self::URL_PREFIX . '/' . $mediaId . '/' . $item1['name'],
      $returnUrl);
    // Check that exact one url was generated
    $this->assertEquals(1, count($urlHelper->getCalls()));
    $calls = $urlHelper->getCalls();
    $this->assertEquals('getUrl', $calls[0]['method']);
    $this->assertEquals($mediaId, $calls[0]['params'][0]);
    $this->assertEquals($returnUrl, $calls[0]['return']);
    // Check that no info storage call has been occurred (expensive)
    $this->assertEquals(1, count($mediaContext->getMediaInfoStorage()->getCalls()));
  }

  /**
   * Checks the Download Url generation
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function test_getDownloadUrl()
  {
    // ARRANGE
    $item1 = $this->generateMediaInfoStorageItem(
      'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA',
      15, 1392029937);
    $itemMap = array($item1['id'] => $item1);
    $urlHelper = new TestMediaUrlHelper(self::URL_PREFIX);
    $mediaContext = $this->getMediaContext($itemMap, $urlHelper);
    $mediaId = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT
    $returnUrl = $mediaItem->getDownloadUrl();
    // ASSERT
    $this->assertEquals(self::URL_PREFIX . '/download/' . $mediaId . '/' . $item1['name'], $returnUrl);
    // Check that exact one url was generated
    $this->assertEquals(1, count($urlHelper->getCalls()));
    $calls = $urlHelper->getCalls();
    $this->assertEquals('getDownloadUrl', $calls[0]['method']);
    $this->assertEquals($mediaId, $calls[0]['params'][0]);
    $this->assertEquals($returnUrl, $calls[0]['return']);
    // Check that no info storage call has been occurred (expensive)
    $this->assertEquals(1, count($mediaContext->getMediaInfoStorage()->getCalls()));
  }

  /**
   * Checks the getImage method
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function test_getImage()
  {
    // ARRANGE
    $item1 = $this->generateMediaInfoStorageItem(
      'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA',
      15, 1392029937);
    $itemMap = array($item1['id'] => $item1);
    $urlHelper = new TestMediaUrlHelper(self::URL_PREFIX);
    $mediaContext = $this->getMediaContext($itemMap, $urlHelper);
    $mediaId = 'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA';
    $mediaItem = new MediaItem($mediaContext, $mediaId);
    // ACT
    $image = $mediaItem->getImage();
    // ASSERT
    $this->assertEmpty($urlHelper->getCalls());
    $this->assertInstanceOf('\Render\APIs\APIv1\MediaImage', $image);
    $this->assertEquals($mediaItem, $image->getMediaItem());
  }

  protected function getMediaContext(array $itemMap, $urlHelper = null)
  {
    if (is_null($urlHelper)) {
      $urlHelper = new TestMediaUrlHelper(self::URL_PREFIX);
    }
    $iconHelper = new SimpleIconHelper('dir', 'fallback');
    $infoStorage = new MediaItemTestArrayBasedMediaInfoStorage($itemMap, $urlHelper, $iconHelper);
    $mediaContext = $this->getMockBuilder('\Render\MediaContext')
            ->disableOriginalConstructor()->getMock();
    $mediaContext->expects($this->atLeastOnce())
            ->method('getMediaInfoStorage')
            ->will($this->returnValue($infoStorage));
    $imageTool = $this->getMockBuilder('\Render\ImageToolFactory\ImageTool')
            ->disableOriginalConstructor()->getMock();
    $imageTool->expects($this->any())->method('isImageFile')
            ->will($this->returnValue(true));
    $mediaContext->expects($this->any())
            ->method('getImageTool')->will($this->returnValue($imageTool));
    return $mediaContext;
  }

  protected function generateMediaInfoStorageItem($id, $size, $lastModified)
  {
    return array('id' => $id, 'name' => 'Name of ' . $id,
      'filePath' => '/file/path/of/' . $id, 'size' => $size,
      'lastModified' => $lastModified);
  }
}
 