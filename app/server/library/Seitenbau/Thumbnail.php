<?php
namespace Seitenbau;

/**
 * Thumbnail Creator
 *
 * @package      Seitenbau
 */

class Thumbnail
{
  const FORMAT_JPG = 'jpg';
  const FORMAT_GIF = 'gif';
  const FORMAT_PNG = 'png';
  
  protected $sourceFilePath;

  protected $destinationFilePath;
  
  protected $width;
  
  protected $height;
  
  protected $format;
  
  protected $createFunction;
  
  protected $copyImage;

  protected $currentSize = array();
  
  public function __construct(
      $sourceFilePath,
      $destinationFilePath,
      $width,
      $height
  ) {
    $this->setSourceFilePath($sourceFilePath);
    $this->setDestinationFilePath($destinationFilePath);
    $this->setWidth($width);
    $this->setHeight($height);
    
    $this->fileWorkable();
    $this->setFormat();
    $this->createThumbnail();
    
    $this->resize($width, $height);
    $this->save($destinationFilePath);
  }
  
  public function resize($width, $height)
  {
    $newImage = imagecreatetruecolor($width, $height);
    imagecopyresampled($newImage, $this->copyImage, 0, 0, 0, 0, $width, $height, $this->currentSize['width'], $this->currentSize['height']);
    $this->copyImage = $newImage;
    $this->currentSize['width'] = $width;
    $this->currentSize['height'] = $height;
  }
  
  /**
   * Speichert das Thumbnail
   */
  public function save($destinationFilePath)
  {
    if (!is_writable(dirname($destinationFilePath))) {
      throw new \Exception('file "' . $destinationFilePath . '" not writable');
    }
    
    switch ($this->format)
        {
      case self::FORMAT_GIF:
          imagegif($this->copyImage, $destinationFilePath);
            break;
      case self::FORMAT_JPG:
          imagejpeg($this->copyImage, $destinationFilePath);
            break;
      case self::FORMAT_PNG:
          imagepng($this->copyImage, $destinationFilePath);
            break;
    }
  }
  
  public function getSourceFilePath()
  {
    return $this->sourceFilePath;
  }

  public function setSourceFilePath($sourceFilePath)
  {
    $this->sourceFilePath = $sourceFilePath;
  }

  public function getDestinationFilePath()
  {
    return $this->destinationFilePath;
  }

  public function setDestinationFilePath($destinationFilePath)
  {
    $this->destinationFilePath = $destinationFilePath;
  }

  public function getWidth()
  {
    return $this->width;
  }

  public function setWidth($width)
  {
    $this->width = $width;
  }

  public function getHeight()
  {
    return $this->height;
  }

  public function setHeight($height)
  {
    $this->height = $height;
  }
  
  
  /**
   * Pruefung, ob die Quell-Datei genutzt werden kann
   *
   * Kann die Datei nicht genutzt werden, so wird eine Exception geworfen
   */
  protected function fileWorkable()
  {
    if (!file_exists($this->getSourceFilePath())) {
      throw new \Exception(
          'Sourcefile "' . $this->getSourceFilePath() . '" not exists'
      );
    } elseif (!is_readable($this->getSourceFilePath())) {
      throw new \Exception(
          'Sourcefile "' . $this->getSourceFilePath() . '" not readable'
      );
    }
  }
  
  /**
   * Legt das Dateiformat fest
   */
  protected function setFormat()
  {
    $imageInfo = getimagesize($this->getSourceFilePath());
    switch ($imageInfo['mime'])
    {
      case 'image/gif':
          $this->format = self::FORMAT_GIF;
        $this->createFunction = 'imagecreatefromgif';
            break;
      case 'image/jpeg':
          $this->format = self::FORMAT_JPG;
        $this->createFunction = 'imagecreatefromjpeg';
            break;
      case 'image/png':
          $this->format = self::FORMAT_PNG;
        $this->createFunction = 'imagecreatefrompng';
            break;
      default:
            throw new \Exception(
                'File Format "' . $imageInfo['mime'] . '" not supported'
            );
    }
  }
  
  /**
   * Erstellt das Thumbnail
   */
  protected function createThumbnail()
  {
    if (!function_exists($this->createFunction)) {
      throw new \Exception('crate function "' . $this->createFunction . '" not exists');
    }
    
    $createFunction = $this->createFunction;
    $this->copyImage = $createFunction($this->getSourceFilePath());
    
    $this->currentSize = array(
      'width' => imagesx($this->copyImage),
      'height' => imagesy($this->copyImage)
    );
  }
}
