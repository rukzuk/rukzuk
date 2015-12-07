<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Seitenbau\UniqueIdGenerator;

/**
 * Orm\Entity\Lock
 */
class Lock
{
  /**
   * @var string $websiteid
   */
  private $websiteid;

  /**
   * @var string $itemid
   */
  private $itemid = '';

  /**
   * @var string $userid
   */
  private $userid = '';

  /**
   * @var string $runid
   */
  private $runid = '';

  /**
   * @var string $type
   */
  private $type = '';

  /**
   * @var starttime
   */
  private $starttime;

  /**
   * @var lastactivity
   */
  private $lastactivity;

  /**
   * @param Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('locks');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\LockRepository');

    $metadata->mapField(array(
      'fieldName' => 'userid',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'runid',
      'type' => 'string',
      'length' => 100
    ));
    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'itemid',
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
      'id' => true,
      'fieldName' => 'type',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'starttime',
      'type' => 'string',
      'length' => 20
    ));
    $metadata->mapField(array(
      'fieldName' => 'lastactivity',
      'type' => 'string',
      'length' => 20
    ));
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
   * Set userid
   *
   * @param string $userid
   */
  public function setUserid($userid)
  {
    $this->userid = $userid;
  }

  /**
   * Get userid
   *
   * @return string
   */
  public function getUserid()
  {
    return $this->userid;
  }

  /**
   * Set runid
   *
   * @param string $runid
   */
  public function setRunid($runid)
  {
    $this->runid = $runid;
  }

  /**
   * Get runid
   *
   * @return string
   */
  public function getRunid()
  {
    return $this->runid;
  }

  /**
   * Set itemid
   *
   * @param string $itemid
   */
  public function setItemid($itemid)
  {
    $this->itemid = $itemid;
  }

  /**
   * Get itemid
   *
   * @return string
   */
  public function getItemid()
  {
    return $this->itemid;
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
   * Get starttime
   *
   * @return int
   */
  public function getStarttime()
  {
    return $this->starttime;
  }

  /**
   * Set starttime
   *
   * @param string $starttime
   */
  public function setStarttime($starttime)
  {
    $this->starttime = $starttime;
  }

  /**
   * Get lastactivity
   *
   * @return int
   */
  public function getLastactivity()
  {
    return $this->lastactivity;
  }

  /**
   * Set lastactivity
   *
   * @param string $lastactivity
   */
  public function setLastactivity($lastactivity)
  {
    $this->lastactivity = $lastactivity;
  }

  /**
   * Liefert alle Columns und deren Values
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'userId' => $this->getUserid(),
      'runId' => $this->getRunid(),
      'itemId' => $this->getItemid(),
      'websiteid' => $this->getWebsiteid(),
      'type' => $this->getType(),
      'starttime' => $this->getStarttime(),
      'lastactivity' => $this->getLastactivity()
    );
  }

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\Lock
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\Lock();
    $dataObject->setUserid($this->getUserid())
               ->setRunid($this->getRunid())
               ->setItemid($this->getItemid())
               ->setWebsiteid($this->getWebsiteid())
               ->setType($this->getType())
               ->setStarttime($this->getStarttime())
               ->setLastactivity($this->getLastactivity());
    return $dataObject;
  }
}
