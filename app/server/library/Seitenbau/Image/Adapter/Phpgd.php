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
 * Bildverarbeitung mit der PHP GD Implementierung
 *
 * @package      Seitenbau
 * @subpackage   Image
 */

class PhpGd extends ImageBase
{
  const TYPE_GD2 = 'gd2';
  
  private $image = null;
  private $_loaded = false;
  private $imageType = null;
  
  protected $isModified = false;

  protected function init()
  {
    parent::init();
    $this->isModified = false;
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
    $imageInfo = getimagesize($filePath);
    return array(
      'width'     => $imageInfo[0],
      'height'    => $imageInfo[1],
    );
  }

  /**
   * Bild oeffnen
   */
  public function load()
  {
    // Evtl. geoeffnetes Bild schliessen
    $this->close();
    
    // Bildinformationen ermitteln
    $imageInfo = $this->getImageInfo();
    $this->imageType = $imageInfo['gdType'];
     
    // Je nach Bildtyp das Bild oeffnen
    switch ($imageInfo['gdType'])
    {
      case IMAGETYPE_JPEG:
        if (imagetypes() & IMG_JPG && function_exists('imagecreatefromjpeg')) {
          // Typ setzen
          $this->imageType = self::TYPE_JPG;
          // JPG oeffnen
          $this->image = imagecreatefromjpeg($this->imageFile) ;
        }
            break;
      case IMAGETYPE_GIF:
        if (imagetypes() & IMG_GIF && function_exists('imagecreatefromgif')) {
          // Typ setzen
          $this->imageType = self::TYPE_GIF;
          // GIF oeffnen
          $this->image = imagecreatefromgif($this->imageFile) ;
        }
            break;
      case IMAGETYPE_PNG:
        if (imagetypes() & IMG_PNG && function_exists('imagecreatefrompng')) {
          // Typ setzen
          $this->imageType = self::TYPE_PNG;
          // PNG oeffnen
          $this->image = imagecreatefrompng($this->imageFile) ;
          imagealphablending($this->image, true);
          imagesavealpha($this->image, true);
        }
            break;
      case IMAGETYPE_WBMP:
        if (imagetypes() & IMG_WBMP && function_exists('imagecreatefromwbmp')) {
          // Typ setzen
          $this->imageType = self::TYPE_WBMP;
          // WBMP oeffnen
          $this->image = imagecreatefromwbmp($this->imageFile) ;
        }
            break;
      default:
          // Fehler! Bild-Type nicht unterstuetzt
            return false;
    }

    // Bild geoeffnet?
    if ($this->image) {
      $this->_loaded = true;
      return true;
    }

    // Bild nicht geladen
    return false;
  }
  
  /**
   * Bild oeffnen
   */
  public function loadFromGd2()
  {
    // Evtl. geoeffnetes Bild schliessen
    $this->close();

    // Bildtype setzen
    $this->imageType = self::TYPE_GD2;
      
    // GD2-Bild oeffnen
    $this->image = imagecreatefromgd2($this->imageFile) ;

    // Bild geoeffnet?
    if ($this->image) {
      $this->_loaded = true;
      return true;
    }

    // Bild nicht geladen
    return false;
  }

  /**
   * Liefert die Funktion zum Speichern eines Bildes
   */
  protected function getSaveFunction($imageType)
  {
    // init
    $saveFunctionName = null;
    $saveFunction     = null;
    
    // Je nach Bildtyp die Speichernfunktion ermitteln
    switch ($imageType)
    {
      case self::TYPE_JPG:
          $saveFunctionName = 'imagejpeg';
          $saveFunction = function ($image, $attributes) use ($saveFunctionName) {
            if (isset($attributes['interlace']) && !$attributes['interlace']) {
              imageinterlace($image, false);
            } else {
              imageinterlace($image, true);
            }
            
            if (isset($attributes['quality'])) {
              return call_user_func(
                  $saveFunctionName,
                  $image,
                  $attributes['file'],
                  $attributes['quality']
              );
            } else {
              return call_user_func(
                  $saveFunctionName,
                  $image,
                  $attributes['file']
              );
            }
          };
            break;
      case self::TYPE_GIF:
          $saveFunctionName = 'imagegif';
            break;
      case self::TYPE_PNG:
          $saveFunctionName = 'imagepng';
            break;
      case self::TYPE_WBMP:
          $saveFunctionName = 'imagewbmp';
            break;
      case self::TYPE_GD2:
          $saveFunctionName = 'imagegd2';
            break;
      default:
        // Fehler! Bild-Type nicht unterstuetzt
            return null;
    }
    
    // Basis-Speichernfunktion vorhanden?
    if (!function_exists($saveFunctionName)) {
      return null;
    }
    
    // Default Speicher-Funktion
    if (!isset($saveFunction)) {
      $saveFunction = function ($image, $attributes) use ($saveFunctionName) {
        return call_user_func($saveFunctionName, $image, $attributes['file']);
      };
    }
    
    // Speichernfunktion zurueckgeben
    return $saveFunction;
  }

