<?php


namespace Render\MediaCDNHelper;

class MediaRequest
{
  const TYPE_STREAM   = 'stream';
  const TYPE_DOWNLOAD = 'download';
  const TYPE_IMAGE    = 'image';
  const TYPE_ICON     = 'icon';
  const TYPE_PREVIEW  = 'preview';

  /**
   * @var string
   */
  private $mediaId;

  /**
   * @var string
   */
  private $cdnType;

  /**
   * @var int
   */
  private $date;

  /**
   * @var string
   */
  private $websiteId;

  /**
   * @var array
   */
  private $operations;


  /**
   * @param string      $mediaId
   * @param string      $cdnType
   * @param int         $date
   * @param null|string $websiteId
   * @param array       $operations
   */
  public function __construct(
      $mediaId,
      $cdnType,
      $date = 0,
      $websiteId = null,
      array $operations = array()
  ) {
    $this->mediaId = $mediaId;
    $this->cdnType = $cdnType;
    $this->date = $date;
    $this->websiteId = $websiteId;
    $this->operations = $operations;
  }

  /**
   * @return string
   */
  public function getMediaId()
  {
    return $this->mediaId;
  }

  /**
   * @return string
   */
  public function getCdnType()
  {
    return $this->cdnType;
  }

  /**
   * @return int
   */
  public function getDate()
  {
    return $this->date;
  }

  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @return array
   */
  public function getOperations()
  {
    return $this->operations;
  }
}
