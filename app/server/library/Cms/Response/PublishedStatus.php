<?php
namespace Cms\Response;

use Cms\Data;
use Cms\Response\IsResponseData;

/**
 * @package      Cms
 * @subpackage   Response
 */
class PublishedStatus implements IsResponseData
{
  /**
   * @var string
   */
  public $id;
  /**
   * @var string
   */
  public $status;
  /**
   * @var integer
   */
  public $timestamp;
  /**
   * @var number
   */
  public $percent;
  /**
   * @var number
   */
  public $remaining;
  /**
   * @var string
   */
  public $msg;
  
  /**
   * @param Cms\Data\PublisherStatus $publishedStatus
   */
  public function __construct(Data\PublisherStatus $publishedStatus)
  {
    $this->setValuesFromData($publishedStatus);
  }
  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @param string $status
   */
  public function setStatus($status)
  {
    $this->status = $status;
  }
  /**
   * @param integer $timestamp
   */
  public function setTimestamp($timestamp)
  {
    $this->timestamp = $timestamp;
  }
  /**
   * @param number $percent
   */
  public function setPercent($percent)
  {
    $this->percent = $percent;
  }
  /**
   * @param number $remaining
   */
  public function setRemaining($remaining)
  {
    $this->remaining = $remaining;
  }
  /**
   * @param string $msg
   */
  public function setMsg($msg)
  {
    $this->msg = $msg;
  }

  /**
   * @param Cms\Data\PublisherStatus $publishedStatus
   */
  protected function setValuesFromData(Data\PublisherStatus $publishedStatus)
  {
    $this->setId($publishedStatus->getId());
    $this->setStatus($publishedStatus->getStatus());
    $this->setTimestamp($publishedStatus->getTimestamp());
    $this->setPercent($publishedStatus->getPercent());
    $this->setRemaining($publishedStatus->getRemaining());
    $this->setMsg($publishedStatus->getMsg());
  }
}
