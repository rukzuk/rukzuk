<?php
namespace Cms\Data;

use \Seitenbau\UniqueIdGenerator;
use \Seitenbau\Json as SbJson;
use \Orm\Data\Site as DataSite;

/**
 * Website Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
 */
class Website
{
  /**
   * @var string $id
   */
  private $id;

  /**
   * @var string $shortId
   */
  private $shortId;

  /**
   * @var string $name
   */
  private $name = '';

  /**
   * @var string $description
   */
  private $description = '';

  /**
   * @var string $navigation
   */
  private $navigation = '';

  /**
   * @var bool $publishingEnabled
   */
  private $publishingEnabled = false;

  /**
   * @var string $publish
   */
  private $publish = '';

  /**
   * @var string $colorscheme
   */
  private $colorscheme = '';

  /**
   * @var string $resolutions
   */
  private $resolutions = '';

  /**
   * @var integer $version
   */
  private $version = '';

  /**
   * @var string $home
   */
  private $home = '';

  /**
   * @var string $creationmode
   */
  private $creationMode = '';

  /**
   * @var boolean $isdeletable
   */
  private $isMarkedForDeletion = false;

  /**
   * @var int $lastupdate
   */
  private $lastUpdate;

  /**
   * @var null
   */
  private $usedSetId = null;

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
   * Set id
   *
   * @param string $id
   *
   * @return $this
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * @param string $shortId
   *
   * @return $this
   */
  public function setShortId($shortId)
  {
    $this->shortId = $shortId;
    return $this;
  }

  /**
   * @return string
   */
  public function getShortId()
  {
    return $this->shortId;
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
   * @param string $description
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
   * @return string $description
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set navigation
   *
   * @param string $navigation
   *
   * @return $this
   */
  public function setNavigation($navigation)
  {
    $this->navigation = $navigation;
    return $this;
  }

  /**
   * Get navigation
   *
   * @return string $navigation
   */
  public function getNavigation()
  {
    return $this->navigation;
  }

  /**
   * Set publishingEnabled
   *
   * @param bool $publishingEnabled
   *
   * @return $this
   */
  public function setPublishingEnabled($publishingEnabled)
  {
    $this->publishingEnabled = (bool)$publishingEnabled;
    return $this;
  }

  /**
   * Get publish
   *
   * @return bool $publishingEnabled
   */
  public function getPublishingEnabled()
  {
    return $this->publishingEnabled;
  }

  /**
   * Set publish
   *
   * @param string $publish
   *
   * @return $this
   */
  public function setPublish($publish)
  {
    $this->publish = $publish;
    return $this;
  }

  /**
   * Get publish
   *
   * @return string $publish
   */
  public function getPublish()
  {
    return $this->publish;
  }

  /**
   * Set colorscheme
   *
   * @param string $colorscheme
   *
   * @return $this
   */
  public function setColorscheme($colorscheme)
  {
    $this->colorscheme = $colorscheme;
    return $this;
  }

  /**
   * Get colorscheme
   *
   * @return string $colorscheme
   */
  public function getColorscheme()
  {
    return $this->colorscheme;
  }

  /**
   * Set resolutions
   *
   * @param string $resolutions
   *
   * @return $this
   */
  public function setResolutions($resolutions)
  {
    $this->resolutions = $resolutions;
    return $this;
  }

  /**
   * Get resolutions
   *
   * @return string $resolutions
   */
  public function getResolutions()
  {
    return $this->resolutions;
  }

  /**
   * Set version
   *
   * @param integer $version
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
   * @return integer $version
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * Set home
   *
   * @param string $home
   *
   * @return $this
   */
  public function setHome($home)
  {
    $this->home = $home;
    return $this;
  }

  /**
   * Get home
   *
   * @return string $home
   */
  public function getHome()
  {
    return $this->home;
  }

  /**
   * @return string|null
   */
  public function getUsedSetId()
  {
    return $this->usedSetId;
  }

  /**
   * @param string|null $usedSetId
   *
   * @return $this
   */
  public function setUsedSetId($usedSetId)
  {
    $this->usedSetId = $usedSetId;
    return $this;
  }

  /**
   * Set creationMode
   *
   * @param string $creationMode
   *
   * @return $this
   */
  public function setCreationMode($creationMode)
  {
    $this->creationMode = $creationMode;
    return $this;
  }

  /**
   * Get creationMode
   *
   * @return string
   */
  public function getCreationMode()
  {
    return $this->creationMode;
  }

  /**
   * Set isMarkedForDeletion
   *
   * @param boolean $markedForDeletion
   *
   * @return $this
   */
  public function setMarkedForDeletion($markedForDeletion)
  {
    $this->isMarkedForDeletion = $markedForDeletion;
    return $this;
  }

  /**
   * Get ismarkedfordeletion
   *
   * @return boolean
   */
  public function isMarkedForDeletion()
  {
    return $this->isMarkedForDeletion;
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
   * setzt die id beim clonen auf null
   */
  public function __clone()
  {
    $this->id = null;
  }

  /**
   * setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataSite::ID_PREFIX .
      UniqueIdGenerator::v4() .
      DataSite::ID_SUFFIX;
  }

  /**
   * Liefert alle Columns und deren Values
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'id' => $this->getId(),
      'shortid' => $this->getShortId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'navigation' => $this->getNavigation(),
      'publishingenabled' => $this->getPublishingEnabled(),
      'publish' => $this->getPublish(),
      'colorscheme' => $this->getColorscheme(),
      'resolutions' => $this->getResolutions(),
      'version' => $this->getVersion(),
      'home' => $this->getHome(),
      'usedsetid' => $this->getUsedSetId(),
      'creationmode' => $this->getCreationMode(),
      'ismarkedfordeletion' => $this->isMarkedForDeletion(),
      'lastupdate' => $this->getLastUpdate(),
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
      'colorscheme' => $this->getColorscheme(),
      'resolutions' => $this->getResolutions(),
      'version' => $this->getVersion(),
      'home' => $this->getHome(),
      'creationmode' => $this->getCreationMode(),
      'usedsetid' => $this->getUsedSetId(),
    );
  }

  protected function decode($fieldName, $fieldValue)
  {
    return $fieldValue;
  }
}
