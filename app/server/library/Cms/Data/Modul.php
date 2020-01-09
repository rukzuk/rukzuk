<?php
namespace Cms\Data;

use Cms\Dao\Base\SourceItem;
use Seitenbau\UniqueIdGenerator;
use Orm\Data\Modul as DataModul;

/**
 * Module data transfer object class.
 *
 * @package      Cms
 * @subpackage   Data
 */

class Modul
{
  const SOURCE_LOCAL = 'local';
  const SOURCE_REPOSITORY = 'repo';

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
   * @var string $description
   */
  private $description;

  /**
   * @var string $version
   */
  private $version;

  /**
   * @var string $icon
   */
  private $icon;

  /**
   * @var string $form
   */
  private $form = null;

  /**
   * @var array $formvalues
   */
  private $formvalues = null;

  /**
   * @var string $category
   */
  private $category;

  /**
   * @var string $moduletype
   */
  private $moduletype;

  /**
   * @var string $allowedchildmoduletype
   */
  private $allowedchildmoduletype;

  /**
   * @var boolean $rerenderrequired
   */
  private $rerenderrequired;

  /**
   * @var int $lastupdate
   */
  private $lastUpdate;

  /**
   * @var mixed $customData
   */
  private $customData = null;

  /**
   * @var string $apiType
   */
  private $apiType = null;

  /**
   * @var string $sessionRequired
   */
  private $sessionRequired = null;

  /**
   * @var string $sourceType
   */
  private $sourceType = null;

  /**
   * @var SourceItem $source
   */
  private $source = null;

  /**
   * @var bool
   */
  private $overwritten = false;

  /**
   * @var string force_on|force_off|unit
   */
  private $ghostContainerMode = null;

  /**
   * @var \stdClass $config
   */
  private $config = null;

  /**
   * Set id
   *
   * @param $id
   *
   * @return string $id
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
   * @param string $websiteid
   *
   * @return $this
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
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
   *
   * @return $this
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
   * Set description
   *
   * @param text $description
   *
   * @return $this
   */
  public function setDescription($description)
  {
    $this->description = $description;
    return $this;
  }

  /**
   * Get description
   *
   * @return text $description
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set version
   *
   * @param string $version
   *
   * @return $this
   */
  public function setVersion($version)
  {
    $this->version = $version;
    return $this;
  }

  /**
   * Get version
   *
   * @return string $version
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * Set icon
   *
   * @param string $icon
   *
   * @return $this
   */
  public function setIcon($icon)
  {
    $this->icon = $icon;
    return $this;
  }

  /**
   * Get icon
   *
   * @return string $icon
   */
  public function getIcon()
  {
    return $this->icon;
  }

  /**
   * Set form
   *
   * @param text $form
   *
   * @return $this
   */
  public function setForm($form)
  {
    if (isset($form)) {
      $this->form = $form;
    } else {
      $this->form = array();
    }
    return $this;
  }

  /**
   * Get form
   *
   * @return text $form
   */
  public function getForm()
  {
    return $this->form;
  }

  /**
   * Set formvalues
   *
   * @param text $formvalues
   *
   * @return $this
   */
  public function setFormvalues($formvalues)
  {
    if (isset($formvalues)) {
      $this->formvalues = $formvalues;
    } else {
      $this->formvalues = new \stdClass();
    }
    return $this;
  }

  /**
   * Get formvalues
   *
   * @return text $formvalues
   */
  public function getFormvalues()
  {
    return $this->formvalues;
  }

  /**
   * Set config
   *
   * @param \stdClass $config
   *
   * @return $this
   */
  public function setConfig($config)
  {
    if (isset($config) && ($config instanceof \stdClass)) {
      $this->config = $config;
    } else {
      $this->config = new \stdClass();
    }
    return $this;
  }

  /**
   * Get config
   *
   * @return \stdClass $config
   */
  public function getConfig()
  {
    if ($this->config instanceof \stdClass) {
      return $this->config;
    }
    return new \stdClass();
  }

  /**
   * Set category
   *
   * @param text $category
   *
   * @return $this
   */
  public function setCategory($category)
  {
    $this->category = $category;
    return $this;
  }

  /**
   * Get category
   *
   * @return text $category
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * Set moduletype
   *
   * @param string $moduletype
   *
   * @return $this
   */
  public function setModuletype($moduletype)
  {
    $this->moduletype = $moduletype;
    return $this;
  }

  /**
   * Get moduletype
   *
   * @return string $moduletype
   */
  public function getModuletype()
  {
    return $this->moduletype;
  }

  /**
   * Set allowedchildmoduletype
   *
   * @param string $allowedchildmoduletype
   *
   * @return $this
   */
  public function setAllowedchildmoduletype($allowedchildmoduletype)
  {
    $this->allowedchildmoduletype = $allowedchildmoduletype;
    return $this;
  }

  /**
   * Get allowedchildmoduletype
   *
   * @return string $allowedchildmoduletype
   */
  public function getAllowedchildmoduletype()
  {
    return $this->allowedchildmoduletype;
  }

