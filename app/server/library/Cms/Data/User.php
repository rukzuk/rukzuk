<?php
namespace Cms\Data;

use Seitenbau\UniqueIdGenerator;
use Orm\Data\User as DataUser;

/**
 * User Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
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
   * @var boolean $isowner
   */
  private $isowner;

  /**
   * @var boolean $isreadonly
   */
  private $isreadonly;

  /**
   * @var array
   */
  private $sourceInfo = array();

  /**
   * @var \Cms\Data\Group[]
   */
  private $groups;

  /**
   * @var int $lastupdate
   */
  private $lastUpdate;

  /**
   * set id
   *
   * @param string $id
   * @return User
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
   * Set lastname
   *
   * @param string $lastname
   * @return \Cms\Data\User
   */
  public function setLastname($lastname)
  {
    $this->lastname = $lastname;
    return $this;
  }

  /**
   * Get lastname
   *
   * @return string $lastname
   */
  public function getLastname()
  {
    return $this->lastname;
  }

  /**
   * Set firstname
   *
   * @param string $firstname
   * @return \Cms\Data\User
   */
  public function setFirstname($firstname)
  {
    $this->firstname = $firstname;
    return $this;
  }

  /**
   * Get firstname
   *
   * @return string $firstname
   */
  public function getFirstname()
  {
    return $this->firstname;
  }

  /**
   * Set password
   *
   * @param string $password
   * @return \Cms\Data\User
   */
  public function setPassword($password)
  {
    $this->password = $password;
    return $this;
  }

  /**
   * Get password
   *
   * @return string $password
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * Set gender
   *
   * @param string $gender
   * @return \Cms\Data\User
   */
  public function setGender($gender)
  {
    if (isset($gender) && !in_array($gender, array(self::GENDER_MALE, self::GENDER_FEMALE))) {
        throw new \InvalidArgumentException('Invalid gender');
    }
    $this->gender = $gender;
    return $this;
  }

  /**
   * Get gender
   *
   * @return string $gender
   */
  public function getGender()
  {
    return $this->gender;
  }

  /**
   * Set email
   *
   * @param string $email
   * @return \Cms\Data\User
   */
  public function setEmail($email)
  {
    $this->email = $email;
    return $this;
  }

  /**
   * Get email
   *
   * @return string $email
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Set language
   *
   * @param string $language
   *
   * @return \Cms\Data\User
   */
  public function setLanguage($language)
  {
    $this->language = $language;
    return $this;
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
   * Set superuser
   *
   * @param boolean $issuperuser
   * @return \Cms\Data\User
   */
  public function setSuperuser($issuperuser)
  {
    $this->issuperuser = $issuperuser;
    return $this;
  }

  /**
   * Get superuser
   *
   * @return boolean $superuser
   */
  public function isSuperuser()
  {
    return $this->issuperuser;
  }

  /**
   * Set isdeletable
   *
   * @param boolean $isdeletable
   * @return \Cms\Data\User
   */
  public function setDeletable($isdeletable)
  {
    $this->isdeletable = ($isdeletable == true);
    return $this;
  }

  /**
   * Get isdeletable
   *
   * @return boolean $isdeletable
   */
  public function isDeletable()
  {
    return $this->isdeletable;
  }

  /**
   * Set isowner
   *
   * @param boolean $isowner
   * @return \Cms\Data\User
   */
  public function setOwner($isowner)
  {
    $this->isowner = ($isowner == true);
    return $this;
  }

  /**
   * Get isowner
   *
   * @return boolean isowner
   */
  public function isOwner()
  {
    return $this->isowner;
  }

  /**
   * Set readonly flag
   *
   * @param boolean $isReadonly
   * @return \Cms\Data\User
   */
  public function setReadonly($isReadonly)
  {
    $this->isreadonly = (bool)$isReadonly;
    return $this;
  }

  /**
   * Get readonly flag
   *
   * @return boolean
   */
  public function isReadonly()
  {
    return $this->isreadonly;
  }

  /**
   * @param array $sourceInfo
   * @return \Cms\Data\User
   */
  public function setSourceInfo(array $sourceInfo)
  {
    $this->sourceInfo = $sourceInfo;
    return $this;
  }

  /**
   * @return array
   */
  public function getSourceInfo()
  {
    return $this->sourceInfo;
  }

  /**
   * @param array $groups
   * @return \Cms\Data\User
   */
  public function setGroups($groups)
  {
    $this->groups = $groups;
    return $this;
  }

  /**
   * @return \Cms\Data\Group[]
   */
  public function getGroups()
  {
    return $this->groups;
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
   *
   * @return \Cms\Data\User
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
    $this->id = DataUser::ID_PREFIX .
                UniqueIdGenerator::v4() .
                DataUser::ID_SUFFIX;
  }

  /**
   * Liefert alle Columns und deren Values (auser das Passwort)
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'id'          => $this->getId(),
      'lastname'    => $this->getLastname(),
      'firstname'   => $this->getFirstname(),
      'gender'      => $this->getGender(),
      'email'       => $this->getEmail(),
      'language'    => $this->getLanguage(),
      'superuser'   => $this->isSuperuser(),
      'owner'       => $this->isOwner(),
      'readonly'    => $this->isReadonly(),
      'sourceInfo'  => $this->getSourceInfo(),
      'lastUpdate'  => $this->getLastUpdate(),
    );
  }
}
