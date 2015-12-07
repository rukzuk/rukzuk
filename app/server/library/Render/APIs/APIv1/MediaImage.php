<?php


namespace Render\APIs\APIv1;

use Render\MediaContext;

/**
 * Media database item image manipulation class of the APIv1.
 *
 * It uses some lazy initialisations to reduce the image url creation time.
 *
 * @package Render\APIs\APIv1
 */
class MediaImage
{
  const RESIZE_STRETCH = 0;
  const RESIZE_CENTER = 1;
  const RESIZE_AND_FILL = 2;
  const RESIZE_SCALE = 3;

  /**
   * @var int|null
   */
  private $originalWidth = null;

  /**
   * @var int|null
   */
  private $originalHeight = null;

  /**
   * @var int|null
   */
  private $width = null;

  /**
   * @var int|null
   */
  private $height = null;

  /**
   * @var array
   */
  private $imageOperations = array();

  /**
   * @var MediaItem
   */
  private $mediaItem;

  /**
   * @var \Render\MediaContext
   */
  private $mediaContext;

  /**
   * Creates a new Image object
   *
   * @param \Render\MediaContext $mediaContext
   * @param MediaItem            $mediaItem the related media item
   *
   * @throws MediaImageInvalidException
   */
  public function __construct(MediaContext $mediaContext, MediaItem $mediaItem)
  {
    $this->mediaItem = $mediaItem;
    $this->mediaContext = $mediaContext;
    $this->validate();
  }

  /**
   * @throws MediaImageInvalidException
   */
  protected function validate()
  {
    $filePath = $this->getFilePath();
    $imageTool = $this->getImageTool();
    if (!$imageTool->isImageFile($filePath)) {
      throw new MediaImageInvalidException();
    };
  }

  /**
   * Reset the modify operations
   */
  public function resetOperations()
  {
    $this->resetImageOperation();
    $this->setDimensions($this->getOriginalWidth(), $this->getOriginalHeight());
  }

  /**
   * @return MediaItem
   */
  public function getMediaItem()
  {
    return $this->mediaItem;
  }

  /**
   * @return string
   */
  public function getMediaId()
  {
    return $this->getMediaItem()->getId();
  }

  /**
   * Returns the width of the image
   *
   * @return int
   */
  public function getWidth()
  {
    $this->initImageDimensions();
    return $this->width;
  }

  /**
   * Returns the height of the image
   *
   * @return int
   */
  public function getHeight()
  {
    $this->initImageDimensions();
    return $this->height;
  }

  /**
   * @return int|null
   */
  public function getOriginalWidth()
  {
    $this->initOriginalImageDimensions();
    return $this->originalWidth;
  }