  /**
   * Set rerenderrequired
   *
   * @param boolean $rerenderrequired
   *
   * @return bool
   */
  public function getRerenderrequired()
  {
    return $this->rerenderrequired;
  }

  /**
   * Get rerenderrequired
   *
   * @param $rerenderrequired
   *
   * @return boolean $rerenderrequired
   */
  public function setRerenderrequired($rerenderrequired)
  {
    if ($rerenderrequired == true) {
      $this->rerenderrequired = true;
    } elseif ($rerenderrequired == false) {
      $this->rerenderrequired = false;
    } else {
      $this->rerenderrequired = null;
    }
    return $this;
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
   * @return $this
   */
  public function setLastUpdate($lastUpdate)
  {
    $this->lastUpdate = $lastUpdate;
    return $this;
  }

  /**
   * @param string $apiType
   *
   * @return $this
   */
  public function setApiType($apiType)
  {
    $this->apiType = $apiType;
    return $this;
  }

  /**
   * @return string
   */
  public function getApiType()
  {
    return $this->apiType;
  }


  /**
   * @return boolean
   */
  public function getSessionRequired()
  {
    return $this->sessionRequired;
  }

  /**
   * @param boolean $sessionRequired
   *
   * @return $this
   */
  public function setSessionRequired($sessionRequired)
  {
    $this->sessionRequired = (bool)$sessionRequired;
    return $this;
  }

  /**
   * Get custom data
   *
   * @return string
   */
  public function getCustomData()
  {
    return $this->customData;
  }

  /**
   * @param string $sourceType
   *
   * @return $this
   */
  public function setSourceType($sourceType)
  {
    $this->sourceType = $sourceType;
    return $this;
  }

  /**
   * @return string
   */
  public function getSourceType()
  {
    return $this->sourceType;
  }


  /**
   * @param SourceItem $source
   *
   * @return $this
   */
  public function setSource($source)
  {
    $this->source = $source;
    return $this;
  }

  /**
   * @return SourceItem
   */
  public function getSource()
  {
    return $this->source;
  }

  /**
   * @return string
   */
  public function getGhostContainerMode()
  {
    return $this->ghostContainerMode;
  }

  /**
   * @param string $ghostContainerMode
   */
  public function setGhostContainerMode($ghostContainerMode)
  {
    $this->ghostContainerMode = $ghostContainerMode;
  }

  /**
   * @param bool $overwritten
   *
   * @return $this
   */
  public function setOverwritten($overwritten)
  {
    $this->overwritten = (bool)$overwritten;
    return $this;
  }

  /**
   * @return boolean
   */
  public function isOverwritten()
  {
    return $this->overwritten;
  }

  /**
   * Set custom data
   *
   * @param mixed $customData
   *
   * @return $this
   */
  public function setCustomData($customData)
  {
    $this->customData = $customData;
    return $this;
  }

  /**
   * Setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataModul::ID_PREFIX .
      UniqueIdGenerator::v4() .
      DataModul::ID_SUFFIX;
    return $this;
  }

  /**
   * @return array
   */
  public function getManifest()
  {
    return array(
      'id' => $this->getId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'version' => $this->getVersion(),
      'category' => $this->getCategory(),
      'icon' => $this->getIcon(),
      'moduleType' => $this->getModuletype(),
      'allowedChildModuleType' => $this->getAllowedchildmoduletype(),
      'reRenderRequired' => $this->getRerenderrequired(),
      'apiType' => $this->getApiType(),
      'ghostContainerMode' => $this->getGhostContainerMode(),
      'sessionRequired' => $this->getSessionRequired(),
      'config' => $this->getConfig()
    );
  }
  /**
   * Liefert die Columns und deren Values welche bei einem Export
   * beruecksichtigt weerden zurueck.
   *
   * @return array
   */
  public function getExportColumnsAndValues()
  {
    return array(
      'id' => $this->getId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'version' => $this->getVersion(),
      'category' => $this->getCategory(),
      'icon' => $this->getIcon(),
      'form' => $this->getForm(),
      'formValues' => $this->getFormvalues(),
      'moduleType' => $this->getModuletype(),
      'allowedChildModuleType' => $this->getAllowedchildmoduletype(),
      'reRenderRequired' => $this->getRerenderrequired(),
      'customData' => $this->getCustomData(),
      'ghostContainerMode' => $this->getGhostContainerMode(),
      'apiType' => $this->getApiType(),
      'sessionRequired' => $this->getSessionRequired(),
      'config' => $this->getConfig()
    );
  }
  public function decode($fieldName)
  {

    $functionName = 'get' . ucfirst($fieldName);
    $value = $this->$functionName();

    // Wert vorhanden
    if (!empty($value)) {
      try {
        // Wert Normal decodieren
        return \Seitenbau\Json::decode($value);
      } catch (Exception $e) {
        // Fehler -> Keine Daten zurueckgeben
      }
    }
    return;
  }
}
