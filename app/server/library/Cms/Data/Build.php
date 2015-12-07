<?php
namespace Cms\Data;

use \Cms\Data\PublisherStatus as PublisherStatusData;

/**
 * Build
 *
 * @package      Cms
 * @subpackage   Data
 */
class Build
{
  /**
   * @var string
   */
  private $id;
  /**
   * @var integer
   */
  private $version;
  /**
   * @var integer
   */
  private $timestamp;
  /**
   * @var string
   */
  private $comment;
  /**
   * @var array
   */
  private $websiteId;
  /**
   * @var array
   */
  private $websiteName;
  /**
   * @var string
   */
  private $builderVersion;
  /**
   * @var array
   */
  private $creatorName;
  /**
   * @var array
   */
  private $creatorVersion;
  /**
   * @var array
   */
  private $lastPublished;

  /**
   */
  public function __construct()
  {
    $this->clear();
  }

  /**
   */
  public function clear()
  {
    $this->setId()
          ->setVersion()
          ->setTimestamp()
          ->setComment()
          ->setWebsiteName()
          ->setWebsiteId()
          ->setBuilderVersion()
          ->setCreatorName()
          ->setCreatorVersion()
          ->setLastPublished();
    return $this;
  }

  /**
   * @param string $id
   *
   * @return $this
   */
  public function setId($id = null)
  {
    $this->id = $id;
    return $this;
  }
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param integer $version
   *
   * @return $this
   */
  public function setVersion($version = null)
  {
    $this->version = $version;
    return $this;
  }
  /**
   * @return integer
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * @param integer $timestamp
   *
   * @return $this
   */
  public function setTimestamp($timestamp = null)
  {
    $this->timestamp = $timestamp;
    return $this;
  }
  /**
   * @return integer
   */
  public function getTimestamp()
  {
    return $this->timestamp;
  }

  /**
   * @param string $comment
   *
   * @return $this
   */
  public function setComment($comment = null)
  {
    $this->comment = $comment;
    return $this;
  }
  /**
   * @return string
   */
  public function getComment()
  {
    return $this->comment;
  }

  /**
   * @param string $websiteName
   *
   * @return $this
   */
  public function setWebsiteName($websiteName = null)
  {
    $this->websiteName = $websiteName;
    return $this;
  }
  /**
   * @return string
   */
  public function getWebsiteName()
  {
    return $this->websiteName;
  }

  /**
   * @param string $websiteId
   *
   * @return $this
   */
  public function setWebsiteId($websiteId = null)
  {
    $this->websiteId = $websiteId;
    return $this;
  }
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param null $builderVersion
   *
   * @internal param string $comment
   *
   * @return $this
   */
  public function setBuilderVersion($builderVersion = null)
  {
    $this->builderVersion = $builderVersion;
    return $this;
  }
  /**
   * @return string
   */
  public function getBuilderVersion()
  {
    return $this->builderVersion;
  }

  /**
   * @param string $creatorName
   *
   * @return $this
   */
  public function setCreatorName($creatorName = null)
  {
    $this->creatorName = $creatorName;
    return $this;
  }
  /**
   * @return array
   */
  public function getCreatorName()
  {
    return $this->creatorName;
  }

  /**
   * @param string $creatorVersion
   *
   * @return $this
   */
  public function setCreatorVersion($creatorVersion = null)
  {
    $this->creatorVersion = $creatorVersion;
    return $this;
  }
  /**
   * @return array
   */
  public function getCreatorVersion()
  {
    return $this->creatorVersion;
  }

  /**
   * @param \Cms\Data\PublisherStatus $lastPublished
   *
   * @return $this
   */
  public function setLastPublished($lastPublished = null)
  {
    if (!($lastPublished instanceof PublisherStatusData)) {
      $lastPublished = new PublisherStatusData();
    }
    $this->lastPublished = $lastPublished;
    return $this;
  }
  /**
   * @return \Cms\Data\PublisherStatus
   */
  public function getLastPublished()
  {
    return $this->lastPublished;
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'id'            => $this->getId(),
      'version'       => $this->getVersion(),
      'timestamp'     => $this->getTimestamp(),
      'comment'       => $this->getComment(),
      'website'       => array(
        'id'            => $this->getWebsiteId(),
        'name'          => $this->getWebsiteName(),
      ),
      'builder'       => array(
        'version'       => $this->getBuilderVersion(),
      ),
      'creator'       => array(
        'name'          => $this->getCreatorName(),
        'version'       => $this->getCreatorVersion(),
      ),
      'lastPublished' => $this->getLastPublished()->toArray(),
    );
  }

  /**
   * @param array $values
   *
   * @return $this
   */
  public function setFromArray($values)
  {
    $this->clear();
    
    if (isset($values['id'])) {
      $this->setId($values['id']);
    }
    if (isset($values['comment'])) {
      $this->setComment($values['comment']);
    }
    if (isset($values['timestamp'])) {
      $this->setTimestamp($values['timestamp']);
    }
    if (isset($values['version'])) {
      $this->setVersion($values['version']);
    }
    if (isset($values['builder'])) {
      if (isset($values['builder']['version'])) {
        $this->setBuilderVersion($values['builder']['version']);
      }
    }
    if (isset($values['creator'])) {
      if (isset($values['creator']['name'])) {
        $this->setCreatorName($values['creator']['name']);
      }
      if (isset($values['creator']['version'])) {
        $this->setCreatorVersion($values['creator']['version']);
      }
    }
    if (isset($values['website'])) {
      if (isset($values['website']['id'])) {
        $this->setWebsiteId($values['website']['id']);
      }
      if (isset($values['website']['name'])) {
        $this->setWebsiteName($values['website']['name']);
      }
    }
    if (isset($values['lastPublished'])) {
      $lastPublished = new PublisherStatusData();
      $this->setLastPublished($lastPublished->setFromArray($values['lastPublished']));
    }
    return $this;
  }
}
