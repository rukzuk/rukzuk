<?php
namespace Cms\Data;

/**
 * Class WebsiteQuota
 * @package Cms\Data
 */
class WebsiteQuota
{

  /**
   * @var int
   */
  private $maxCount = 0;

  /**
   * @param null|int $maxCount
   */
  public function __construct($maxCount = null)
  {
    if (!is_null($maxCount)) {
      $this->maxCount = (int)$maxCount;
    }
  }

  /**
   * @return int
   */
  public function getMaxCount()
  {
    return $this->maxCount;
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'maxCount' => $this->getMaxCount(),
    );
  }
}
