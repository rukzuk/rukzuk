<?php
namespace Cms\Request\User;

use Cms\Request\Base;

/**
 * GetAll
 *
 * @package      Cms
 * @subpackage   Request
 */
class GetAll extends Base
{
  /**
   * @var string
   */
  private $websiteId;
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
  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
  }
}
