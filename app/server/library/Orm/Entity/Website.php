<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Seitenbau\UniqueIdGenerator;
use Orm\Data\Site as DataSite;

/**
 * Orm\Entity\Website
 */
class Website
{
  /**
   * @var string $id
   */
  private $id;

  /**
   * @var string $shortid
   */
  private $shortid;

  /**
   * @var string $name
   */
  private $name;

  /**
   * @var text $description
   */
  private $description = '';

  /**
   * @var text $navigation
   */
  private $navigation = '';

  /**
   * @var bool $publishingenabled
   */
  public $publishingenabled = false;

  /**
   * @var text $publish
   */
  private $publish = '';

  /**
   * @var text $colorscheme
   */
  private $colorscheme = '';
  
  /**
   * @var text $resolutions
   */
  private $resolutions = '';

  /**
   * @var integer $version
   */
  private $version = 0;

  /**
   * @var string $home
   */
  private $home = '';

  /**
   * @var null
   */
  private $usedsetid = null;

  /**
   * @var string $creationmode
   */
  private $creationmode = '';

  /**
   * @var boolean $isdeletable
   */
  private $ismarkedfordeletion = false;

  /**
   * @var timestamp
   */
  private $lastupdate;

  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadataInfo|\Orm\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('website');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\WebsiteRepository');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'prePersist');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'preUpdate');

    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'id',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'shortid',
      'type' => 'string',
      'length' => 10,
    ));
    $metadata->mapField(array(
      'fieldName' => 'name',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'description',
      'type' => 'text',
    ));
    $metadata->mapField(array(
      'fieldName' => 'navigation',
      'type' => 'text',
    ));
    $metadata->mapField(array(
      'fieldName' => 'publishingenabled',
      'type' => 'boolean',
      'default' => false,
    ));
    $metadata->mapField(array(
      'fieldName' => 'publish',
      'type' => 'text',
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'colorscheme',
      'type' => 'text',
    ));
    $metadata->mapField(array(
      'fieldName' => 'resolutions',
      'type' => 'text',
    ));
    $metadata->mapField(array(
      'fieldName' => 'version',
      'type' => 'integer',
    ));
    $metadata->mapField(array(
      'fieldName' => 'home',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'usedsetid',
      'type' => 'string',
      'length' => 100,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'creationmode',
      'type' => 'string',
      'length' => 10,
      'default' => 'full',
    ));
    $metadata->mapField(array(
      'fieldName' => 'ismarkedfordeletion',
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
   * Set short id
   *
   * @param string $shortId
   */
  public function setShortId($shortId)
  {
    $this->shortid = $shortId;
  }

  /**
   * Get short id
   *
   * @return string
   */
  public function getShortId()
  {
    return $this->shortid;
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
   * Set description
   *
   * @param text $description
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }

  /**
   * Get description
   *
   * @return text
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set navigation
   *
   * @param text $navigation
   */
  public function setNavigation($navigation)
  {
    $this->navigation = $navigation;
  }

  /**
   * Get navigation
   *
   * @return text
   */
  public function getNavigation()
  {
    return $this->navigation;
  }

  /**
   * Set publishing enabled
   *
   * @param bool $publishingEnabled
   */
  public function setPublishingEnabled($publishingEnabled)
  {
    $this->publishingenabled = $publishingEnabled;
  }

  /**
   * Get publishing enabled
   *
   * @return bool
   */
  public function getPublishingEnabled()
  {
    return $this->publishingenabled;
  }

  /**
   * Set publish
   *
   * @param text $publish
   */
  public function setPublish($publish)
  {
    $this->publish = $publish;
  }

  /**
   * Get publish
   *
   * @return text
   */
  public function getPublish()
  {
    return $this->publish;
  }

  /**
   * Set colorscheme
   *
   * @param text $colorscheme
   */
  public function setColorscheme($colorscheme)
  {
    $this->colorscheme = $colorscheme;
  }

  /**
   * Get colorscheme
   *
   * @return text
   */
  public function getColorscheme()
  {
    return $this->colorscheme;
  }

  /**
   * Set resolutions
   *
   * @param string $resolutions
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
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }

  /**
   * Get version
   *
   * @return integer
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * Set home
   *
   * @param string $home
   */
  public function setHome($home)
  {
    $this->home = $home;
  }

  /**
   * Get home
   *
   * @return string
   */
  public function getHome()
  {
    return $this->home;
  }

  /**
   * Set creationmode
   *
   * @param string $creationMode
   */
  public function setCreationMode($creationMode)
  {
    $this->creationmode = $creationMode;
  }

  /**
   * Get creationmode
   *
   * @return string
   */
  public function getCreationMode()
  {
    return $this->creationmode;
  }

  /**
   * Set ismarkedfordeletion
   *
   * @param boolean $ismarkedfordeletion
   */
  public function setMarkedForDeletion($markedForDeletion)
  {
    $this->ismarkedfordeletion = $markedForDeletion;
  }

  /**
   * Get ismarkedfordeletion
   *
   * @return boolean
   */
  public function isMarkedForDeletion()
  {
    return $this->ismarkedfordeletion;
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
   * @param string $usedSetId
   */
  public function setUsedSetId($usedSetId)
  {
    $this->usedsetid = $usedSetId;
  }

  /**
   * @return string
   */
  public function getUsedSetId()
  {
    return $this->usedsetid;
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
      'short' => $this->getShortId(),
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
      'lastupdate' => $this->getLastupdate(),
    );
  }

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\Website
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\Website();
    $dataObject->setId($this->getId())
               ->setShortId($this->getShortId())
               ->setName($this->getName())
               ->setDescription($this->getDescription())
               ->setNavigation($this->getNavigation())
               ->setColorscheme($this->getColorscheme())
               ->setResolutions($this->getResolutions())
               ->setVersion($this->getVersion())
               ->setPublishingEnabled($this->getPublishingEnabled())
               ->setPublish($this->getPublish())
               ->setHome($this->getHome())
               ->setUsedSetId($this->getUsedSetId())
               ->setCreationMode($this->getCreationMode())
               ->setMarkedForDeletion($this->isMarkedForDeletion())
               ->setLastUpdate($this->getLastupdate());
    return $dataObject;
  }
}