  /**
   * Liefert die Funktion zum Speichern eines Bildes
   */
  protected function canSaveAsOriginalFile($imageType)
  {
    if ($imageType != $this->imageType || $this->isModified !== false
        || empty($this->imageFile) || !\is_file($this->imageFile)
    ) {
      return false;
    }
    
    switch ($imageType)
    {
      case self::TYPE_JPG:
        if ($this->interlace === false && is_null($this->quality)) {
          return true;
        }
            break;
      
      default:
            return true;
        break;
    }
    
    return false;
  }

  /**
   * Bild schliessen
   */
  public function close()
  {
    // Bild schliessen
    if ($this->image) {
      imagedestroy($this->image);
    }

    // Bild nicht mehr geladen
    $this->_loaded = false;
    $this->image = null;
    $this->imageType = null;
    $this->quality = null;
    $this->isModified = false;
  }

  /**
   * Bild speichern
   */
  public function save($file, $imageType = null)
  {
    // Bild vorhanden
    if ($this->_loaded && $this->image) {
    // Zu speicherndes Bildformat ermitteln
      if (!isset($imageType)) {
        $imageType = $this->imageType;
      }
      
      if ($this->canSaveAsOriginalFile($imageType)) {
        return copy($this->imageFile, $file);
      }
      
      // Bildspeicherfunktion ermitteln
      $saveFunction = $this->getSaveFunction($imageType);
      if (isset($saveFunction)) {
        $attributes = array(
          'file'        => $file,
          'quality'     => $this->quality,
          'interlace'   => $this->interlace,
        );
        return $saveFunction($this->image, $attributes);
      }
    }

    // Nichts zum Speichern
    return false;
  }

  /**
   * Bild direkt ausgeben
   */
  public function send($imageType = null)
  {
    // Bild vorhanden
    if ($this->_loaded && $this->image) {
    // Zu speicherndes Bildformat ermitteln
      if (!isset($imageType)) {
        $imageType = $this->imageType;
      }

      if ($this->canSaveAsOriginalFile($imageType)) {
        if (!is_file($this->imageFile) || !is_readable($this->imageFile)) {
          return false;
        }
        if (!($fd=fopen($this->imageFile, 'rb'))) {
          return false;
        }
        while ((!feof($fd)) && (!connection_aborted())) {
          print(fread($fd, self::CHUNK_SIZE));
        }
        fclose($fd);
        return true;
      }

      // Bildsausgabefunktion ermitteln
      $saveFunction = $this->getSaveFunction($imageType);
      if (isset($saveFunction)) {
        $attributes = array(
          'file'        => null,
          'quality'     => $this->quality,
          'interlace'   => $this->interlace,
        );
        return $saveFunction($this->image, $attributes);
      }
    }

    // Nichts zum Ausgeben
    return false;
  }

  /**
   * Bildgroesse zurueckgeben
   */
  public function getImageSize()
  {
    $imageInfo = $this->getImageInfo();
    return array(
        'width'   => $imageInfo['width']
      , 'height'  => $imageInfo['height']);
  }

