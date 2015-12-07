<?php
namespace Cms\Request\Import;

use Cms\Request\Base;

/**
 * Request object for Import over url
 *
 * @package      Cms
 * @subpackage   Request
 */
class Url extends Base
{
  const DEFAULT_EMPTY_WEBSITE_ID = '-';
  
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var string
   */
  private $url;
  /**
   * @var string
   */
  private $allowedType;
  

  /**
   * @param string $id
   */
  public function setWebsiteId($id)
  {
    $this->websiteId = $id;
  }
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param string $url
   */
  public function setUrl($url)
  {
    $this->url = $url;
  }
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
  
  /**
   * @param string $allowedType
   */
  public function setAllowedType($allowedType)
  {
    $this->allowedType = $allowedType;
  }
  /**
   * @return string
   */
  public function getAllowedType()
  {
    return $this->allowedType;
  }
  protected function setValues()
  {
    if ($this->getRequestParam('websiteid') === null ||
        $this->getRequestParam('websiteid') === '') {
      $this->setWebsiteId(self::DEFAULT_EMPTY_WEBSITE_ID);
    } else {
      $this->setWebsiteId($this->getRequestParam('websiteid'));
    }
    
    if ($this->getRequestParam('url') !== null) {
      $this->setUrl($this->getRequestParam('url'));
    }

    if ($this->getRequestParam('allowedtype') !== null) {
      $this->setAllowedType($this->getRequestParam('allowedtype'));
    }
  }
}
