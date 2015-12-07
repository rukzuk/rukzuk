<?php


namespace Render;

use Render\ImageToolFactory\IImageToolFactory;
use Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage;
use \Seitenbau\Image as SBImage;

class MediaContext
{

  /**
   * @var InfoStorage\MediaInfoStorage\IMediaInfoStorage
   */
  private $mediaInfoStorage;

  /**
   * @var ImageToolFactory\IImageToolFactory
   */
  private $imageToolFactory;

  public function __construct(
      IMediaInfoStorage $mediaInfoStorage,
      IImageToolFactory $imageToolFactory
  ) {
    $this->mediaInfoStorage = $mediaInfoStorage;
    $this->imageToolFactory = $imageToolFactory;
  }

  /**
   * @return InfoStorage\MediaInfoStorage\IMediaInfoStorage
   */
  public function getMediaInfoStorage()
  {
    return $this->mediaInfoStorage;
  }

  /**
   * @return ImageToolFactory\ImageTool
   */
  public function getImageTool()
  {
    return $this->imageToolFactory->createImageTool();
  }
}