  /**
   * Bildinformationen zurueckgeben
   */
  public function getImageInfo()
  {
    try {
      // Datei vorhanden?
      if (file_exists($this->imageFile)) {
        $imageInfo = getimagesize($this->imageFile);
        return array(
            'width'     => $imageInfo[0]
          , 'height'    => $imageInfo[1]
          , 'gdType'    => $imageInfo[2]
          , 'channels'  => (isset($imageInfo['channels']) ? $imageInfo['channels'] : null)
          , 'bits'      => (isset($imageInfo['bits']) ? $imageInfo['bits'] : null)
          , 'type'      => $this->getTypeFromGdType($imageInfo[2])
        );
      }
    } catch (\Exception $e) {
    // Fehler -> Bildinformationen konnte nicht ermittelt werden
    }
    
    // Fehler -> Datei nicht vorhanden oder es ist kein Bild
    return array(
        'width'   => null
      , 'height'  => null
      , 'type'    => null );
  }

  /**
   * Aktuelle Bildgroesse zurueckgeben
   */
  public function getCurImageSize()
  {
    // Bild vorhanden
    if ($this->_loaded && $this->image) {
    // Aktuelle Groesse des aus dem Bildhandle ermitteln
      return array(
          'width'   => imagesx($this->image)
        , 'height'  => imagesy($this->image) );
    }

    // Fallback: Aktuelle ueber die Datei ermitteln
    return $this->getImageSize();
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
    $w = imagesx($this->image);
    $h = imagesy($this->image);

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
    $src_x = ($src_x < 0 ? 0 : $src_x);
    $src_y = ($src_y < 0 ? 0 : $src_y);
    $dst_x = ($dst_x < 0 ? 0 : $dst_x);
    $dst_y = ($dst_y < 0 ? 0 : $dst_y);


    // Neues Bild erzeugen
    return $this->copyResampled(
        $newWidth,
        $newHeight,
        $dst_x,
        $dst_y,
        $src_x,
        $src_y,
        $dst_w,
        $dst_h,
        $src_w,
        $src_h
    );
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
    $newWidth = intval($action['width']);
    $newHeight = intval($action['height']);
    $src_x = intval($action['x']);
    $src_y = intval($action['y']);
    $src_w = $newWidth;
    $src_h = $newHeight;
    $dst_x = 0;
    $dst_y = 0;
    $dst_w = $src_w;
    $dst_h = $src_h;


    // Ausschneiden im negativen Bereich korrigieren
    if ($src_x < 0) {
      $src_x = abs($src_x);
      $dst_w -= $src_x;
      $dst_x += $src_x;
      $src_w -= $src_x;
      $src_x = 0;
    }
    if ($src_y < 0) {
      $src_y = abs($src_y);
      $dst_h -= $src_y;
      $dst_y += $src_y;
      $src_h -= $src_y;
      $src_y = 0;
    }
    // Ausschnitt groesser als das Originalbild korrigieren
    $srcSize = $this->getCurImageSize();
    if ($srcSize['width'] < ($src_x+$src_w)) {
      $wDiff = ($src_x+$src_w) - $srcSize['width'];
      $dst_w -= $wDiff;
      $src_w -= $wDiff;
    }
    if ($srcSize['height'] < ($src_y+$src_h)) {
      $hDiff = ($src_y+$src_h) - $srcSize['height'];
      $dst_h -= $hDiff;
      $src_h -= $hDiff;
    }

    // Neues Bild erzeugen
    return $this->copyResampled(
        $newWidth,
        $newHeight,
        $dst_x,
        $dst_y,
        $src_x,
        $src_y,
        $dst_w,
        $dst_h,
        $src_w,
        $src_h
    );
  }

