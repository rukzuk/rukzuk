<?php
namespace Cms\Response;

use \Cms\Data;

/**
 * Einzelner Lock fuer Response-Darstellung
 *
 * @package      Cms
 * @subpackage   Response
 */

class Lock implements IsResponseData
{
  /**
   * @var string
   */
  public $websiteid = null;

  /**
   * @var string
   */
  public $userid = null;

  /**
   * @var string
   */
  public $runid = null;

  /**
   * @var string
   */
  public $id = null;

  /**
   * @var string
   */
  public $type = null;

  /**
   * @var int
   */
  public $starttime = null;

  /**
   * @var int
   */
  public $lastactivity = null;

  
  /**
   * @param \Orm\Entity\Lock $data
   */
  public function __construct(Data\Lock $data = null)
  {
    if ($data !== null) {
      $this->setValuesFromData($data);
    }
  }


  /**
   * Set websiteid
   *
   * @param string $websiteid
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
    return $this;
  }

  /**
   * Get websiteid
   *
   * @return string $websiteid
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
    return $this;
  }

  /**
   * Get userid
   *
   * @return string $userid
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
    return $this;
  }

  /**
   * Get runid
   *
   * @return string $runid
   */
  public function getRunid()
  {
    return $this->runid;
  }

  /**
   * Set name
   *
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * Get name
   *
   * @return string $id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set type
   *
   * @param string $type
   */
  public function setType($type)
  {
    $this->type = $type;
    return $this;
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
   * Set starttime
   *
   * @param string $starttime
   */
  public function setStarttime($starttime)
  {
    $this->starttime = $starttime;
    return $this;
  }

  /**
   * Get starttime
   *
   * @return string
   */
  public function getStarttime()
  {
    return $this->starttime;
  }

  /**
   * Set lastactivity
   *
   * @param string $lastactivity
   */
  public function setLastactivity($lastactivity)
  {
    $this->lastactivity = $lastactivity;
    return $this;
  }

  /**
   * Get lastactivity
   *
   * @return string
   */
  public function getLastactivity()
  {
    return $this->lastactivity;
  }

    
  /**
   * @param $data
   */
  protected function setValuesFromData(Data\Lock $data)
  {
    $this->setWebsiteid($data->getWebsiteId());
    $this->setUserid($data->getUserid());
    $this->setRunid($data->getRunid());
    $this->setId($data->getItemid());
    $this->setType($data->getType());
    $this->setStarttime($data->getStarttime());
    $this->setLastactivity($data->getLastactivity());
  }
}
