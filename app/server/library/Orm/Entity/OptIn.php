<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Orm\Entity\OptIn
 */
class OptIn
{
  const MODE_REGISTER = 'register';
  const MODE_PASSWORD = 'password';
  
  /**
   * @var string $userid
   */
  private $userid;

  /**
   * @var string $code
   */
  private $code;

  /**
   * @var datetime $timestamp
   */
  private $timestamp;

  /**
   * @var string $mode
   */
  private $mode;

  /**
   * @var \Cms\Data\User
   */
  private $user;

  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('user_opt_in');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\OptInRepository');
    
    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'userid',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'id' => true,
      'unique' => true,
      'fieldName' => 'code',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'timestamp',
      'type' => 'datetime',
    ));
    $metadata->mapField(array(
      'fieldName' => 'mode',
      'type' => 'string',
      'length' => 100,
    ));
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
   * Set code
   *
   * @param string $code
   */
  public function setCode($code)
  {
    $this->code = $code;
  }

  /**
   * Get code
   *
   * @return string
   */
  public function getCode()
  {
    return $this->code;
  }

  /**
   * Set timestamp
   *
   * @param datetime $timestamp
   */
  public function setTimestamp($timestamp)
  {
    $this->timestamp = $timestamp;
  }

  /**
   * Get timestamp
   *
   * @return datetime
   */
  public function getTimestamp()
  {
    return $this->timestamp;
  }

  /**
   * Get mode
   *
   * @return string
   */
  public function getMode()
  {
    return $this->mode;
  }

  /**
   * Set user
   *
   * @param \Cms\Data\User $user
   */
  public function setUser(\Cms\Data\User $user)
  {
    $this->user = $user;
  }

  /**
   * Get user
   *
   * @return \Cms\Data\User
   */
  public function getUser()
  {
    return $this->user;
  }
  /**
   * @param string $mode
   */
  public function setMode($mode)
  {
    if (!in_array($mode, array(self::MODE_REGISTER, self::MODE_PASSWORD))) {
      throw new \InvalidArgumentException('Invalid mode');
    }
    $this->mode = $mode;
  }
}
