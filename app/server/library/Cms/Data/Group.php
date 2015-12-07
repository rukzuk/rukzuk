<?php
namespace Cms\Data;

use Seitenbau\UniqueIdGenerator;
use Orm\Data\Group as DataGroup;

/**
 * Group Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
 */

class Group
{
  /**
   * @var string $id
   */
  private $id;

  /**
   * @var string $websiteid
   */
  private $websiteid;

  /**
   * @var string $name
   */
  private $name;

  /**
   * @var string $rights
   */
  private $rights;

  /**
   * @var string $users
   */
  private $users;

  /**
   * @var int $lastupdate
   */
  private $lastUpdate;

  /**
   * Set id
   *
   * @param string $id
   * @return Cms\Data\Group
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * Get id
   *
   * @return string $id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set websiteid
   *
   * @param string $id
   * @return Cms\Data\Group
   */
  public function setWebsiteid($id)
  {
    $this->websiteid = $id;
    return $this;
  }

  /**
   * Get websiteid
   *
   * @return string $websiteid
   */
  public function getWebsiteid()
  {
    return $this->websiteid;
  }

  /**
   * Set name
   *
   * @param string $name
   * @return Cms\Data\Group
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Get name
   *
   * @return string $name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set rights
   *
   * @param string $rights
   * @return Cms\Data\Group
   */
  public function setRights($rights)
  {
    $this->rights = $rights;
    return $this;
  }

  /**
   * Get rights
   *
   * @return string $rights
   */
  public function getRights()
  {
    return $this->rights;
  }

  /**
   * Set users
   *
   * @param string $users
   * @return Cms\Data\Group
   */
  public function setUsers($users)
  {
    $this->users = $users;
    return $this;
  }

  /**
   * Get users
   *
   * @return string $users
   */
  public function getUsers()
  {
    return $this->users;
  }

  /**
   * Get lastUpdate
   *
   * @return int
   */
  public function getLastUpdate()
  {
    return $this->lastUpdate;
  }

  /**
   * Set lastUpdate
   *
   * @param int $lastUpdate
   */
  public function setLastUpdate($lastUpdate)
  {
    $this->lastUpdate = $lastUpdate;
    return $this;
  }

  /**
   * Setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataGroup::ID_PREFIX .
                UniqueIdGenerator::v4() .
                DataGroup::ID_SUFFIX;
  }
}
