<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Seitenbau\UniqueIdGenerator;
use Orm\Data\User as DataUser;

/**
 * Orm\Entity\User
 */
class User
{
  const GENDER_MALE = 'm';
  const GENDER_FEMALE = 'f';
  /**
   * @var string $id
   */
  private $id;

  /**
   * @var string $lastname
   */
  private $lastname;

  /**
   * @var string $firstname
   */
  private $firstname;

  /**
   * @var string $password
   */
  private $password;

  /**
   * @var string $gender
   */
  private $gender;

  /**
   * @var string $email
   */
  private $email;

  /**
   * @var string $language
   */
  private $language;

  /**
   * @var boolean $issuperuser
   */
  private $issuperuser;

  /**
   * @var boolean $isdeletable
   */
  private $isdeletable;
  
  /**
   * @var array
   */
  private $groups;

  /**
   * @var timestamp
   */
  private $lastupdate;
  
  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('user');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\UserRepository');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'prePersist');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'preUpdate');

    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'id',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'lastname',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'firstname',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'password',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'gender',
      'type' => 'string',
      'length' => 10,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'email',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'language',
      'type' => 'string',
      'length' => 10,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'issuperuser',
      'type' => 'boolean',
    ));
    $metadata->mapField(array(
      'fieldName' => 'isdeletable',
      'type' => 'boolean',
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
   * Set lastname
   *
   * @param string $lastname
   */
  public function setLastname($lastname)
  {
    $this->lastname = $lastname;
  }

  /**
   * Get lastname
   *
   * @return string
   */
  public function getLastname()
  {
    return $this->lastname;
  }

  /**
   * Set firstname
   *
   * @param string $firstname
   */
  public function setFirstname($firstname)
  {
    $this->firstname = $firstname;
  }

  /**
   * Get firstname
   *
   * @return string
   */
  public function getFirstname()
  {
    return $this->firstname;
  }

  /**
   * Set password
   *
   * @param string $password
   */
  public function setPassword($password)
  {
    $this->password = $password;
  }

  /**
   * Get password
   *
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * Set gender
   *
   * @param string $gender
   */
  public function setGender($gender)
  {
    if (!is_null($gender) && !in_array($gender, array(self::GENDER_MALE, self::GENDER_FEMALE))) {
        throw new \InvalidArgumentException('Invalid gender');
    }
    $this->gender = $gender;
  }

  /**
   * Get gender
   *
   * @return string
   */
  public function getGender()
  {
    return $this->gender;
  }

  /**
   * Set email
   *
   * @param string $email
   */
  public function setEmail($email)
  {
    $this->email = $email;
  }

  /**
   * Get email
   *
   * @return string
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Set language
   *
   * @param string $language
   */
  public function setLanguage($language)
  {
    $this->language = $language;
  }

  /**
   * Get language
   *
   * @return string
   */
  public function getLanguage()
  {
    return $this->language;
  }

  /**
   * Set issuperuser
   *
   * @param boolean $issuperuser
   */
  public function setSuperuser($issuperuser)
  {
    $this->issuperuser = $issuperuser;
  }

  /**
   * Get issuperuser
   *
   * @return boolean
   */
  public function isSuperuser()
  {
    return $this->issuperuser;
  }

  /**
   * Set isdeletable
   *
   * @param boolean $isdeletable
   */
  public function setDeletable($isdeletable)
  {
    $this->isdeletable = $isdeletable;
  }

  /**
   * Get isdeletable
   *
   * @return boolean
   */
  public function isDeletable()
  {
    return $this->isdeletable;
  }
  /**
   * @param array $groups
   */
  public function setGroups($groups)
  {
    $this->groups = $groups;
  }
  /**
   * @return array
   */
  public function getGroups()
  {
    return $this->groups;
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
   * Setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataUser::ID_PREFIX .
                UniqueIdGenerator::v4() .
                DataUser::ID_SUFFIX;
  }
  
  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\User
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\User();
    $dataObject->setId($this->getId())
               ->setDeletable($this->isDeletable())
               ->setEmail($this->getEmail())
               ->setFirstname($this->getFirstname())
               ->setGender($this->getGender())
               ->setLanguage($this->getLanguage())
               ->setGroups($this->getGroups())
               ->setLastname($this->getLastname())
               ->setPassword($this->getPassword())
               ->setSuperuser($this->isSuperuser())
               ->setOwner(false)
               ->setLastUpdate($this->getLastupdate());
    return $dataObject;
  }
}
