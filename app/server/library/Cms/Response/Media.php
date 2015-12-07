<?php
namespace Cms\Response;

use Cms\Data\Media as MediaData;

/**
 * Einzelne Media fuer Response-Darstellung
 *
 * @package      Cms
 * @subpackage   Response
 */

class Media implements IsResponseData
{
  /**
   * @var string
   */
  public $id;
  
  /**
   * @var string
   */
  public $websiteId;
  
  /**
   * @var string
   */
  public $albumId;

  /**
   * @var string
   */
  public $name;
  
  /**
   * @var string
   */
  public $url;
  
  /**
   * @var string
   */
  public $icon;

  /**
   * @var string
   */
  public $downloadUrl;

  /**
   * @var string
   */
  public $type;
  
  /**
   * @var string
   */
  public $extension;
  
  /**
   * @var integer
   */
  public $filesize;
  
  /**
   * @var string
   */
  private $file;
  
  /**
   * @var integer
   */
  private $lastModification;
  
  /**
   * @var integer
   */
  public $dateUploaded;

  /**
   * @param \Orm\Entity\Media $data
   */
  public function __construct(MediaData $data = null)
  {
    if ($data !== null) {
      $this->setValuesFromData($data);
    }
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
   * @param string $id
   */
  public function setAlbumId($id)
  {
    $this->albumId = $id;
  }
  
  /**
   * @return string
   */
  public function getAlbumId()
  {
    return $this->albumId;
  }

  /**
   * @param string $id
   */
  public function setWebsiteId($id)
  {
    $this->websiteId = $id;
  }
  
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
  
  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  
  /**
   * @param string $extension
   */
  public function setExtension($extension)
  {
    $this->extension = $extension;
  }
  
  /**
   * @return string
   */
  public function getExtension()
  {
    return $this->extension;
  }
  
  /**
   * @param string $size
   */
  public function setFilesize($size)
  {
    $this->filesize = $size;
  }
  
  /**
   * @return string
   */
  public function getFilesize()
  {
    return $this->filesize;
  }
  
  /**
   * @param string $file
   */
  public function setFile($file)
  {
    $this->file = $file;
  }
  
  /**
   * @return string
   */
  public function getFile()
  {
    return $this->file;
  }
  
  /**
   * @param integer $time
   */
  public function setLastModification($time)
  {
    $this->lastModification = $time;
  }
  
  /**
   * @return integer
   */
  public function getLastModification()
  {
    return $this->lastModification;
  }
  
  /**
   * @param string $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }
  
  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
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
   * @param string $icon
   */
  public function setIcon($icon)
  {
    $this->icon = $icon;
  }
  
  /**
   * @return string
   */
  public function getIcon()
  {
    return $this->icon;
  }

  /**
   * @param string $url
   */
  public function setDownloadUrl($url)
  {
    $this->downloadUrl = $url;
  }

  /**
   * @return string
   */
  public function getDownloadUrl()
  {
    return $this->downloadUrl;
  }

  /**
   * @return int
   */
  public function getDateUploaded()
  {
    return $this->dateUploaded;
  }

  /**
   * @param int $dateUploaded
   */
  public function setDateUploaded($dateUploaded)
  {
    $this->dateUploaded = $dateUploaded;
  }

    
  /**
   * @param \Cms\Data\Media $data
   */
  protected function setValuesFromData(MediaData $data)
  {
    $this->setId($data->getId());
    $this->setWebsiteId($data->getWebsiteId());
    $this->setAlbumId($data->getAlbumId());
    $this->setName($data->getName());
    $this->setExtension($data->getExtension());
    $this->setFilesize($data->getSize());
    $this->setFile($data->getFile());
    $this->setLastModification($data->getLastmod());
    $this->setType($data->getType());
    $this->setDateUploaded($data->getDateUploaded());
    $this->setUrl($data->getUrl());
    $this->setIcon($data->getIconUrl());
    $this->setDownloadUrl($data->getDownloadUrl());
  }
}
