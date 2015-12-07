<?php
namespace Cms\Request\Log;

use Cms\Request\Base;

/**
 * Get Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class Get extends Base
{
  /**
   * @var string
   */
  private $websiteId = null;
  /**
   * @var integer
   */
  private $limit;
  /**
   * @var txt
   */
  private $format = 'txt';
  
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }
  
  /**
   * @param integer $limit
   */
  public function setLimit($limit)
  {
    $this->limit = $limit;
  }
  
  /**
   * @return integer
   */
  public function getLimit()
  {
    return $this->limit;
  }
  
  /**
   * @param string $format
   */
  public function setFormat($format)
  {
    $this->format = $format;
  }
  /**
   * @return string
   */
  public function getFormat()
  {
    return $this->format;
  }
  
  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setLimit($this->getRequestParam('limit'));
    if ($this->getRequestParam('format') !== null) {
      $this->setFormat($this->getRequestParam('format'));
    }
  }
}
