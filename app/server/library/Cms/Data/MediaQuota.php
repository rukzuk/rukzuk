<?php
namespace Cms\Data;

/**
 * @package      Cms
 * @subpackage   Data
 */
class MediaQuota
{
  /**
   * @var string
   */
  private $maxFileSize = 0;

  /**
   * @var string
   */
  private $maxSizePerWebsite = 0;

  /**
   * @param null|int $maxFileSize
   * @param null|int $maxSizePerWebsite
   */
  public function __construct(
      $maxFileSize = null,
      $maxSizePerWebsite = null
  ) {
    if (!is_null($maxFileSize)) {
      $this->maxFileSize = (int)$maxFileSize;
    }
    if (!is_null($maxSizePerWebsite)) {
      $this->maxSizePerWebsite = (int)$maxSizePerWebsite;
    }
  }

  /**
   * @return string
   */
  public function getMaxFileSize()
  {
    return $this->maxFileSize;
  }

  /**
   * @return string
   */
  public function getMaxSizePerWebsite()
  {
    return $this->maxSizePerWebsite;
  }

  /**
   * @param   integer $bytes
   * @param int       $precision
   *
   * @return  integer
   */
  public function convertByteToMiB($bytes, $precision = 0)
  {
    return round(((int)$bytes / 1024 / 1024), $precision, PHP_ROUND_HALF_EVEN);
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'maxFileSize' => $this->getMaxFileSize(),
      'maxSizePerWebsite' => $this->getMaxSizePerWebsite(),
    );
  }
}
