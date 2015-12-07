<?php
namespace Cms\Response;

use Cms\Data\Group as GroupData;
use Cms\Dao\Group as DaoGroup;
use Cms\Response\IsResponseData;

/**
 * Einzelnes Group fuer Response-Darstellung
 *
 * @package      Cms
 * @subpackage   Response
 */

class Group implements IsResponseData
{
  /**
   * @var string
   */
  public $id;
  
  /**
   * @var string
   */
  public $websiteId;
  
  /**
   * @var string
   */
  public $name;
  
  /**
   * @var string
   */
  public $rights;
  /**
   * @var array
   */
  public $users;

  /**
   * @param Cms\Data\Group $data
   */
  public function __construct(GroupData $data)
  {
    $this->setValuesFromData($data);
  }
  
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
   * @param string $rights
   */
  public function setRights($rights)
  {
    if ($rights === DaoGroup::DEFAULT_EMPTY_RIGHTS) {
      $this->rights = array();
    } else {
      $this->rights = json_decode($rights);
    }
  }
  
  /**
   * @return string
   */
  public function getRights()
  {
    return $this->rights;
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
   * @param string $users
   */
  public function setUsers($users)
  {
    if (is_array($users) && count($users) > 0) {
      foreach ($users as $user) {
        $this->users[] = new \Cms\Response\User($user);
      }
    } else {
      $this->users = array();
    }
  }
  
  /**
   * @return array
   */
  public function getUsers()
  {
    return $this->users;
  }
  
  /**
   * @param $data
   */
  protected function setValuesFromData(GroupData $data)
  {
    $this->setId($data->getId());
    $this->setWebsiteId($data->getWebsiteId());
    $this->setName($data->getName());
    $this->setRights($data->getRights());
    $this->setUsers($data->getUsers());
  }
}
