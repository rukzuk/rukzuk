<?php
namespace Cms\Data;

/**
 * @package      Cms
 * @subpackage   Data
 */
class PublisherStatus
{
  const STATUS_UNKNOWN      = 'UNKNOWN';
  const STATUS_INIT         = 'INIT';
  const STATUS_INPROGRESS   = 'INPROGRESS';
  const STATUS_FINISHED     = 'FINISHED';
  const STATUS_FAILED       = 'FAILED';
  
  /**
   * @var string
   */
  private $id;
  /**
   * @var string
   */
  private $status;
  /**
   * @var integer
   */
  private $timestamp;
  /**
   * @var number
   */
  private $percent;
  /**
   * @var number
   */
  private $remaining;
  /**
   * @var string
   */
  private $msg;

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
    $this->setId();
    $this->setStatus();
    $this->setTimestamp();
    $this->setPercent();
    $this->setRemaining();
    $this->setMsg();
    return $this;
  }

  /**
   * @param string $id
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
   * @param string $status
   */
  public function setStatus($status = null)
  {
    $this->status = $status;
    return $this;
  }
  /**
   * @return string
   */
  public function getStatus()
  {
    return $this->status;
  }
  /**
   * @param integer $timestamp
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
   * @param number $percent
   */
  public function setPercent($percent = null)
  {
    $this->percent = $percent;
    return $this;
  }
  /**
   * @return number
   */
  public function getPercent()
  {
    return $this->percent;
  }
  /**
   * @param number $remaining
   */
  public function setRemaining($remaining = null)
  {
    $this->remaining = $remaining;
    return $this;
  }
  /**
   * @return number
   */
  public function getRemaining()
  {
    return $this->remaining;
  }
  /**
   * @param string $msg
   */
  public function setMsg($msg = null)
  {
    $this->msg = $msg;
    return $this;
  }
  /**
   * @return string
   */
  public function getMsg()
  {
    return $this->msg;
  }
  
  /**
   * @return boolean
   */
  public function isPublishing()
  {
    $status = $this->getStatus();
    if ($status == self::STATUS_INIT || $status == self::STATUS_INPROGRESS) {
      return true;
    } else {
      return false;
    }
  }

  public function toArray()
  {
    return array(
      'id'            => $this->getId(),
      'status'        => $this->getStatus(),
      'timestamp'     => $this->getTimestamp(),
      'percent'       => $this->getPercent(),
      'remaining'     => $this->getRemaining(),
      'msg'           => $this->getMsg(),
    );
  }

  public function setFromArray($values)
  {
    $this->clear();
    
    if (isset($values['id'])) {
      $this->setId($values['id']);
    }
    if (isset($values['status'])) {
      $this->setStatus($values['status']);
    }
    if (isset($values['timestamp'])) {
      $this->setTimestamp($values['timestamp']);
    }
    if (isset($values['percent'])) {
      $this->setPercent($values['percent']);
    }
    if (isset($values['remaining'])) {
      $this->setRemaining($values['remaining']);
    }
    if (isset($values['msg'])) {
      $this->setMsg($values['msg']);
    }
    return $this;
  }
}
