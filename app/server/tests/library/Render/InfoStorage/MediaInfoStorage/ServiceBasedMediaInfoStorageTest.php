<?php


namespace Render\InfoStorage\MediaInfoStorage;


use Cms\Data\Media as MediaData;
use Cms\Render\InfoStorage\MediaInfoStorage\ServiceBasedMediaInfoStorage;
use Test\Render\TestMediaUrlHelper;

class ServiceBasedMediaInfoStorageTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function test_getById_returnItem()
  {
    // ARRANGE
    $websiteId = 'SITE-664d8c7b-34cf-4068-9da4-303638b6e33b-SITE';
    $mediaId = 'MEDIA-c33c0354-9006-423e-b781-0564d19bca24-MEDIA';
    $name = 'MediaData';
    $filePath = '/media/path/';
    $size = 4096;
    $lastUpdate = '2014-02-11 12:30:30';

    // Create test media data object
    $mediaData = $this->createMediaDataObject($websiteId, $mediaId, $name,
      $filePath, $size, $lastUpdate);

    // Create Media Service Mock
    $mediaService = $this->getMockBuilder('\Cms\Service\Media')
            ->disableOriginalConstructor()->getMock();
    $mediaService->expects($this->once())
            ->method('getById')->with($mediaId, $websiteId)
            ->will($this->returnValue($mediaData));
    $urlHelper = new TestMediaUrlHelper('http://example.com');
    $iconHelper = $this->getMock('Render\IconHelper\IIconHelper');
    $infoStorage = new ServiceBasedMediaInfoStorage($websiteId, null, $mediaService,
      $urlHelper, $iconHelper);

    // ACT
    $item = $infoStorage->getItem($mediaId);

    // ASSERT
    $this->assertInstanceOf('\Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem', $item);
    $this->assertEquals($mediaId, $item->getId());
    $this->assertEquals($name, $item->getName());
    $this->assertEquals('/' . $filePath, $item->getFilePath());
    $this->assertEquals($size, $item->getSize());
    $this->assertEquals($lastUpdate, $item->getLastModified());
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @expectedException \Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItemDoesNotExists
   */
  public function test_getById_noItem()
  {
    $websiteId = 'SITE-b141862a-a9eb-4343-90bd-f18af211b931-SITE';
    $mediaId = 'MEDIA-5ed255b6-d5e5-4f99-9734-8abbd000b9e7-MEDIA';

    // Create Media Service Mock
    $mediaService = $this->getMockBuilder('\Cms\Service\Media')
            ->disableOriginalConstructor()->getMock();
    $mediaService->expects($this->once())
            ->method('getById')->with($mediaId, $websiteId)
            ->will($this->throwException(new \Cms\Exception('232', __METHOD__, __LINE__)));

    $urlHelperMock = $this->getMock('\Render\MediaUrlHelper\IMediaUrlHelper');
    $iconHelper = $this->getMock('Render\IconHelper\IIconHelper');
    $infoStorage = new ServiceBasedMediaInfoStorage($websiteId, null,
      $mediaService, $urlHelperMock, $iconHelper);

    // ACT & ASSERT EXCEPTION
    $infoStorage->getItem($mediaId);
  }

  /**
   * @param $websiteId
   * @param $mediaId
   * @param $name
   * @param $filePath
   * @param $size
   * @param $lastUpdate
   *
   * @return MediaData
   */
  protected function createMediaDataObject($websiteId, $mediaId, $name,
                                           $filePath, $size, $lastUpdate)
  {
    $mediaData = new MediaData();
    $mediaData->setWebsiteid($websiteId);
    $mediaData->setId($mediaId);
    $mediaData->setName($name);
    $mediaData->setFile($filePath);
    $mediaData->setSize($size);
    $mediaData->setLastUpdate($lastUpdate);
    return $mediaData;
  }

}
 