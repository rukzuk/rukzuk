<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Orm\Entity\UserStatus
 */
class UserStatus
{
  /**
   * @var string $userid
   */
  private $userid;

  /**
   * @var string $authbackend
   */
  private $authbackend;

  /**
   * @var int $lastlogin
   */
  private $lastlogin;

  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('user_status');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\UserStatusRepository');

    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'userid',
      'type' => 'string',
      'length' => 255,
    ));

    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'authbackend',
      'type' => 'string',
      'length' => 100,
    ));

    $metadata->mapField(array(
      'fieldName' => 'lastlogin',
      'type' => 'datetime',
    ));
  }

  /**
   * Set userid
   *
   * @param string $userid
   */
  public function setUserId($userid)
  {
    $this->userid = $userid;
  }

  /**
   * Get userid
   *
   * @return string
   */
  public function getUserId()
  {
    return $this->userid;
  }

  /**
   * @return string
   */
  public function getAuthbackend()
  {
    return $this->authbackend;
  }

  /**
   * @param string $authbackend
   */
  public function setAuthbackend($authbackend)
  {
    $this->authbackend = $authbackend;
  }

  /**
   * Get lastlogin
   *
   * @return int
   */
  public function getLastlogin()
  {
    return $this->lastlogin;
  }

  /**
   * Set lastlogin
   *
   * @param int $lastlogin
   */
  public function setLastlogin($lastlogin)
  {
    $this->lastlogin = $lastlogin;
  }

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\UserStatus
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\UserStatus();
    $dataObject->setLastLogin($this->getLastlogin());
    $dataObject->setUserId($this->getUserId());
    $dataObject->setAuthBackend($this->getAuthbackend());
    return $dataObject;
  }
}
