<?php
namespace Dual\Render;

use Dual\Media\Image;
use Dual\Media\Item;
use \Dual\Render\RenderContext;

/**
 * Render MediaDb
 *
 * @package      Dual
 * @subpackage   Render
 */

class MediaDb
{
  /**
   * Ermittelt die URL eines Bildes
   * @param string $id ID des Mediums
   * @param integer $width Breite des Bildes
   * @param integer $height Hoehe des Bildes
   * @return string
   */
  public static function getImageUrl($id, $width = null, $height = null)
  {
    // Url ermitteln
    $image = self::getImage($id);
    if (is_null($image)) {
      return '';
    }
    if (isset($width) || isset($height)) {
      $image->resize($width, $height);
    }
    return $image->getUrl();
  }

  /**
   * Liefert das Bild-Bearbeitungsobjekt eines Media-Objekts
   * @param string $id ID des Mediums
   * @param boolean $icon Icon des Mediums verwenden
   * @return  \Dual\Media\Image|null
   */
  public static function getImage($id, $icon = false)
  {
    try {
      return new Image($id, $icon);
    } catch (\Exception $e) {
      return null;
    }
  }

  /**
   * Ermittelt die URL eines Mediums
   * @param string $id ID des Mediums
   * @return string|void
   */
  public static function getUrl($id)
  {
    $mediaUrl = '';


    try {
      // Medium ermitteln
      $item = self::getItem($id);

      // Medium gefunden?
      if (isset($item)) {
        // URL ermitteln
        $mediaUrl = $item->getUrl();
      }
    } catch (\Exception $e) {
      // No Error Handling
    }
    // Url ermitteln
    return $mediaUrl;
  }

  /**
   * Liefert das Media-Item-Objekt der angegeben ID zurueck
   * @param string $id ID des Mediums
   * @return \Dual\Media\Item
   */
  public static function getItem($id)
  {
    try {
      return new Item($id);
    } catch (\Exception $e) {
      return null;
    }
  }

  /**
   * Ermittelt die URL eines Mediums zum direkten Download
   * @param string $id ID des Mediums
   * @return string|void
   */
  public static function getDownloadUrl($id)
  {
    $mediaUrl = '';

    try {
      // Medium ermitteln
      $item = self::getItem($id);

      // Medium gefunden?
      if (isset($item)) {
        // URL ermitteln
        $mediaUrl = $item->getDownloadUrl();
      }
    } catch (\Exception $e) {
      // No Error Handling
    }
    // Url ermitteln
    return $mediaUrl;
  }

  /**
   * Ermittelt alle Medien-Ids eines Albums
   * @param string $albumId Id des Aldums
   * @return array
   */
  public static function getIdsByAlbumId($albumId)
  {
    return RenderContext::getMediaIdsByAlbumId($albumId);
  }
}