  /**
   * Neues Bild erstellen
   */
  private function copyResampled($newWidth, $newHeight, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $bgColor = null)
  {
    // Breite und Hoehe angegeben?
    if ($newWidth <= 0 && $newHeight <= 0) {
    // Fehler
      return;
    }
    
    $cur_w = imagesx($this->image);
    $cur_h = imagesy($this->image);
    if ($src_x == 0 && $dst_x == 0 && $src_y == 0 && $dst_y == 0
        && $src_w == $cur_w && $dst_w == $cur_w && $src_h == $cur_h && $dst_h == $cur_h
    ) {
      return true;
    }

    // Genuegend Speicher vorhanden?
    $newImageInfo = $this->getImageInfo();
    $newImageInfo['width'] = $newWidth;
    $newImageInfo['height'] = $newHeight;
    $newImage = null;
    if ($this->enoughMemory($newImageInfo)) {
    // Ja -> Bild komplett verarbeiten
      $success = $this->doCopyResampled($this->image, $newImage, $this->imageType, $newWidth, $newHeight, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $bgColor);
      if ($success && $newImage) {
        imagedestroy($this->image);
        $this->image = $newImage;
        $this->isModified = true;
        return true;
      }
    } else {
      // Nein -> Bild in kleinen Stuecken verarbeiten
      $success = $this->copyResampledPart($this->image, $newImage, $newImageInfo, $newWidth, $newHeight, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $bgColor);
      if ($success && $newImage) {
        $this->image = $newImage;
        $this->isModified = true;
        return true;
      }
    }

    // Fehler
    return false;
  }

  /**
   * Neues Bild Stueckweise erstellen
   */
  private function copyResampledPart(&$image, &$newImage, $newImageInfo, $newWidth, $newHeight, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $bgColor = null)
  {
    // Breite und Hoehe angegeben?
    if ($newWidth <= 0 && $newHeight <= 0) {
    // Fehler
      return;
    }

    // temporaeres Verzeichnis vorhanden
    if (!empty($this->tempDir) && file_exists($this->tempDir)) {
    // Ja -> verwenden
      $tmpDir = $this->tempDir;
    } else {
      // Nein -> temporaeres Verzeichnis des Systems verwenden
      $tmpDir = sys_get_temp_dir();
    }

    // Maximial Breite der einzelnen Bereich ermitteln
    $partWidth = 50;
    $maxUsedMemory = ($this->getMemoryLimit() - memory_get_usage());
    if ($maxUsedMemory > 0) {
      do {
        $partWidth = $newImageInfo['width'] = (int)ceil($newImageInfo['width']/2);
        $requiredMemory = $this->getRequiredMemory($newImageInfo);
      } while ($partWidth > 50 && $maxUsedMemory < $requiredMemory);
    }
    if ($partWidth < 50) {
      $partWidth = 50;
    }
    
    // Bild in einzelne Bereich zerlegen
    $partImages = array();
    $partX = 0;
    while ($partX < $newWidth) {
      $partImage = null;
      $success = $this->doCopyResampled($image, $partImage, $this->imageType, $partWidth, $newHeight, $dst_x-$partX, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $bgColor);
      if ($success && $partImage) {
        $temp_file = tempnam($tmpDir, 'part_img_');
        if (!imagegd2($partImage, $temp_file)) {
          return false;
        }
        $partImages[] = array('x' => $partX, 'file' => $temp_file);
        imagedestroy($partImage);
        gc_collect_cycles();
      } else {
        return false;
      }
      
      $partX += $partWidth;
    }

    // Originalbild schliessen
    imagedestroy($image);
    gc_collect_cycles();
    $image = null;

    // neues Bild erstellen
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    if (!$newImage) {
      return false;
    }
    
    // Neues Bild aus den einzelne Bereich erstellen
    $first = true;
    foreach ($partImages as $nextPart) {
      $partImage = imagecreatefromgd2($nextPart['file']);
      unlink($nextPart['file']);
      if ($newImage && $partImage) {
        $this->doCopyResampled(
            $partImage,
            $newImage,
            $this->imageType,
            $newWidth,
            $newHeight,
            $nextPart['x'],
            0,
            0,
            0,
            $partWidth,
            $newHeight,
            $partWidth,
            $newHeight,
            $bgColor,
            $first
        );
      }
      if ($partImage) {
        imagedestroy($partImage);
      }
      $first = false;
    }
    
    // Status zurueckgeben
    return ($newImage ? true : false);
  }

