<?php
namespace Cms\Request\Group;

use Cms\Request\Base;

/**
 * Edit Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class Edit extends Base
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
   * @var string
   */
  private $name;
  /**
   * @var string
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
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
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
    $this->setName($this->getRequestParam('name'));
    $this->setRights($this->getRequestParam('rights'));
  }
}
