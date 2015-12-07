<?php
namespace Cms\Data;

use Seitenbau\Registry as Registry;

/**
 * Lock Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
 */

class Lock
{
  /**
   * @var string $websiteid
   */
  private $websiteid;

  /**
   * @var string $userid
   */
  private $userid = '';

  /**
   * @var string $runid
   */
  private $runid = '';

  /**
   * @var string $itemid
   */
  private $itemid = '';

  /**
   * @var string $type
   */
  private $type = '';

  /**
   * @var int $starttime
   */
  private $starttime = '';

  /**
   * @var int $lastactivity
   */
  private $lastactivity = '';


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
   * @param string $itemid
   */
  public function setItemid($itemid)
  {
    $this->itemid = $itemid;
    return $this;
  }

  /**
   * Get name
   *
   * @return string $itemid
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
   * @return boolean
   */
  public function isExpired()
  {
    return ($this->getLastactivity() < time()-(int)Registry::getConfig()->lock->lifetime);
  }
  

  /**
   * Liefert alle Columns und deren Values
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'websiteId' => $this->getWebsiteid(),
      'userId' => $this->getUserid(),
      'runId' => $this->getRunid(),
      'itemId' => $this->getItemid(),
      'type' => $this->getType(),
      'starttime' => $this->getStarttime(),
      'lastactivity' => $this->getLastactivity(),
      'isExpired' => $this->isExpired(),
    );
  }
}
