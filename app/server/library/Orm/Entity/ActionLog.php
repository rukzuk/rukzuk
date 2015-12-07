<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Orm\Entity\ActionLog
 */
class ActionLog
{
  /**
   * @var integer $logid
   */
  private $logid;

  /**
   * @var string $websiteid
   */
  private $websiteid;

  /**
   * @var string $id
   */
  private $id;

  /**
   * @var string $name
   */
  private $name;

  /**
   * @var string $timestamp
   */
  private $timestamp;

  /**
   * @var string $userlogin
   */
  private $userlogin;

  /**
   * @var string $action
   */
  private $action;

  /**
   * @var string $additionalinfo
   */
  private $additionalinfo;

  /**
   * @param Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('action_log');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    $metadata->setCustomRepositoryClass('Orm\Repository\ActionLogRepository');
    
    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'logid',
      'type' => 'integer',
      'generatedValue' => true
    ));
    $metadata->mapField(array(
      'fieldName' => 'websiteid',
      'type' => 'string',
      'length' => 100
    ));
    $metadata->mapField(array(
      'fieldName' => 'id',
      'type' => 'string',
      'length' => 100
    ));
    $metadata->mapField(array(
      'fieldName' => 'name',
      'type' => 'string',
      'length' => 255
    ));
    $metadata->mapField(array(
      'fieldName' => 'timestamp',
      'type' => 'string',
      'length' => 20
    ));
    $metadata->mapField(array(
      'fieldName' => 'userlogin',
      'type' => 'string',
      'length' => 255
    ));
    $metadata->mapField(array(
      'fieldName' => 'action',
      'type' => 'string',
      'length' => 255
    ));
    $metadata->mapField(array(
      'fieldName' => 'additionalinfo',
      'type' => 'text',
      'nullable' => true,
    ));
  }

  /**
   * Get logid
   *
   * @return integer
   */
  public function getLogid()
  {
    return $this->logid;
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
   * Get timestamp
   *
   * @return string
   */
  public function getTimestamp()
  {
    return $this->timestamp;
  }

  /**
   * Set userlogin
   *
   * @param string $userlogin
   */
  public function setUserlogin($userlogin)
  {
    $this->userlogin = $userlogin;
  }

  /**
   * Get userlogin
   *
   * @return string
   */
  public function getUserlogin()
  {
    return $this->userlogin;
  }

  /**
   * Set action
   *
   * @param string $action
   */
  public function setAction($action)
  {
    $this->action = $action;
  }

  /**
   * Get action
   *
   * @return string
   */
  public function getAction()
  {
    return $this->action;
  }

  /**
   * Set additional information
   *
   * @param string $additionalinfo
   */
  public function setAdditionalinfo($additionalinfo)
  {
    $this->additionalinfo = $additionalinfo;
  }

  /**
   * Get additional information
   *
   * @return string
   */
  public function getAdditionalinfo()
  {
    return $this->additionalinfo;
  }

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\ActionLog
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\ActionLog();
    $dataObject->setTimestamp($this->getTimestamp())
               ->setWebsiteid($this->getWebsiteid())
               ->setId($this->getId())
               ->setName($this->getName())
               ->setUserlogin($this->getUserlogin())
               ->setAction($this->getAction())
               ->setAdditionalinfo($this->getAdditionalinfo());
    
    return $dataObject;
  }
}
