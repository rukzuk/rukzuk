<?php
namespace Dual\Media;

use \Dual\Render\RenderContext;

class Image
{
  const RESIZE_STRETCH = 0;
  const RESIZE_CENTER = 1;
  const RESIZE_AND_FILL = 2;
  const RESIZE_SCALE = 3;

  private $mediaContext = null;
  private $id;
  private $icon = false;
  private $imageOperations = array();
  private $width = 0;
  private $height = 0;
  private $mediaItem = null;

  public function __construct($id, $icon = false)
  {
    $this->mediaContext = RenderContext::getMediaContext();
    $this->id = $id;
    $this->icon = $icon;

    $this->mediaItem = $this->mediaContext->getMediaInfoStorage()->getItem($id);
    $this->initImageDimensions();

  }

  protected function initImageDimensions()
  {
    if ($this->icon) {
      $filePathImg = $this->mediaItem->getIconFilePath();
    } else {
      $filePathImg = $this->mediaItem->getFilePath();
    }

    if (empty($filePathImg)) {
      $this->setDimensions(0, 0);
      return;
    }

    try {
      $imageTool = $this->mediaContext->getImageTool();
      $dimensions = $imageTool->getDimensionFromFile($filePathImg);
      $this->setDimensions($dimensions['width'], $dimensions['height']);
    } catch (\Exception $donothing) {
    }
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

  public function getUrl()
  {
    try {
      if ($this->icon) {
        return $this->mediaContext->getMediaInfoStorage()->getPreviewUrl(
            $this->id,
            $this->imageOperations
        );
      } else {
        return $this->mediaContext->getMediaInfoStorage()->getImageUrl(
            $this->id,
            $this->imageOperations
        );
      }
    } catch (\Exception $_e) {
      return "";
    }
  }

  public function getDownloadUrl()
  {
    try {
      return $this->mediaContext->getMediaInfoStorage()->getDownloadUrl($this->id);
    } catch (\Exception $_e) {
      return "";
    }
  }

  public function getIconUrl($maxWidth = null, $maxHeight = null)
  {
    try {
      $op = $this->imageOperations;
      if (!is_null($maxWidth) || !is_null($maxHeight)) {
        $op[] = array('maxsize', (int)$maxWidth, (int)$maxHeight);
      }
      return $this->mediaContext->getMediaInfoStorage()->getPreviewUrl(
          $this->id,
          $op
      );
    } catch (\Exception $_e) {
      return "";
    }
  }

  public function getImage($icon = false)
  {
    return new Image($this->id, $icon);
  }

  public function reset()
  {
    $this->imageOperations = array();
    $this->initImageDimensions();
  }

  public function isIcon()
  {
    return $this->icon;
  }

  public function getWidth()
  {
    $imageSize = $this->getSize();
    return (isset($imageSize['width']) ? $imageSize['width'] : 0);
  }

  public function getSize()
  {
    return array('width' => $this->width, 'height' => $this->height);
  }

  public function getHeight()
  {
    $imageSize = $this->getSize();
    return (isset($imageSize['height']) ? $imageSize['height'] : 0);
  }

  /**
   * Bildgroesse aendern in die Chain aufnehmen
   *
   * @param int $maxWidth
   * @param int $maxHeight
   * @param boolean $border
   * @param boolean $proportions
   * @return boolean
   */
  public function resize($maxWidth = 0, $maxHeight = 0, $border = false, $proportions = true)
  {
    $maxWidth = (int)$maxWidth;
    $maxHeight = (int)$maxHeight;

    // no change
    if ($maxWidth == 0 && $maxHeight == 0) {
      return $this;
    }

    // scale
    if ($maxWidth <= 0 || $maxHeight <= 0) {
      // convert 0 to NULL
      if ($maxWidth == 0) {
        $maxWidth = null;
      }
      if ($maxHeight == 0) {
        $maxHeight = null;
      }
      return $this->resizeScale($maxWidth, $maxHeight);
    }

    if ($border) {
      return $this->resizeImpl($maxWidth, $maxHeight, self::RESIZE_AND_FILL);
    }

    if ($proportions) {
      return $this->resizeImpl($maxWidth, $maxHeight, self::RESIZE_CENTER);
    }

    return $this->resizeImpl($maxWidth, $maxHeight, self::RESIZE_STRETCH);
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

  protected function addImageOperation(array $operation)
  {
    $this->imageOperations[] = $operation;
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
    $curHeight = $this->getHeight();

    if ($curWidth == 0 || $curHeight == 0) {
      return 1;
    }

    $xScale = floatval($newWidth) / floatval($curWidth);
    $yScale = floatval($newHeight) / floatval($curHeight);

    return floatval(min($xScale, $yScale));
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
  protected function resizeImpl($width, $height, $mode)
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
   * Bild zuschneiden in die Chain aufnehmen
   *
   * @param integer $x
   * @param integer $y
   * @param $width
   * @param $height
   * @internal param int $newWidth
   * @internal param int $newHeight
   * @return boolean
   */
  public function crop($x, $y, $width, $height)
  {
    $x = intval($x);
    $y = intval($y);
    $width = intval($width);
    $height = intval($height);
    $this->addImageOperation(array(
      'crop', $x, $y, $width, $height
    ));
    $this->setDimensions($width, $height);
    return $this;
  }

  /**
   * Bild zuschneiden in die Chain aufnehmen
   *
   * @param int $maxWidth
   * @param int $maxHeight
   * @return boolean
   */
  public function maxSize($maxWidth = 0, $maxHeight = 0)
  {
    $maxWidth = intval($maxWidth);
    $maxHeight = intval($maxHeight);

    // add to operation
    $this->addImageOperation(array('maxsize', $maxWidth, $maxHeight));

    // Update internal size
    if (empty($maxWidth)) {
      $maxWidth = PHP_INT_MAX;
    }

    if (empty($maxHeight)) {
      $maxHeight = PHP_INT_MAX;
    }

    $scale = $this->calculateScale($maxWidth, $maxHeight);
    $this->setDimensions(intval($this->width * $scale), intval($this->height * $scale));
  }

  /**
   * Bild-Qualitaet uebernehmen
   *
   * @param integer $quality
   * @return array  Aktion-Array
   */
  public function quality($quality = null)
  {
    $this->addImageOperation(array(
      'quality', intval($quality)
    ));
    return $this;
  }

  /**
   * Interlace-Verfahren ein/ausschalten uebernehmen
   *
   * @param boolean $interlace
   * @return array  Aktion-Array
   */
  public function interlace($interlace = null)
  {
    $this->addImageOperation(array(
      'interlace', (bool)$interlace
    ));
    return $this;
  }
}
