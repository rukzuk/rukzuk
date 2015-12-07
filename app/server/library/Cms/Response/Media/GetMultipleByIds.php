<?php
namespace Cms\Response\Media;

use Cms\Response\Media as MediaResponse;

/**
 * Response Ergebnis fuer GetMultipleByIds
 *
 * @package      Cms
 * @subpackage   Response
 */

class GetMultipleByIds
{
  /**
   * @var array
   */
  public $media = array();

  /**
   * @param array $medias
   */
  public function __construct(
      array $expectedMediaIds = array(),
      array $medias = array()
  ) {
    $this->setMedia($expectedMediaIds, $medias);
  }
  
  /**
   * @return array
   */
  public function getMedia()
  {
    return $this->media;
  }
  
  /**
   * @param array $medias
   */
  protected function setMedia(array $expectedMediaIds, array $medias)
  {
    foreach ($medias as $media) {
      $this->media[$media->getId()] = new MediaResponse($media);
      if (($key = array_search($media->getId(), $expectedMediaIds)) !== false) {
        unset($expectedMediaIds[$key]);
      }
    }
    
    foreach ($expectedMediaIds as $expectedMediaId) {
      $this->media[$expectedMediaId] = false;
    }
  }
}