  /**
   * @return int|null
   */
  public function getOriginalHeight()
  {
    $this->initOriginalImageDimensions();
    return $this->originalHeight;
  }

  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->getMediaContext()->getMediaInfoStorage()->getImageUrl(
        $this->getMediaId(),
        $this->getImageOperations()
    );
  }

  public function resizeScale($maxWidth = null, $maxHeight = null)
  {
    if (is_null($maxWidth) && is_null($maxHeight)) {
      return $this;
    }
    $this->addImageOperation(array(
      'resize', intval($maxWidth), intval($maxHeight), self::RESIZE_SCALE
    ));
    if (is_null($maxWidth)) {
      $maxWidth = PHP_INT_MAX;
    } else {
      $maxWidth = intval($maxWidth);
    }
    if (is_null($maxHeight)) {
      $maxHeight = PHP_INT_MAX;
    } else {
      $maxHeight = intval($maxHeight);
    }
    if ($maxWidth >= $this->getWidth() && $maxHeight >= $this->getHeight()) {
      return $this;
    }
    $scale = $this->calculateScale($maxWidth, $maxHeight);
    $this->setDimensions(
        intval($this->getWidth() * $scale),
        intval($this->getHeight() * $scale)
    );
    return $this;
  }

  public function resizeStretch($width, $height)
  {
    return $this->resize($width, $height, self::RESIZE_STRETCH);
  }

  public function resizeCenter($width, $height)
  {
    return $this->resize($width, $height, self::RESIZE_CENTER);
  }

  public function resizeBorder($width, $height)
  {
    return $this->resize($width, $height, self::RESIZE_AND_FILL);
  }

  /**
   * Resizes the image.
   *
   * With the optional $mode parameter the resizing mode
   * can be adjust.
   *  RESIZE_STRETCH    : stretch
   *  RESIZE_CENTER     : cut to fit (The image will cut to the given
   *                      dimensions from the center)
   *  RESIZE_AND_FILL   : resize and fill (Add borders to fit the given
   *                      dimensions)
   *
   * @param int $width
   * @param int $height
   * @param int $mode
   *
   * @return $this
   */
  protected function resize($width, $height, $mode)
  {
    $newWidth = intval($width);
    $newHeight = intval($height);
    $this->addImageOperation(array(
      'resize', $newWidth, $newHeight, $mode
    ));
    $this->setDimensions($newWidth, $newHeight);
    return $this;
  }

  /**
   * Crops the Image object.
   *
   * @param int $top
   * @param int $left
   * @param int $width
   * @param int $height
   *
   * @return $this
   */
  public function crop($top, $left, $width, $height)
  {
    $top = intval($top);
    $left = intval($left);
    $width = intval($width);
    $height = intval($height);
    $this->addImageOperation(array(
      'crop', $top, $left, $width, $height
    ));
    $this->setDimensions($width, $height);
    return $this;
  }

  /**
   * Sets the image quality (if supported by the mime type)
   *
   * @param int $quality
   *
   * @return $this
   */
  public function setQuality($quality)
  {
    $quality = intval($quality);
    if ($quality > 100) {
      $quality = 100;
    }
    if ($quality < 0) {
      $quality = 0;
    }
    $this->addImageOperation(array(
      'quality', $quality
    ));
    return $this;
  }

  /**
   * Sets the interlacing mode
   *
   * @param bool|null $interlaced
   *
   * @return $this
   */
  public function setInterlaced($interlaced)
  {
    $this->addImageOperation(array(
      'interlace', (bool)$interlaced
    ));
    return $this;
  }
  /**
   * reset the operation stack
   */
  protected function resetImageOperation()
  {
    $this->imageOperations = array();
  }

  /**
   * Adds the given operation to the operation stack
   *
   * @param array $operation
   */
  protected function addImageOperation(array $operation)
  {
    $this->imageOperations[] = $operation;
  }

  protected function initImageDimensions()
  {
    if (!is_null($this->width) && !is_null($this->height)) {
      return;
    }
    try {
      $this->setDimensions($this->getOriginalWidth(), $this->getOriginalHeight());
    } catch (\Exception $donothing) {
    }
  }

  protected function initOriginalImageDimensions()
  {
    if (!is_null($this->originalWidth) && !is_null($this->originalHeight)) {
      return;
    }
    $imageTool = $this->getImageTool();
    $dimensions = $imageTool->getDimensionFromFile($this->getFilePath());
    $this->originalWidth = $dimensions['width'];
    $this->originalHeight = $dimensions['height'];

  }

  protected function getFilePath()
  {
    return $this->mediaItem->getFilePath();
  }

  /**
   * @param int $width
   * @param int $height
   */
  protected function setDimensions($width, $height)
  {
    $this->width = intval($width);
    $this->height = intval($height);
  }

  /**
   * @return \Render\ImageToolFactory\ImageTool
   */
  protected function getImageTool()
  {
    return $this->getMediaContext()->getImageTool();
  }

  /**
   * @return \Render\MediaContext
   */
  protected function getMediaContext()
  {
    return $this->mediaContext;
  }

  /**
   * Calculates the scale factor so that the image fits into the new dimensions.
   *
   * @param int $newWidth
   * @param int $newHeight
   *
   * @return float
   */
  private function calculateScale($newWidth, $newHeight)
  {
    $curWidth = $this->getWidth();
    $xScale = floatval($newWidth) / floatval($curWidth);
    $curHeight = $this->getHeight();
    $yScale = floatval($newHeight) / floatval($curHeight);
    return floatval(min($xScale, $yScale));
  }

  /**
   * @return array
   */
  protected function getImageOperations()
  {
    return $this->imageOperations;
  }
}
