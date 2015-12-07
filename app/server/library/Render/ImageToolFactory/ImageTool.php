<?php


namespace Render\ImageToolFactory;

use Seitenbau\Image\Image;

class ImageTool
{
  /**
   * @var \Seitenbau\Image\Image
   */
  private $imageAdapter;

  /**
   * @param Image $imageAdapter
   */
  public function __construct(Image $imageAdapter)
  {
    $this->imageAdapter = $imageAdapter;
  }

  /**
   * Returns TRUE if file is an image; FALSE otherwise
   *
   * @param string $filePath
   *
   * @return bool
   */
  public function isImageFile($filePath)
  {
    return $this->imageAdapter->isImageFile($filePath);
  }

  /**
   * @param string $filePath
   *
   * @return array
   */
  public function getDimensionFromFile($filePath)
  {
    return $this->imageAdapter->getDimensionFromFile($filePath);
  }

  /**
   * Opens the image given by $filePath
   *
   * @param string $filePath
   */
  public function open($filePath)
  {
    $this->imageAdapter->close();
    $this->imageAdapter->setFile($filePath);
    $this->imageAdapter->load();
  }

  /**
   * Close the loaded image
   */
  public function close()
  {
    $this->imageAdapter->close();
  }

  /**
   * Write images to file
   *
   * @param string $filePath
   *
   * @return bool
   */
  public function save($filePath)
  {
    return (bool)$this->imageAdapter->save($filePath);
  }

  /**
   * Output image to browser
   */
  public function send()
  {
    $this->imageAdapter->send();
  }

  /**
   * @return array
   */
  public function getDimension()
  {
    $dimension = $this->imageAdapter->getCurImageSize();
    return array(
      'width'   => $dimension['width'],
      'height'  => $dimension['height'],
    );
  }

  /**
   * Modify the image by given operations
   *
   * @param array $operations
   */
  public function modify(array $operations = array())
  {
    foreach ($operations as $operation) {
      switch ($operation[0]) {
        case "resize":
          $this->resize($operation);
              break;
        case "crop":
          $this->crop($operation);
              break;
        case "maxsize":
          $this->maxsize($operation);
              break;
        case "quality":
          $this->setQuality($operation);
              break;
        case "interlace":
          $this->setInterlace($operation);
              break;
      }
    }
  }

  /**
   * @param array $operation
   */
  protected function resize(array $operation)
  {
    switch ($operation[3]) {
      case 1:
            return $this->resizeCenter($operation);
        break;
      case 2:
            return $this->resizeFill($operation);
        break;
      case 3:
            return $this->resizeScale($operation);
        break;
      default:
            return $this->resizeStretch($operation);
        break;
    }
  }

  /**
   * @param array $operation
   */
  protected function resizeCenter(array $operation)
  {
    $this->imageAdapter->resize(array(
      'width' => $operation[1], 'height' => $operation[2],
      'proportions' => true, 'border' => false,
    ));
  }

  /**
   * @param array $operation
   */
  protected function resizeFill(array $operation)
  {
    $this->imageAdapter->resize(array(
      'width' => $operation[1], 'height' => $operation[2],
      'proportions' => true, 'border' => true,
    ));
  }

  /**
   * @param array $operation
   */
  protected function resizeScale(array $operation)
  {
    $maxWidth = $operation[1];
    $maxHeight = $operation[2];
    if (empty($maxWidth) && empty($maxHeight)) {
      return;
    }
    if (empty($maxWidth)) {
      $maxWidth = PHP_INT_MAX;
    }
    if (empty($maxHeight)) {
      $maxHeight = PHP_INT_MAX;
    }
    $size = $this->imageAdapter->getCurImageSize();
    if ($maxWidth >= $size['width'] && $maxHeight >= $size['height']) {
      return;
    }
    $scale = $this->calculateScale(
        $size['width'],
        $size['height'],
        $maxWidth,
        $maxHeight
    );
    $maxWidth = intval($size['width'] * $scale);
    $maxHeight = intval($size['height'] * $scale);
    $this->imageAdapter->resize(array(
      'width' => $maxWidth, 'height' => $maxHeight,
      'proportions' => true, 'border' => false,
    ));
  }

  /**
   * @param array $operation
   */
  protected function resizeStretch(array $operation)
  {
    $this->imageAdapter->resize(array(
      'width' => $operation[1], 'height' => $operation[2],
      'proportions' => false, 'border' => false,
    ));
  }

  /**
   * @param array $operation
   */
  protected function crop(array $operation)
  {
    $this->imageAdapter->crop(array(
      'x' => $operation[1], 'y' => $operation[2],
      'width' => $operation[3], 'height' => $operation[4],
    ));
  }

  /**
   * @param array $operation
   */
  protected function maxsize(array $operation)
  {
    $maxWidth = $operation[1];
    $maxHeight = $operation[2];
    if (empty($maxWidth) && empty($maxHeight)) {
      return;
    }
    if (empty($maxWidth)) {
      $maxWidth = PHP_INT_MAX;
    }
    if (empty($maxHeight)) {
      $maxHeight = PHP_INT_MAX;
    }
    $size = $this->imageAdapter->getCurImageSize();
    $scale = $this->calculateScale(
        $size['width'],
        $size['height'],
        $maxWidth,
        $maxHeight
    );
    $this->imageAdapter->resize(array(
      'width' => intval($size['width'] * $scale),
      'height' => intval($size['height'] * $scale),
      'proportions' => false, 'border' => false,
    ));
  }

  /**
   * @param array $operation
   */
  protected function setQuality(array $operation)
  {
    $this->imageAdapter->quality(array(
      'quality' => $operation[1],
    ));
  }

  /**
   * @param array $operation
   */
  protected function setInterlace(array $operation)
  {
    $this->imageAdapter->interlace(array(
      'interlace' => $operation[1],
    ));
  }

  /**
   * Calculates the scale factor so that the image fits into the new dimensions.
   *
   * @param int $curWidth
   * @param int $curHeight
   * @param int $newWidth
   * @param int $newHeight
   *
   * @return float
   */
  protected function calculateScale($curWidth, $curHeight, $newWidth, $newHeight)
  {
    $xScale = floatval($newWidth) / floatval($curWidth);
    $yScale = floatval($newHeight) / floatval($curHeight);
    return floatval(min($xScale, $yScale));
  }
}
