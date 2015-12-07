<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Seitenbau\UniqueIdGenerator;
use Orm\Data\Album as DataAlbum;

/**
 * Orm\Entity\Album
 */
class Album
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
   * @var timestamp
   */
  private $lastupdate;

  public function __construct()
  {
    $this->medias = new \Doctrine\Common\Collections\ArrayCollection();
  }

  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('album');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\AlbumRepository');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'prePersist');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'preUpdate');

    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'id',
      'type' => 'string',
      'length' => 100,
    ));

    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'websiteid',
      'type' => 'string',
      'length' => 100,
    ));

    $metadata->mapField(array(
      'fieldName' => 'name',
      'type' => 'string',
      'length' => 255,
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
   * Set medias
   *
   * @param array $medias
   */
  public function setMedias($medias)
  {
    $this->medias = $medias;
  }

  /**
   * Get medias
   *
   * @return Doctrine\Common\Collections\Collection
   */
  public function getMedias()
  {
    return $this->medias;
  }

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\Album
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\Album();
    $dataObject->setId($this->getId())
               ->setName($this->getName())
               ->setWebsiteid($this->getWebsiteid())
               ->setLastUpdate($this->getLastupdate());

    return $dataObject;
  }
  /**
   * Setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataAlbum::ID_PREFIX .
                UniqueIdGenerator::v4() .
                DataAlbum::ID_SUFFIX;
  }
}