  /**
   * Neues Bild erstellen
   */
  private function doCopyResampled(&$image, &$newImage, $imageType, $newWidth, $newHeight, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $bgColor = null, $setDefault = true)
  {
    // Breite und Hoehe angegeben?
    if ($newWidth <= 0 && $newHeight <= 0) {
    // Fehler
      return false;
    }
    
    // Bild anlegen
    if (!isset($newImage)) {
      $newImage = imagecreatetruecolor($newWidth, $newHeight);
    }

    // Bild vorhanden?
    if ($newImage) {
    // Defaultwerte uebernehmen
      if ($setDefault) {
      // Transparenz uebernehmen
        imagealphablending($newImage, true);
        $transparencyIndex = imagecolortransparent($image);
        $colorsTotal = imagecolorstotal($image);
        if ((($imageType == self::TYPE_PNG || $imageType == self::TYPE_GIF)
              && $transparencyIndex >= 0 )
          ||
            ($transparencyIndex >= 0 && $transparencyIndex < $colorsTotal)
          ) {
          imagepalettecopy($image, $newImage);
          imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparencyIndex);
          imagecolortransparent($newImage, $transparencyIndex);
          imagetruecolortopalette($newImage, true, 256);
        } // Transparenz fuer png oder gif uebernehmen
        elseif ($imageType == self::TYPE_PNG || $imageType == self::TYPE_GIF) {
          imagealphablending($newImage, false);
          imagesavealpha($newImage, true);
          $transparencyIndex = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
          //imagefill($newImage, 0, 0, $transparencyIndex);
          imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparencyIndex);
        } else {
          // Keine Transparenz -> Hintergrundfarbe verwenden

          // Hintergrundfarbe uebergeben?
          if (!(isset($bgColor) && is_array($bgColor) && count($bgColor) >= 3)) {
          // Default: Hintergrundfarbe Weis setzen
            $bgColor = array(255, 255, 255);
          }

          // Hintergrundfarbe setzen
          $bgcolor = imagecolorallocate($newImage, $bgColor[0], $bgColor[1], $bgColor[2]);
          imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $bgcolor);
        }
      }

      // Bild kopieren
      if (imagecopyresampled($newImage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)) {
      // Neues Bildes erfolgreich erstellt
        return true;
      }
      
      // Neues Bild loeschen
      imagedestroy($newImage);
    }

    // Fehler
    return false;
  }
  
  /**
   * Ist zum Anlegen des Bildes im Speicher genuegend Platz
   */
  private function enoughMemory($imageInfo)
  {
    // Ist ein Speicherlimit gesetzt
    if (isset($this->memoryLimit)) {
      $requiredMemory = $this->getRequiredMemory($imageInfo);
      $currentMemory = memory_get_usage();
      $memoryLimit = $this->getMemoryLimit();
      if (($memoryLimit-$currentMemory-$requiredMemory) < 0) {
      // Nicht genuegend Speicher vorhanden
        return false;
      }
    }
    
    // Genuegend Speicher vorhanden
    return true;
  }

  /**
   * Ermittelt den benoetigte Speicher fuer die angegebenen Bildinforamtioenn
   */
  private function getRequiredMemory($imageInfo)
  {
    $bits     = (empty($imageInfo['bits']) ? 8 : $imageInfo['bits']);
    $channels = (empty($imageInfo['channels']) ? 1 : $imageInfo['channels']);
    return (int)ceil($imageInfo['width'] * $imageInfo['height'] * ($bits / 8) * $channels * 2.5);
  }

  /**
   * Liefert den maximal zu verfuegungstehenden Speicher in Bytes
   */
  private function getMemoryLimit()
  {
    // Speicherlimit zurueckgeben, sonst Default 256MB (268435456 Byte)
    return ((isset($this->memoryLimit) && $this->memoryLimit > 0)
              ? $this->memoryLimit
              : 268435456);
  }
  
  private function getTypeFromGdType($gdType)
  {
    switch($gdType) {
      case IMAGETYPE_JPEG:
            return self::TYPE_JPG;
        break;
      case IMAGETYPE_GIF:
            return self::TYPE_GIF;
        break;
      case IMAGETYPE_PNG:
            return self::TYPE_PNG;
        break;
      case IMAGETYPE_WBMP:
            return self::TYPE_WBMP;
        break;
    }
    return null;
  }
}
