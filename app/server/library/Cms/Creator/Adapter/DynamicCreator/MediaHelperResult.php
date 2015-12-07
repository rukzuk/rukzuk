<?php


namespace Cms\Creator\Adapter\DynamicCreator;

class MediaHelperResult
{
  /**
   * @var array
   */
  private $mediaIds;
  /**
   * @var array
   */
  private $albumsIds;

  /**
   * @param array $mediaIds
   * @param array $albumsIds
   */
  public function __construct(array $mediaIds = array(), array $albumsIds = array())
  {

    $this->mediaIds = $mediaIds;
    $this->albumsIds = $albumsIds;
  }

  /**
   * @return array
   */
  public function getMediaIds()
  {
    return $this->mediaIds;
  }

  /**
   * @return array
   */
  public function getAlbumsIds()
  {
    return $this->albumsIds;
  }
}
