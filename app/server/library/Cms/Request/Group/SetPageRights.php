<?php
namespace Cms\Request\Group;

use Cms\Request\Base;
use Seitenbau\Types\Boolean as Boolean;

/**
 * SetPageRights Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class SetPageRights extends Base
{
  /**
   * @var string
   */
  private $id;
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var boolean
   */
  private $allRights = false;
  /**
   * @var arrray
   */
  private $rights;
  
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  
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
   * @param mixed $allRights
   */
  public function setAllRights($allRights)
  {
    $boolean = new Boolean($allRights);
    $this->allRights = $boolean->getValue();
  }
  /**
   * @return mixed
   */
  public function getAllRights()
  {
    return $this->allRights;
  }
  
  /**
   * @param string $rights
   */
  public function setRights($rights)
  {
    $this->rights = $rights;
  }
  /**
   * @return string
   */
  public function getRights()
  {
    return $this->rights;
  }
  
  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setAllRights($this->getRequestParam('allrights'));
    $this->setRights($this->getRequestParam('rights'));
  }
}
