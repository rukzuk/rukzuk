<?php
namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Seitenbau\UniqueIdGenerator;
use Orm\Data\Media as DataMedia;

/**
 * Orm\Entity\Media
 */
class Media
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
   * @var integer $dateUploaded
   */
  private $dateUploaded;

  /**
   * @var string $name
   */
  private $name;

  /**
   * @var string $filename
   */
  private $filename;

  /**
   * @var string $extension
   */
  private $extension;

  /**
   * @var bigint $size
   */
  private $size;

  /**
   * @var string $lastmod
   */
  private $lastmod;

  /**
   * @var string $file
   */
  private $file;

  /**
   * @var string $type
   */
  private $type;

  /**
   * @var string $mimetype
   */
  private $mimetype;

  /**
   * @var string $albumid
   */
  private $albumid;

  /**
   * @var string
   */
  private $iconUrl;

  /**
   * @var string
   */
  private $url;

  /**
   * @var string
   */
  private $lastupdate;

  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('media_item');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\MediaRepository');
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
      'fieldName' => 'dateUploaded',
      'type' => 'integer',
      'length' => 11,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'name',
      'type' => 'string',
      'length' => 255,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'filename',
      'type' => 'string',
      'length' => 255,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'extension',
      'type' => 'string',
      'length' => 255,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'size',
      'type' => 'bigint',
      'length' => 20,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'lastmod',
      'type' => 'string',
      'length' => 20,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'file',
      'type' => 'string',
      'length' => 255,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'type',
      'type' => 'string',
      'length' => 255,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'mimetype',
      'type' => 'string',
      'length' => 255,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'albumid',
      'type' => 'string',
      'length' => 100,
      'nullable' => true,
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
   * Set dateUploaded
   *
   * @param integer $dateUploaded
   */
  public function setDateUploaded($dateUploaded)
  {
    $this->dateUploaded = $dateUploaded;
  }

  /**
   * Get dateUploaded
   *
   * @return integer
   */
  public function getDateUploaded()
  {
    return $this->dateUploaded;
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
   * Set filename
   *
   * @param string $filename
   */
  public function setFilename($filename)
  {
    $this->filename = $filename;
  }

  /**
   * Get filename
   *
   * @return string
   */
  public function getFilename()
  {
    return $this->filename;
  }

  /**
   * Set extension
   *
   * @param string $extension
   */
  public function setExtension($extension)
  {
    $this->extension = $extension;
  }

  /**
   * Get extension
   *
   * @return string
   */
  public function getExtension()
  {
    return $this->extension;
  }

  /**
   * Set size
   *
   * @param bigint $size
   */
  public function setSize($size)
  {
    $this->size = $size;
  }

  /**
   * Get size
   *
   * @return bigint
   */
  public function getSize()
  {
    return $this->size;
  }

  /**
   * Set lastmod
   *
   * @param string $lastmod
   */
  public function setLastmod($lastmod)
  {
    $this->lastmod = $lastmod;
  }

  /**
   * Get lastmod
   *
   * @return string
   */
  public function getLastmod()
  {
    return $this->lastmod;
  }

  /**
   * Set file
   *
   * @param string $file
   */
  public function setFile($file)
  {
    $this->file = $file;
  }

  /**
   * Get file
   *
   * @return string
   */
  public function getFile()
  {
    return $this->file;
  }

  /**
   * Set type
   *
   * @param string $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * Get type
   *
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Set mimetype
   *
   * @param string $mimetype
   */
  public function setMimetype($mimetype)
  {
    $this->mimetype = $mimetype;
  }

  /**
   * Get mimetype
   *
   * @return string
   */
  public function getMimetype()
  {
    return $this->mimetype;
  }

  /**
   * Set album id
   *
   * @param string $albumid
   */
  public function setAlbumid($albumid)
  {
    $this->albumid = $albumid;
  }

  /**
   * Get album id
   *
   * @return string
   */
  public function getAlbumid()
  {
    return $this->albumid;
  }

  /**
   * @param string $url
   */
  public function setIconUrl($url)
  {
    $this->iconUrl = $url;
  }
  /**
   * @return string
   */
  public function getIconUrl()
  {
    return $this->iconUrl;
  }

  /**
   * @param string $url
   */
  public function setUrl($url)
  {
    $this->url = $url;
  }
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }

  /**
   * @return string
   */
  public function getLastupdate()
  {
    return $this->lastupdate;
  }

  /**
   * @param string $lastupdate
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
    $this->id = DataMedia::ID_PREFIX .
                UniqueIdGenerator::v4() .
                DataMedia::ID_SUFFIX;
  }

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\Media
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\Media();

    $dataObject->setAlbumId($this->getAlbumId())
               ->setDateUploaded($this->getDateUploaded())
               ->setExtension($this->getExtension())
               ->setFile($this->getFile())
               ->setFilename($this->getFilename())
               ->setIconUrl($this->getIconUrl())
               ->setId($this->getId())
               ->setLastmod($this->getLastmod())
               ->setMimetype($this->getMimetype())
               ->setName($this->getName())
               ->setSize($this->getSize())
               ->setType($this->getType())
               ->setUrl($this->getUrl())
               ->setWebsiteid($this->getWebsiteid())
               ->setLastUpdate($this->getLastupdate());

    return $dataObject;
  }
}
