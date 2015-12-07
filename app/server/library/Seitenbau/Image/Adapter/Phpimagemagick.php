<?php
namespace Seitenbau\Image\Adapter;

use Seitenbau\Image\Base as ImageBase;

/**
 * Image base
 *
 * @see Seitenbau\Image\Base
 */
require_once __DIR__.'/../Base.php';

/**
 * Bildverarbeitung mit der ImageMagick Implementierung
 *
 * @package      Seitenbau
 * @subpackage   Image
 */

class PhpImageMagick extends ImageBase
{
  private $image = null;
  private $_loaded = false;
  
  protected function init()
  {
    parent::init();
    \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, 128);
    \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_MAP, 128);
  }

  /**
   * @param string $filePath
   *
   * @throws \Exception
   * @return array
   */
  public function getDimensionFromFile($filePath)
  {
    if (!file_exists($filePath)) {
      throw new \Exception('file not exists');
    }
    $orgImage = $this->createImagick();
    if (!$orgImage) {
      throw new \Exception('error at open file');
    }
    $orgImage->pingImage($filePath);
    $size = $orgImage->getImageGeometry();
    return array(
      'width'     => $size['width'],
      'height'    => $size['height'],
    );
  }

  /**
   * Bild oeffnen
   */
  public function load()
  {
    // Evtl. geoeffnetes Bild schliessen
    $this->close();
  
    // Bild laden
    $this->image = $this->createImagick($this->imageFile);
    if (!$this->image) {
      return false;
    }
    
    $this->_loaded = true;
    return true;
  }

  /**
   * Bild schliessen
   */
  public function close()
  {
    // Bild schliessen
    if ($this->image) {
      $this->image->clear();
      $this->image->destroy();
    }

    // Bild nicht mehr geladen
    $this->_loaded = false;
    $this->image = null;
  }

  /**
   * Bild speichern
   */
  public function save($file, $imageType = null)
  {
    // Bild vorhanden
    if (!$this->prepareImage($imageType)) {
      return false;
    }

    // Bild speichern
    return $this->image->writeImages($file, true);
  }


  /**
   * Bild direkt ausgeben
   */
  public function send($imageType = null)
  {
    // Bild vorhanden
    if (!$this->prepareImage($imageType)) {
      return false;
    }

    // Bild ausgeben
    echo $this->image;
    return true;
  }

  /**
   * @param null $imageType
   *
   * @return bool
   */
  protected function prepareImage($imageType = null)
  {
    // Bild vorhanden
    if (!$this->_loaded || !$this->image) {
      return false;
    }

    // Bildformat setzen
    if (isset($imageType)) {
      switch($imageType) {
        case self::TYPE_GIF:
          $this->image->setImageFormat('gif');
              break;
        case self::TYPE_JPG:
          $this->image->setImageFormat('jpg');
              break;
        case self::TYPE_PNG:
          $this->image->setImageFormat('png');
              break;
        case self::TYPE_WBMP:
          $this->image->setImageFormat('wbmp');
              break;
        default:
          // Typ nicht bekannt
              return false;
          break;
      }
    }

    // Qualitaet bei JPEG setzen
    if (isset($this->quality) && $this->image->getImageFormat() == 'jpeg') {
      $this->image->setCompression(\Imagick::COMPRESSION_JPEG);
      $this->image->setCompressionQuality($this->quality);
    }
  }

  /**
   * Bildgroesse zurueckgeben
   */
  public function getImageSize()
  {
    $imageInfo = $this->getImageInfo();
    return array(
      'width'   => $imageInfo['width'],
      'height'  => $imageInfo['height'],
    );
  }

  /**
   * Aktuelle Bildgroesse zurueckgeben
   */
  public function getCurImageSize()
  {
    // Bild vorhanden
    if ($this->_loaded && $this->image) {
    // Aktuelle Groesse aus dem Bildhandle ermitteln
      $size = $this->image->getImageGeometry();
      return array(
          'width'   => $size['width']
        , 'height'  => $size['height'] );
    }

    // Fallback: Aktuelle ueber die Datei ermitteln
    return $this->getImageSize();
  }

  /**
   * Bildinformationen zurueckgeben
   */
  public function getImageInfo()
  {
    $info = array(
      'width'   => null,
      'height'  => null,
      'type'    => null,
    );
    try {
      if (file_exists($this->imageFile)) {
        $orgImage = $this->createImagick();
        if ($orgImage) {
          $orgImage->pingImage($this->imageFile);
          $size = $orgImage->getImageGeometry();
          $info['width'] = $size['width'];
          $info['height'] = $size['height'];
          $identify = $orgImage->identifyImage();
          $info['type'] = $this->getTypeFromImagickType($identify['compression']);
        }
      }
    } catch (\Exception $doNothing) {
    }
    
    return $info;
  }

  /**
   * Bildgroesse aendern
   */
  public function resize($action)
  {
    // Bild ggf. laden
    if (!$this->_loaded && !isset($this->image)) {
      if (!$this->load()) {
        // Fehler
        return false;
      }
    }

    // Aktuelle Groesse ermitteln
    $size = $this->image->getImageGeometry();
    $w = $size['width'];
    $h = $size['height'];

    // Werte aus der 'action' uebernehmen
    $newWidth  = intval($action['width']);
    $newHeight = intval($action['height']);

    // init Variable
    $src_x = 0;
    $src_y = 0;
    $src_w = $w;
    $src_h = $h;
    $dst_x = 0;
    $dst_y = 0;
    $dst_w = $newWidth;
    $dst_h = $newHeight;

    // resize
    if (isset($action['proportions']) && $action['proportions']) {
    // Proportionen beibehalten (resize+crop)
      $ratio_w = $newWidth/$w;
      $ratio_h = $newHeight/$h;
      $ratio = max($ratio_w, $ratio_h);

      if ($ratio > 0) {
      // Raender beachten
        if (isset($action['border']) && $action['border']) {
        // Kleinstes Verhaeltnis verwenden
          $ratio = min($ratio_w, $ratio_h);

          // Raender Links+Rechts beachten
          if ($ratio_w != $ratio) {
            $dst_w = $w * $ratio;
            $dst_x = ($newWidth - $dst_w) / 2;
          } // Raender Open+Unten beachten
          else {
            $dst_h = $h * $ratio;
            $dst_y = ($newHeight - $dst_h) / 2;
          }
        } // Mitte ausschneiden
        else {
          // Links+Rechts abschneiden
          if ($ratio_w != $ratio) {
            $src_w = ceil($newWidth / $ratio);
            $src_x = intval(($w - $src_w) / 2);
          } // Open+Unten abschneiden
          else {
            $src_h = ceil($newHeight / $ratio);
            $src_y = intval(($h - $src_h) / 2);
          }
        }
      }
    }
    
    // Werte korrigieren
    $src_x = ($src_x < 0 ? 0 : intval($src_x));
    $src_y = ($src_y < 0 ? 0 : intval($src_y));
    $src_w = intval($src_w);
    $src_h = intval($src_h);
    $dst_x = ($dst_x < 0 ? 0 : intval($dst_x));
    $dst_y = ($dst_y < 0 ? 0 : intval($dst_y));
    $dst_w = intval($dst_w);
    $dst_h = intval($dst_h);

    // Evtl. zuerst Bildbereich ausschneiden
    if ($src_x > 0 || $src_y > 0 || $src_w != $w || $src_h != $h) {
      try {
        $this->image = $this->image->coalesceImages();
        foreach ($this->image as $frame) {
          $frame->cropImage($src_w, $src_h, $src_x, $src_y);
          $frame->setImagePage($src_w, $src_h, 0, 0);
        }
        $this->image = $this->image->coalesceImages();
      } catch (ImagickException $e) {
        // Fehler
        return false;
      }
    }
    
    // Groesse des Bildes aendern
    try {
      foreach ($this->image as $frame) {
        $frame->scaleImage($dst_w, $dst_h);
      }
    } catch (ImagickException $e) {
    // Fehler
      return false;
    }


    // Evtl. Raender erstellen
    if ($dst_x > 0 || $dst_y > 0) {
      try {
        $this->image = $this->image->coalesceImages();
        foreach ($this->image as $frame) {
          $frame->setImagePage($newWidth, $newHeight, $dst_x, $dst_y);
        }
        $this->image = $this->image->coalesceImages();
      } catch (ImagickException $e) {
        // Fehler
        return false;
      }
    }

    // Fehler
    return false;
  }

  /**
   * Bildauschnitt waehlen
   */
  public function crop($action)
  {
    // Bild ggf. laden
    if (!$this->_loaded && !isset($this->image)) {
      if (!$this->load()) {
        // Fehler
        return false;
      }
    }

    // Werte aus der 'action' uebernehmen
    $src_x = intval($action['x']);
    $src_y = intval($action['y']);
    $newWidth = intval($action['width']);
    $newHight = intval($action['height']);

    // Bereich ausschneiden
    try {
      $this->image = $this->image->coalesceImages();
      foreach ($this->image as $frame) {
        $frame->cropImage($newWidth, $newHight, $src_x, $src_y);
        $frame->setImagePage($newWidth, $newHight, 0, 0);
      }
      $this->image = $this->image->coalesceImages();
    } catch (ImagickException $e) {
    // Fehler
      return false;
    }
 
    // Bildausschnitt erfolgreich ermittelt
    return true;
  }

  protected function createImagick($file = null)
  {
    $image = new \Imagick();
    if (!empty($file)) {
      $image->readImage($file);
    }
    return $image;
  }
  
  private function getTypeFromImagickType($imagickType)
  {
    switch($imagickType) {
      case 'JPEG':
            return self::TYPE_JPG;
        break;
      case 'GIF':
            return self::TYPE_GIF;
        break;
      case 'PNG':
            return self::TYPE_PNG;
        break;
      case 'BMP':
            return self::TYPE_WBMP;
        break;
    }
    return null;
  }
}
