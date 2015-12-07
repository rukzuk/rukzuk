<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Seitenbau\UniqueIdGenerator;
use Orm\Data\Group as DataGroup;

/**
 * Orm\Entity\Group
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
   * @var text $rights
   */
  private $rights;

  /**
   * @var text $users
   */
  private $users;

  /**
   * @var timestamp
   */
  private $lastupdate;

  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
   */
  public static function loadMetadata(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('user_group');
    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\GroupRepository');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'prePersist');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'preUpdate');

    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'id',
      'type' => 'string',
      'length' => 100
    ));
    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'websiteid',
      'type' => 'string',
      'length' => 100
    ));
    $metadata->mapField(array(
      'fieldName' => 'name',
      'type' => 'string',
      'length' => 255
    ));
    $metadata->mapField(array(
      'fieldName' => 'rights',
      'type' => 'text',
      'nullable' => true
    ));
    $metadata->mapField(array(
      'fieldName' => 'users',
      'type' => 'text'
    ));

    $metadata->mapField(array(
      'fieldName' => 'lastupdate',
      'type' => 'bigint',
      'default' => 0,
    ));
  }

  /**
   * set lastupdate to now
   */
  public function setLastupdateToNow()
  {
    $this->lastupdate = time();
  }

  /**
   * Set id
   *
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * Get id
   *
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set websiteid
   *
   * @param string $websiteid
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
  }

  /**
   * Get websiteid
   *
   * @return string
   */
  public function getWebsiteid()
  {
    return $this->websiteid;
  }

  /**
   * Set name
   *
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set rights
   *
   * @param text $rights
   */
  public function setRights($rights)
  {
    $this->rights = $rights;
  }

  /**
   * Get rights
   *
   * @return text
   */
  public function getRights()
  {
    return $this->rights;
  }

  /**
   * Set users
   *
   * @param text $users
   */
  public function setUsers($users)
  {
    $this->users = $users;
  }

  /**
   * Get lastupdate
   *
   * @return int
   */
  public function getLastupdate()
  {
    return $this->lastupdate;
  }

  /**
   * Set lastupdate
   *
   * @param int $lastupdate
   */
  public function setLastupdate($lastupdate)
  {
    $this->lastupdate = $lastupdate;
  }

  /**
   * Get users
   *
   * @return text
   */
  public function getUsers()
  {
    return $this->users;
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

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\Group
   */
  public function toCmsData()
  {
    $group = new \Cms\Data\Group();

    $group->setId($this->getId())
          ->setName($this->getName())
          ->setRights($this->getRights())
          ->setUsers($this->getUsers())
          ->setWebsiteid($this->getWebsiteid())
          ->setLastUpdate($this->getLastupdate());

    return $group;
  }
}
