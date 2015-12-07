<?php


namespace Cms\Creator\Adapter\DynamicCreator;

use Orm\Data\Media as DataMedia;
use Orm\Data\Album as DataAlbum;

class MediaHelper
{
  /**
   * @param mixed $value
   *
   * @return MediaHelperResult
   */
  public function findMediaAndAlbumIds($value)
  {
    if (is_array($value) || is_object($value)) {
      $value = json_encode($value);
    }
    if (!is_string($value) || empty($value)) {
      return array(array(), array());
    }
    return $this->findMediaAndAlbumIdsInString($value);
  }

  /**
   * @param string $content
   *
   * @return MediaHelperResult
   */
  protected function findMediaAndAlbumIdsInString($content)
  {
    $mediaIds = array();
    $albumIds = array();
    $regexpMedia = '(' . preg_quote(DataMedia::ID_PREFIX, '/') . '.*?' . preg_quote(DataMedia::ID_SUFFIX, '/') . ')';
    $regexpAlbum = '(' . preg_quote(DataAlbum::ID_PREFIX, '/') . '.*?' . preg_quote(DataAlbum::ID_SUFFIX, '/') . ')';
    if (preg_match_all('/' . $regexpMedia . '|' . $regexpAlbum . '/', $content, $aMatches, PREG_SET_ORDER)) {
      foreach ($aMatches as $nextItem) {
        if (!empty($nextItem[1])) {
          $mediaIds[] = $nextItem[0];
        } elseif (!empty($nextItem[2])) {
          $albumIds[] = $nextItem[0];
        }
      }
    }
    return new MediaHelperResult(array_unique($mediaIds), array_unique($albumIds));
  }
}
