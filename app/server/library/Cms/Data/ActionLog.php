<?php
namespace Cms\Data;

/**
 * ActionLog
 *
 * @package      Cms
 * @subpackage   Data
 */
class ActionLog
{
  /**
   * @var integer
   */
  private $timestamp;
  /**
   * @var string
   */
  private $websiteid;
  /**
   * @var string
   */
  private $id;
  /**
   * @var string
   */
  private $name;
  /**
   * @var string
   */
  private $userlogin;
  /**
   * @var string
   */
  private $action;
  /**
   * @var string
   */
  private $additionalinfo;
  
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
   * Set id
   *
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * Get id
   *
   * @return string $id
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
    return $this;
  }

  /**
   * Get name
   *
   * @return string $name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set timestamp
   *
   * @param string $timestamp
   */
  public function setTimestamp($timestamp)
  {
    $this->timestamp = $timestamp;
    return $this;
  }
  
  /**
   * Get timestamp
   *
   * @return string $timestamp
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
    return $this;
  }

  /**
   * Get userlogin
   *
   * @return string $userlogin
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
    return $this;
  }

  /**
   * Get action
   *
   * @return string $action
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
}
