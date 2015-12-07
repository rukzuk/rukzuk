<?php
namespace Cms\Request\Modul;

use Cms\Request\Base;

/**
 * Request object for Modul GetAll
 *
 * @package      Application
 * @subpackage   Controller
 *
 * @SWG\Model(id="Request/Module/GetAll")
 */
class GetAll extends Base
{
  /**
   * @var string
   *
   * @SWG\Property(required=true,
   *  description="ID of website from which all modules will be fetched")
   */
  private $websiteId;

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
  }

  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
}
