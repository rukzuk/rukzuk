<?php
namespace Cms\Response\Media;

use Cms\Response\Media as MediaResponse;

/**
 * Response Ergebnis fuer GetByFilter
 *
 * @package      Cms
 * @subpackage   Response
 */

class GetByFilter
{
  /**
   * @var array
   */
  public $media = array();

  /**
   * @var integer
   */
  public $total = 0;

  /**
   * @param array $media
   * @param array $mediaUnlimited Total media without applied Sql limit
   */
  public function __construct($media = array(), $mediaUnlimited = array())
  {
    $this->setMedia($media);
    $this->setTotal(count($mediaUnlimited));
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
  protected function setMedia(array $media)
  {
    foreach ($media as $media) {
      $this->media[] = new MediaResponse($media);
    }
  }
  
  /**
   * @param integer $count
   */
  public function setTotal($count)
  {
    $this->total = $count;
  }
  
  /**
   * @return integer
   */
  public function getTotal()
  {
    return $this->total;
  }
}
