<?php


namespace Render\InfoStorage\MediaInfoStorage;

/**
 * This data transfer class represents a media database item inside of the
 * renderer.
 *
 * The rendering runs inside of the rukzuk backend (edit mode and preview) and
 * also on the live system. So this class is used to completely abstract the
 * rukzuk backend services and data objects from the renderings.
 * This way we can easily use the same rendering code on the live system and
 * inside of the rukzuk backend.
 *
 * @package Render\InfoStorage\MediaInfoStorage
 */
class MediaInfoStorageItem
{

  /**
   * @var string
   */
  private $id;

  /**
   * @var string
   */
  private $name;

  /**
   * @var string
   */
  private $filePath;

  /**
   * @var int
   */
  private $size;

  /**
   * @var int
   */
  private $lastModified;

  /**
   * @var string
   */
  private $iconFilePath;

  /**
   * @var null|string
   */
  private $websiteId;

  /**
   * @param string      $id
   * @param string      $filePath
   * @param string      $name
   * @param int         $size
   * @param int         $lastModified
   * @param string      $iconFilePath
   * @param null|string $websiteId
   */
  public function __construct(
      $id,
      $filePath,
      $name,
      $size,
      $lastModified,
      $iconFilePath,
      $websiteId = null
  ) {
    $this->id = $id;
    $this->filePath = $filePath;
    $this->name = $name;
    $this->size = $size;
    $this->lastModified = $lastModified;
    $this->iconFilePath = $iconFilePath;
    $this->websiteId = $websiteId;
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return int
   */
  public function getSize()
  {
    return $this->size;
  }

  /**
   * @return string
   */
  public function getFilePath()
  {
    return $this->filePath;
  }

  /**
   * @return int
   */
  public function getLastModified()
  {
    return $this->lastModified;
  }

  /**
   * @return string
   */
  public function getIconFilePath()
  {
    return $this->iconFilePath;
  }

  /**
   * @return null|string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
}
