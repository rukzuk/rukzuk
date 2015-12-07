<?php


namespace Render\APIs\APIv1;


use Render\ImageToolFactory\SimpleImageToolFactory;
use Test\Render\TestMediaUrlHelper;

class MediaImageTest extends \PHPUnit_Framework_TestCase
{
  /**
   * Check getMediaItem
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_getMediaItem()
  {
    // Create MediaContext Mock
    $mediaContext = $this->createMediaContextMock();
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);

    // ASSERT
    $this->assertEquals($mediaItem, $mediaImage->getMediaItem());
  }

  /**
   * Check getMediaItem
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_getMediaId()
  {
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    // Create MediaContext Mock
    $mediaContext = $this->createMediaContextMock();
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($mediaId));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);

    // ASSERT
    $this->assertEquals($mediaId, $mediaImage->getMediaId());
  }

  /**
   * Check getUrl
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_getUrl()
  {
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    $mediaUrl = 'URL/'.$mediaId;

    // Create MediaInfoStorage Mock
    $mediaInfoStorage = $this->getMock('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage');
    // Create UrlHelper Mock
    $mediaInfoStorage->expects($this->once())->method('getImageUrl')
      ->with($mediaId, array())
      ->will($this->returnValue($mediaUrl));

    // Create MediaContext Mock
    $mediaContext = $this->createMediaContextMock();
    $mediaContext->expects($this->atLeastOnce())
            ->method('getMediaInfoStorage')
            ->will($this->returnValue($mediaInfoStorage));
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($mediaId));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);

    // ASSERT
    $this->assertEquals($mediaUrl, $mediaImage->getUrl());
  }

  /**
   * Check setQuality
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_setQuality()
  {
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    $mediaUrl = 'URL/'.$mediaId;
    $q = 80.9;

    // Create MediaInfoStorage Mock
    $mediaInfoStorage = $this->getMock('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage');
    // Create UrlHelper Mock
    $mediaInfoStorage->expects($this->once())->method('getImageUrl')
            ->with($mediaId, array(array('quality', intval($q))))
            ->will($this->returnValue($mediaUrl));

    // Create MediaContext Mock
    $mediaContext = $this->createMediaContextMock();
    $mediaContext->expects($this->atLeastOnce())
            ->method('getMediaInfoStorage')
            ->will($this->returnValue($mediaInfoStorage));
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($mediaId));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);
    $mediaImage->setQuality($q);

    // ASSERT
    $this->assertEquals($mediaUrl, $mediaImage->getUrl());
  }

  /**
   * Check setInterlaced
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_setInterlaced()
  {
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    $mediaUrl = 'URL/'.$mediaId;
    $inter = true;

    // Create MediaInfoStorage Mock
    $mediaInfoStorage = $this->getMock('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage');
    // Create UrlHelper Mock
    $mediaInfoStorage->expects($this->once())->method('getImageUrl')
            ->with($mediaId, array(array('interlace', $inter)))
            ->will($this->returnValue($mediaUrl));

    // Create MediaContext Mock
    $mediaContext = $this->createMediaContextMock();
    $mediaContext->expects($this->atLeastOnce())
            ->method('getMediaInfoStorage')
            ->will($this->returnValue($mediaInfoStorage));
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($mediaId));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);
    $mediaImage->setInterlaced($inter);

    // ASSERT
    $this->assertEquals($mediaUrl, $mediaImage->getUrl());
  }

  /**
   * Check resize
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_resize_dontKeepAspectRation()
  {
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    $mediaUrl = 'URL/'.$mediaId;
    $width = 320;
    $height = 240;
    $operations = array(
      array('resize', $width, $height, MediaImage::RESIZE_STRETCH)
    );

    // Create MediaInfoStorage Mock
    $mediaInfoStorage = $this->getMock('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage');
    // Create UrlHelper Mock
    $mediaInfoStorage->expects($this->once())->method('getImageUrl')
            ->with($mediaId, $operations)
            ->will($this->returnValue($mediaUrl));

    // Create MediaContext Mock
    $mediaContext = $this->createMediaContextMock();
    $mediaContext->expects($this->atLeastOnce())
            ->method('getMediaInfoStorage')
            ->will($this->returnValue($mediaInfoStorage));
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($mediaId));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);
    $mediaImage->resizeStretch(320, 240);

    // ASSERT
    $this->assertEquals(320, $mediaImage->getWidth());
    $this->assertEquals(240, $mediaImage->getHeight());
    $this->assertEquals($mediaUrl, $mediaImage->getUrl());
  }

  /**
   * Check resize
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_resize_keepAspectRation()
  {
    $filePath = '/media/id/filePath';
    $width = 640;
    $height = 480;
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    $mediaUrl = 'URL/'.$mediaId;
    $newWidth = $width/2;
    $newHeight = $newWidth;
    $resHeight = $height/2;
    $operations = array(
      array('resize', $newWidth, $newHeight, MediaImage::RESIZE_SCALE)
    );

    // Create MediaInfoStorage Mock
    $mediaInfoStorage = $this->getMock('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage');
    $mediaInfoStorage->expects($this->once())->method('getImageUrl')
            ->with($mediaId, $operations)
            ->will($this->returnValue($mediaUrl));
    // Create image tool mock
    $imageTool = $this->getMockBuilder('\Render\ImageToolFactory\ImageTool')
            ->disableOriginalConstructor()->getMock();
    $imageTool->expects($this->once()) // assert that the method will be called once
            ->method('getDimensionFromFile') // Method to mock
            ->with($this->equalTo($filePath)) // assert parameters
            ->will($this->returnValue(
              array('width' => $width, 'height' => $height) // mock the return value
            ));
    $imageTool->expects($this->once())->method('isImageFile')
            ->will($this->returnValue(true));

    // Create MediaContext Mock
    $mediaContext = $this->getMockBuilder('\Render\MediaContext')
            ->disableOriginalConstructor()->getMock();
    $mediaContext->expects($this->atLeastOnce())
            ->method('getMediaInfoStorage')
            ->will($this->returnValue($mediaInfoStorage));
    $mediaContext->expects($this->exactly(2))
            ->method('getImageTool')->will($this->returnValue($imageTool));
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($mediaId));
    $mediaItem->expects($this->exactly(2))
            ->method('getFilePath')
            ->will($this->returnValue($filePath));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);
    $mediaImage->resizeScale($newWidth, $newHeight);

    // ASSERT
    $this->assertEquals($newWidth, $mediaImage->getWidth());
    $this->assertEquals($resHeight, $mediaImage->getHeight());
    $this->assertEquals($mediaUrl, $mediaImage->getUrl());
  }

  /**
   * Check resize
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_resizeScale_didntResizeIfImageSmallerThanNewSize()
  {
    $filePath = '/media/id/filePath';
    $width = 640;
    $height = 480;
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    $mediaUrl = 'URL/'.$mediaId;
    $newWidth = $width+1;
    $newHeight = $height+1;
    $operations = array(
      array('resize', $newWidth, $newHeight, MediaImage::RESIZE_SCALE)
    );

    // Create MediaInfoStorage Mock
    $mediaInfoStorage = $this->getMock('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage');
    $mediaInfoStorage->expects($this->once())->method('getImageUrl')
      ->with($mediaId, $operations)
      ->will($this->returnValue($mediaUrl));
    // Create image tool mock
    $imageTool = $this->getMockBuilder('\Render\ImageToolFactory\ImageTool')
      ->disableOriginalConstructor()->getMock();
    $imageTool->expects($this->once()) // assert that the method will be called once
    ->method('getDimensionFromFile') // Method to mock
    ->with($this->equalTo($filePath)) // assert parameters
    ->will($this->returnValue(
      array('width' => $width, 'height' => $height) // mock the return value
    ));
    $imageTool->expects($this->once())->method('isImageFile')
      ->will($this->returnValue(true));

    // Create MediaContext Mock
    $mediaContext = $this->getMockBuilder('\Render\MediaContext')
      ->disableOriginalConstructor()->getMock();
    $mediaContext->expects($this->atLeastOnce())
      ->method('getMediaInfoStorage')
      ->will($this->returnValue($mediaInfoStorage));
    $mediaContext->expects($this->exactly(2))
      ->method('getImageTool')->will($this->returnValue($imageTool));
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
      ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
      ->method('getId')
      ->will($this->returnValue($mediaId));
    $mediaItem->expects($this->exactly(2))
      ->method('getFilePath')
      ->will($this->returnValue($filePath));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);
    $mediaImage->resizeScale($newWidth, $newHeight);

    // ASSERT
    $this->assertEquals($width, $mediaImage->getWidth());
    $this->assertEquals($height, $mediaImage->getHeight());
    $this->assertEquals($mediaUrl, $mediaImage->getUrl());
  }

  /**
   * Check resize
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_resizeBorder()
  {
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    $mediaUrl = 'URL/'.$mediaId;
    $width = 230;
    $height = 420;
    $operations = array(
      array('resize', $width, $height, MediaImage::RESIZE_AND_FILL)
    );

    // Create MediaInfoStorage Mock
    $mediaInfoStorage = $this->getMock('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage');
    // Create UrlHelper Mock
    $mediaInfoStorage->expects($this->once())->method('getImageUrl')
      ->with($mediaId, $operations)
      ->will($this->returnValue($mediaUrl));

    // Create MediaContext Mock
    $mediaContext = $this->createMediaContextMock();
    $mediaContext->expects($this->atLeastOnce())
      ->method('getMediaInfoStorage')
      ->will($this->returnValue($mediaInfoStorage));
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
      ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
      ->method('getId')
      ->will($this->returnValue($mediaId));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);
    $mediaImage->resizeBorder(230, 420);

    // ASSERT
    $this->assertEquals(230, $mediaImage->getWidth());
    $this->assertEquals(420, $mediaImage->getHeight());
    $this->assertEquals($mediaUrl, $mediaImage->getUrl());
  }

  /**
   * Check crop
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_crop()
  {
    $width = 640;
    $height = 480;
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    $mediaUrl = 'URL/'.$mediaId;
    $left = 1;
    $top = 2;
    $right = 3;
    $bottom = 4;
    $newWidth = $width-$left-$right;
    $newHeight = $height-$top-$bottom;
    $operations = array(
      array('crop', $top, $left, $newWidth, $newHeight)
    );

    // Create MediaInfoStorage Mock
    $mediaInfoStorage = $this->getMock('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage');
    $mediaInfoStorage->expects($this->once())->method('getImageUrl')
            ->with($mediaId, $operations)
            ->will($this->returnValue($mediaUrl));

    // Create MediaContext Mock
    $mediaContext = $this->createMediaContextMock();
    $mediaContext->expects($this->atLeastOnce())
            ->method('getMediaInfoStorage')
            ->will($this->returnValue($mediaInfoStorage));
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($mediaId));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);
    $mediaImage->crop($top, $left, $newWidth, $newHeight);

    // ASSERT
    $this->assertEquals($newWidth, $mediaImage->getWidth());
    $this->assertEquals($newHeight, $mediaImage->getHeight());
    $this->assertEquals($mediaUrl, $mediaImage->getUrl());
  }

  /**
   * Check crop and resize chaining
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_crop_resize()
  {
    $mediaId = 'MEDIA-a4464129-a64e-4af9-bb07-b94322fb36c8-MEDIA';
    $mediaUrl = 'URL/'.$mediaId;
    $left = 1;
    $top = 2;
    $newWidth = 320;
    $newHeight = 240;
    $operations = array(
      array('crop', $top, $left, 640, 480),
      array('resize', $newWidth, $newHeight, MediaImage::RESIZE_STRETCH)
    );

    // Create MediaInfoStorage Mock
    $mediaInfoStorage = $this->getMock('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage');
    $mediaInfoStorage->expects($this->once())->method('getImageUrl')
            ->with($mediaId, $operations)
            ->will($this->returnValue($mediaUrl));

    // Create MediaContext Mock
    $mediaContext = $this->createMediaContextMock();
    $mediaContext->expects($this->atLeastOnce())
            ->method('getMediaInfoStorage')
            ->will($this->returnValue($mediaInfoStorage));
    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($mediaId));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);
    $mediaImage->crop($top, $left, 640, 480);
    $mediaImage->resizeStretch($newWidth, $newHeight);

    // ASSERT
    $this->assertEquals($newWidth, $mediaImage->getWidth());
    $this->assertEquals($newHeight, $mediaImage->getHeight());
    $this->assertEquals($mediaUrl, $mediaImage->getUrl());
  }

  /**
   * Check init dimensions and getWidth and getHeight
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function  test_initImageDimensions()
  {
    $filePath = '/media/id/filePath';
    $width = 640;
    $height = 480;

    // Create ImageTool Mock
    $imageTool = $this->getMockBuilder('\Render\ImageToolFactory\ImageTool')
            ->disableOriginalConstructor()->getMock();
    $imageTool->expects($this->once()) // assert that the method will be called once
            ->method('getDimensionFromFile') // Method to mock
            ->with($this->equalTo($filePath)) // assert parameters
            ->will($this->returnValue(
              array('width' => $width, 'height' => $height) // mock the return value
            ));
    $imageTool->expects($this->once())->method('isImageFile')
            ->will($this->returnValue(true));

    // Create MediaContext Mock
    $mediaContext = $this->getMockBuilder('\Render\MediaContext')
            ->disableOriginalConstructor()->getMock();
    $mediaContext->expects($this->never())
            ->method('getMediaInfoStorage')
            ->will($this->returnValue(null));
    $mediaContext->expects($this->any())
            ->method('getImageTool')
            ->will($this->returnValue($imageTool));

    // Create MediaItem Mock
    $mediaItem = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
            ->disableOriginalConstructor()->getMock();
    $mediaItem->expects($this->exactly(2))
            ->method('getFilePath')
            ->will($this->returnValue($filePath));

    // ACT
    $mediaImage = new MediaImage($mediaContext, $mediaItem);

    // ASSERT
    $this->assertEquals($width, $mediaImage->getWidth());
    $this->assertEquals($height, $mediaImage->getHeight());
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  protected function createMediaContextMock()
  {
    $mediaContext = $this->getMockBuilder('\Render\MediaContext')
            ->disableOriginalConstructor()->getMock();
    $imageTool = $this->getMockBuilder('\Render\ImageToolFactory\ImageTool')
            ->disableOriginalConstructor()->getMock();
    $imageTool->expects($this->once())->method('isImageFile')
            ->will($this->returnValue(true));
    $mediaContext->expects($this->once())->method('getImageTool')
            ->will($this->returnValue($imageTool));
    return $mediaContext;
  }

}
 