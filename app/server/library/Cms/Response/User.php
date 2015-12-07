<?php
namespace Cms\Response;

use Cms\Data\User as UserData;
use Cms\Response\IsResponseData;
use Seitenbau\Types\Boolean as Boolean;
use Seitenbau\Locale as SbLocale;

/**
 * User
 *
 * @package      Cms
 * @subpackage   Response
 */

class User implements IsResponseData
{
  /**
   * @var string
   */
  public $id;
  
  /**
   * @var string
   */
  public $lastname;
  
  /**
   * @var string
   */
  public $firstname;
  
  /**
   * @var char
   */
  public $gender;
  
  /**
   * @var string
   */
  public $email;
  
  /**
   * @var string
   */
  public $language;
  
  /**
   * @var boolean
   */
  public $superuser;
  
  /**
   * @var boolean
   */
  public $isdeletable;

  /**
   * @var boolean
   */
  public $owner;

  /**
   * @var boolean
   */
  public $readonly;

  /**
   * @var array
   */
  public $groups;

  /**
   * @param \Cms\Data\User $data
   */
  public function __construct(UserData $data)
  {
    $this->setValuesFromData($data);
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
  public function getId()
  {
    return $this->id;
  }
  
  /**
   * @param string $lastname
   */
  public function setLastname($lastname)
  {
    $this->lastname = $lastname;
  }
  
  /**
   * @return string
   */
  public function getLastname()
  {
    return $this->lastname;
  }
  
  /**
   * @param string $firstname
   */
  public function setFirstname($firstname)
  {
    $this->firstname = $firstname;
  }
  
  /**
   * @return string
   */
  public function getFirstname()
  {
    return $this->firstname;
  }
  
  /**
   * @param string $gender
   */
  public function setGender($gender)
  {
    $this->gender = $gender;
  }
  
  /**
   * @return string
   */
  public function getGender()
  {
    return $this->gender;
  }
  
  /**
   * @param string $email
   */
  public function setEmail($email)
  {
    $this->email = $email;
  }
  
  /**
   * @return string
   */
  public function getEmail()
  {
    return $this->email;
  }
  
  /**
   * @param string $language
   */
  public function setLanguage($language)
  {
    if (is_string($language)) {
      $this->language = SbLocale::convertToLanguageCode($language);
    } else {
      $this->language = null;
    }
  }
  /**
   * @return string
   */
  public function getLanguage()
  {
    return $this->language;
  }

  /**
   * @param boolean $boolean
   */
  public function setSuperuser($boolean)
  {
    $boolean = new Boolean($boolean);
    $this->superuser = $boolean->getValue(Boolean::STRICT_VALUE);
  }

  /**
   * @return boolean
   */
  public function getSuperuser()
  {
    return $this->superuser;
  }

  /**
   * @param boolean $boolean
   */
  public function setOwner($boolean)
  {
    $boolean = new Boolean($boolean);
    $this->owner = $boolean->getValue(Boolean::STRICT_VALUE);
  }

  /**
   * @return boolean
   */
  public function getOwner()
  {
    return $this->owner;
  }
    
  /**
   * @param boolean $boolean
   */
  public function setDeletable($boolean)
  {
    $boolean = new Boolean($boolean);
    $this->isdeletable = $boolean->getValue(Boolean::STRICT_VALUE);
  }
  
  /**
   * @return boolean
   */
  public function getDeletable()
  {
    return $this->isdeletable;
  }

  /**
   * @param bool
   */
  public function setReadonly($readonly)
  {
    $boolean = new Boolean($readonly);
    $this->readonly = $boolean->getValue(Boolean::STRICT_VALUE);
  }

  /**
   * @return bool
   */
  public function getReadonly()
  {
    return $this->readonly;
  }

  /**
   * @param array[] Cms\Data\Group $groups
   */
  public function setGroups(array $groups)
  {
    $groupIds = null;
    if (is_array($groups) && count($groups) > 0) {
      foreach ($groups as $group) {
        $groupIds[$group->getWebsiteid()][] = $group->getId();
      }
    }
    $this->groups = $groupIds;
  }
  
  /**
   * @return array
   */
  public function getGroups()
  {
    return $this->groups;
  }

  /**
   * @param \Cms\Data\User $data
   */
  protected function setValuesFromData(UserData $data)
  {
    $this->setId($data->getId());
    $this->setLastname($data->getLastname());
    $this->setFirstname($data->getFirstname());
    $this->setGender($data->getGender());
    $this->setEmail($data->getEmail());
    $this->setLanguage($data->getLanguage());
    $this->setSuperuser($data->isSuperuser());
    $this->setDeletable($data->isDeletable());
    $this->setOwner($data->isOwner());
    $this->setReadonly($data->isReadonly());
    $this->setGroups($data->getGroups());
  }
}
