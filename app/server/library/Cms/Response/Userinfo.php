<?php
namespace Cms\Response;

use Seitenbau\Locale as SbLocale;

class Userinfo implements IsResponseData
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
   * @var string
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
  public $superuser = false;
  /**
   * @var boolean
   */
  public $owner = false;
  /**
   * @var boolean
   */
  public $readonly = false;
  /**
   * @var string
   */
  public $dashboardUrl;
  /**
   * @var string
   */
  public $upgradeUrl;
  /**
   * @var array
   */
  public $groups = array();
  /**
   * @param stdClass
   */
  public $privilege;

  public function __construct($data)
  {
    if (is_array($data)) {
      $this->setValuesFromArray($data);
    }
  }

  public function setIsSuperuser($isSuperuser)
  {
    $this->superuser = ($isSuperuser === true) ? true : false;
  }

  public function setIsOwner($isOwner)
  {
    $this->owner = ($isOwner === true) ? true : false;
  }

  public function setIsReadonly($isReadonly)
  {
    $this->readonly = ($isReadonly === true) ? true : false;
  }

  public function setDashboardUrl($dashboardUrl)
  {
    $this->dashboardUrl = $dashboardUrl;
  }

  public function setUpgradeUrl($upgradeUrl)
  {
    $this->upgradeUrl = $upgradeUrl;
  }

  public function setId($id)
  {
    $this->id = $id;
  }
  public function setLastname($name)
  {
    $this->lastname = $name;
  }
  public function setFirstname($name)
  {
    $this->firstname = $name;
  }
  public function setGender($gender)
  {
    $this->gender = $gender;
  }
  public function setEmail($email)
  {
    $this->email = $email;
  }
  public function setLanguage($language)
  {
    if (is_string($language)) {
      $this->language = SbLocale::convertToLanguageCode($language);
    } else {
      $this->language = null;
    }
  }
  public function setGroups(array $groups)
  {
    if (count($groups) > 0) {
      foreach ($groups as $group) {
        if (isset($group['id'])) {
          $this->groups[$group['websiteid']][] = $group['id'];
        }
      }
    }
  }
  
  public function getPrivilege()
  {
    return $this->privilege;
  }

  public function setPrivilege($privilege)
  {
    $this->privilege = $privilege;
  }
  
  private function setValuesFromArray(array $data = array())
  {
    $this->setId($data['id']);
    if (isset($data['superuser'])) {
      $this->setIsSuperuser($data['superuser']);
    }
    if (isset($data['lastname'])) {
      $this->setLastname($data['lastname']);
    }
    if (isset($data['firstname'])) {
      $this->setFirstname($data['firstname']);
    }
    if (isset($data['gender'])) {
      $this->setGender($data['gender']);
    }
    if (isset($data['email'])) {
      $this->setEmail($data['email']);
    }
    if (isset($data['language'])) {
      $this->setLanguage($data['language']);
    }
    if (isset($data['groups'])) {
      $this->setGroups($data['groups']);
    }
    if (isset($data['privilege'])) {
      $this->setPrivilege($data['privilege']);
    }
    if (isset($data['owner'])) {
      $this->setIsOwner($data['owner']);
    }
    if (isset($data['readonly'])) {
      $this->setIsReadonly($data['readonly']);
    }
    if (isset($data['sourceInfo']) && is_array($data['sourceInfo'])) {
      $sourceInfo = $data['sourceInfo'];
      if (isset($sourceInfo['dashboardUrl']) && !empty($sourceInfo['dashboardUrl'])) {
        $this->setDashboardUrl($sourceInfo['dashboardUrl']);
      }
      if (isset($sourceInfo['upgradeUrl']) && !empty($sourceInfo['upgradeUrl'])) {
        $this->setUpgradeUrl($sourceInfo['upgradeUrl']);
      }
    }
  }
}
