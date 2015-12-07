<?php
namespace Cms\Response;

use Cms\Data;
use Seitenbau\Registry;
use Cms\Response\IsResponseData;

/**
 * @package      Cms
 * @subpackage   Response
 */
class Build implements IsResponseData
{
  /**
   * @var string
   */
  public $id;
  /**
   * @var integer
   */
  public $version;
  /**
   * @var integer
   */
  public $timestamp;
  /**
   * @var string
   */
  public $comment;
  /**
   * @var array
   */
  public $creatorName;
  /**
   * @var \Cms\Response\PublishedStatus
   */
  public $lastPublished;
  
  /**
   * @param Cms\Data\Build $build
   */
  public function __construct(Data\Build $build)
  {
    $this->setValuesFromData($build);
  }
  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @param integer $version
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }
  /**
   * @param integer $timestamp
   */
  public function setTimestamp($timestamp)
  {
    $this->timestamp = $timestamp;
  }
  /**
   * @param string $comment
   */
  public function setComment($comment)
  {
    $this->comment = $comment;
  }
  /**
   * @param string $creatorName
   */
  public function setCreatorName($creatorName)
  {
    $this->creatorName = $creatorName;
  }
  /**
   * @param string $creatorVersion
   */
  /**
   * @param string $lastPublished
   */
  public function setLastPublished($lastPublished)
  {
    $this->lastPublished = new \Cms\Response\PublishedStatus($lastPublished);
  }

  /**
   * @param Cms\Data\Build $data
   */
  protected function setValuesFromData(Data\Build $data)
  {
    $this->setId($data->getId());
    $this->setVersion($data->getVersion());
    $this->setTimestamp($data->getTimestamp());
    $this->setComment($data->getComment());
    $this->setCreatorName($data->getCreatorName());
    $this->setLastPublished($data->getLastPublished());
  }
}
