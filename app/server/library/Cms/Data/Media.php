<?php

namespace Cms\Data;

use Seitenbau\UniqueIdGenerator;
use Orm\Data\Media as DataMedia;

/**
 * Media Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
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
   * @var int $dateUploaded
   */
  private $dateUploaded;

  /**
   * @var string $albumid
   */
  private $album;

  /**
   * @var string $albumId
   */
  private $albumId;

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
   * @var int $size
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
  private $downloadUrl;

  /**
   * @var string
   */
  private $lastUpdate;

  /**
   * Set id
   *
   * @param string $id
   * @return Cms\Data\Media
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
   * @return Cms\Data\Media
   */
  public function setWebsiteid($id)
  {
    $this->websiteid = $id;
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
   * Set dateUploaded
   *
   * @param int $dateUploaded
   * @return Cms\Data\Media
   */
  public function setDateUploaded($dateUploaded)
  {
    $this->dateUploaded = $dateUploaded;
    return $this;
  }

  /**
   * Get dateUploaded
   *
   * @return int $dateUploaded
   */
  public function getDateUploaded()
  {
    return $this->dateUploaded;
  }

  /**
   * Get album
   *
   * @return string $album
   */
  public function getAlbum()
  {
    return $this->album;
  }

  /**
   * Set album
   *
   * @param string $albumId
   * @return \Cms\Data\Media
   */
  public function setAlbumId($albumId)
  {
    $this->albumId = $albumId;
    return $this;
  }

  /**
   * Get album
   *
   * @return string $albumId
   */
  public function getAlbumId()
  {
    return $this->albumId;
  }

  /**
   * Set name
   *
   * @param string $name
   * @return Cms\Data\Media
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
   * Set filename
   *
   * @param string $filename
   * @return Cms\Data\Media
   */
  public function setFilename($filename)
  {
    $this->filename = $filename;
    return $this;
  }

  /**
   * Get filename
   *
   * @return string $filename
   */
  public function getFilename()
  {
    return $this->filename;
  }

  /**
   * Set extension
   *
   * @param string $extension
   * @return Cms\Data\Media
   */
  public function setExtension($extension)
  {
    $this->extension = $extension;
    return $this;
  }

  /**
   * Get extension
   *
   * @return string $extension
   */
  public function getExtension()
  {
    return $this->extension;
  }

  /**
   * Set size
   *
   * @param int $size
   * @return Cms\Data\Media
   */
  public function setSize($size)
  {
    $this->size = $size;
    return $this;
  }

  /**
   * Get size
   *
   * @return int $size
   */
  public function getSize()
  {
    return $this->size;
  }

  /**
   * Set lastmod
   *
   * @param string $lastmod
   * @return Cms\Data\Media
   */
  public function setLastmod($lastmod)
  {
    $this->lastmod = $lastmod;
    return $this;
  }

  /**
   * Get lastmod
   *
   * @return string $lastmod
   */
  public function getLastmod()
  {
    return $this->lastmod;
  }

  /**
   * Set file
   *
   * @param string $file
   * @return Cms\Data\Media
   */
  public function setFile($file)
  {
    $this->file = $file;
    return $this;
  }

  /**
   * Get file
   *
   * @return string $file
   */
  public function getFile()
  {
    return $this->file;
  }

  /**
   * Set type
   *
   * @param string $type
   * @return Cms\Data\Media
   */
  public function setType($type)
  {
    $this->type = $type;
    return $this;
  }

  /**
   * Get type
   *
   * @return string $type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Set mimetype
   *
   * @param string $mimetype
   * @return Cms\Data\Media
   */
  public function setMimetype($mimetype)
  {
    $this->mimetype = $mimetype;
    return $this;
  }

  /**
   * Get mimetype
   *
   * @return string $mimetype
   */
  public function getMimetype()
  {
    return $this->mimetype;
  }

  /**
   * @param string $url
   * @return Cms\Data\Media
   */
  public function setIconUrl($url)
  {
    $this->iconUrl = $url;
    return $this;
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
   * @return Cms\Data\Media
   */
  public function setUrl($url)
  {
    $this->url = $url;
    return $this;
  }
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }

  /**
   * @param string $url
   * @return Cms\Data\Media
   */
  public function setDownloadUrl($url)
  {
    $this->downloadUrl = $url;
    return $this;
  }
  /**
   * @return string
   */
  public function getDownloadUrl()
  {
    return $this->downloadUrl;
  }

  /**
   * @param int  $lastUpdate
   * @return \Cms\Data\Media
   */
  public function setLastUpdate($lastUpdate)
  {
    $this->lastUpdate = $lastUpdate;
    return $this;
  }

  /**
   * @return int
   */
  public function getLastUpdate()
  {
    return $this->lastUpdate;
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
   * Liefert alle Columns und deren Values
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'id' => $this->getId(),
      'name' => $this->getName(),
      'filename' => $this->getFilename(),
      'extension' => $this->getExtension(),
      'size' => $this->getSize(),
      'file' => $this->getFile(),
      'type' => $this->getType(),
      'mimetype' => $this->getMimetype(),
      'dateUploaded' => $this->getDateUploaded(),
      'websiteId' => $this->getWebsiteid(),
      'albumId' => $this->getAlbumId(),
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
      'filename' => $this->getFilename(),
      'extension' => $this->getExtension(),
      'size' => $this->getSize(),
      'file' => $this->getFile(),
      'type' => $this->getType(),
      'mimetype' => $this->getMimetype(),
      'dateUploaded' => $this->getDateUploaded(),
      'albumId' => $this->getAlbumId(),
    );
  }
}
