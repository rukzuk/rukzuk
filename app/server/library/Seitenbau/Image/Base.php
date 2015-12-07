<?php
namespace Seitenbau\Image;

use Seitenbau\Image\Image as SBImage;
use Seitenbau\Image\ImageException;

/**
 * Image interface
 *
 * @see Seitenbau\Image\Base
 */
require_once __DIR__.'/Image.php';

/**
 * Image exception
 *
 * @see Seitenbau\Image\Base
 */
require_once __DIR__.'/ImageException.php';

/**
 * Bildverarbeitungs-Basis-Klasse
 *
 * @package      Seitenbau
 * @subpackage   Image
 */

abstract class Base implements SBImage
{
  // Speicher in Byte welcher als Reserve vorhanden bleiben soll (0,5MB = 524288)
  const MEMORY_BUFFER = 524288;

  const CHUNK_SIZE = 1048576;
  
  /**
   * Bilddatei
   * @var string
   */
  protected $imageFile = null;

  /**
   * Qualitaet des Bildes
   * @var integer
   */
  protected $quality = null;

  /**
   * Interlacing beim Speichern des Bildes anwenden
   * @var integer
   */
  protected $interlace = null;

  /**
   * Konfiguration
   * @var array
   */
  protected $config = array();

  /**
   * Temporaeres Verzeichnis
   * @var string
   */
  protected $tempDir = null;
  
  /**
   * Speicherlimit
   * @var integer
   */
  protected $memoryLimit = null;

  /**
   * @var string[]
   */
  protected $validFileExtensions = array('gif', 'png', 'jpg', 'jpeg');
  

  public function __construct(array $config = array())
  {
    $this->config = $config;
    if (isset($config['temp_dir'])) {
      $this->setTempDir($config['temp_dir']);
    }
    
    if (isset($config['memory_limit'])) {
      $this->setMemoryLimit($config['memory_limit']);
    } elseif (defined('CMS_MEMORY_LIMIT')) {
      $this->setMemoryLimit(CMS_MEMORY_LIMIT);
    }
    
    $this->init();
  }
  
  protected function init()
  {
    // do nothing
  }

  /**
   * Setzt das zu verwendende temporaere Verzeichnis
   *
   * @param string $tempDir  Das zu verwendende temporaere Verzeichnis
   */
  public function setTempDir($tempDir)
  {
    // temporaeres Verzeichnis uebernehmen
    $this->tempDir = $tempDir;
  }

  /**
   * Setzt das Speicherlimit
   *
   * @param int $tempDir  Speicherlimit in Bytes
   */
  public function setMemoryLimit($memoryLimit)
  {
    // Speicherlimit uebernehmen
    if ((int)$memoryLimit > 0) {
      $this->memoryLimit = $memoryLimit;
    }
  }

  /**
   * Entfernt das Speicherlimit
   */
  public function resetMemoryLimit()
  {
    $this->memoryLimit = null;
  }

  /**
   * Setzt die zu verwendende Bilddatei
   *
   * @param string $imageFile
   */
  public function setFile($imageFile)
  {
    // Datei vorhanden?
    if (file_exists($imageFile)) {
      $this->imageFile = $imageFile;
    } else {
      throw new ImageException('Image-File not exists: '.$imageFile);
    }
  }

  /**
   * Setzt die Qualitaet des Bildes
   *
   * @param integer $quality  Qualitaet des Bildes (min. 0, max. 100)
   */
  public function quality($action)
  {
    // Qualitaetsangabe uebernehmen
    $this->setQuality((isset($action['quality']) ? $action['quality'] : null));
  }

  /**
   * Setzt die Qualitaet des Bildes
   *
   * @param integer $quality  Qualitaet des Bildes (min. 0, max. 100)
   */
  public function setQuality($quality = null)
  {
    // Qualitaetsangabe bereinigen
    if (isset($quality)) {
      $quality = (int)$quality;
      if ($quality < 1) {
        $quality = 1;
      }
      if ($quality > 100) {
        $quality = 100;
      }
    }

    // Qualitaetsangabe uebernehmen
    $this->quality = $quality;
  }

  /**
   * Interlace-Verfahren ein/ausschalten
   *
   * @param array $action       Key 'interlace': Interlace-Verfahren ein/ausschalten
   */
  public function interlace($action)
  {
    $this->setInterlace((isset($action['interlace']) ? $action['interlace'] : true));
  }

  /**
   * Interlace-Verfahren ein/ausschalten
   *
   * @param boolean $interlace  Interlace-Verfahren ein/ausschalten
   */
  public function setInterlace($interlace = null)
  {
    // Interlace-Verfahren
    if (isset($interlace)) {
      $interlace = ($interlace ? true : false);
    }
    
    $this->interlace = $interlace;
  }

  /**
   * @param string $filePath
   *
   * @return bool
   */
  public function isImageFile($filePath)
  {
    $fileExtension = $this->getFileExtension(basename($filePath));
    return in_array($fileExtension, $this->validFileExtensions);
  }

  /**
   * @param string $fileName
   *
   * @return string
   */
  protected function getFileExtension($fileName)
  {
    return strtolower(substr(strrchr($fileName, '.'), 1));
  }
}
