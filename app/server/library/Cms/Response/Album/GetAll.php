<?php
namespace Cms\Response\Album;

use Cms\Response;
use Cms\Response\IsResponseData;

/**
 * Get all response
 *
 * @package      Cms
 * @subpackage   Response
 */
class GetAll implements IsResponseData
{
  /**
   * @var array
   */
  public $albums;
  /**
   * @param array $allAlbums
   */
  public function __construct(array $albums = array())
  {
    $this->albums = array();
    $this->setAlbums($albums);
  }
  
  /**
   * @param array $albums
   */
  protected function setAlbums(array $albums)
  {
    foreach ($albums as $album) {
      $this->albums[] = new Response\Album($album);
    }
  }
  
  /**
   * @return array
   */
  public function getAlbums()
  {
    return $this->albums;
  }
}
