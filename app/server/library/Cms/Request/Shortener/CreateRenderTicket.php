<?php
namespace Cms\Request\Shortener;

use Cms\Request\Base;

/**
 * CreateRenderTicket Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class CreateRenderTicket extends Base
{
  /**
   * @var string
   */
  private $websiteId = null;
  
  /**
   * @var string
   */
  private $type = null;
  
  /**
   * @var string
   */
  private $id = null;
  
  /**
   * @var boolean
   */
  private $protect = true;
  
  /**
   * @var array
   */
  private $credentials = null;
  
  /**
   * @var integer
   */
  private $ticketLifetime = null;
  
  /**
   * @var integer
   */
  private $sessionLifetime = null;
  
  /**
   * @var integer
   */
  private $remainingCalls = null;
  
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }
  
  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @param string $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }
  
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  
  /**
   * @return boolean
   */
  public function getProtect()
  {
    return $this->protect;
  }

  /**
   * @param boolean $protect
   */
  public function setProtect($protect)
  {
    $this->protect = $protect;
  }
  
  /**
   * @return array
   */
  public function getCredentials()
  {
    return $this->credentials;
  }

  /**
   * @param array $credentials
   */
  public function setCredentials($credentials)
  {
    $this->credentials = $credentials;
  }
  
  /**
   * @return integer
   */
  public function getTicketLifetime()
  {
    return $this->ticketLifetime;
  }

  /**
   * @param integer $ticketLifetime
   */
  public function setTicketLifetime($ticketLifetime)
  {
    $this->ticketLifetime = $ticketLifetime;
  }
  
  /**
   * @return integer
   */
  public function getSessionLifetime()
  {
    return $this->sessionLifetime;
  }

  /**
   * @param integer $sessionLifetime
   */
  public function setSessionLifetime($sessionLifetime)
  {
    $this->sessionLifetime = $sessionLifetime;
  }
  
  /**
   * @return integer
   */
  public function getRemainingCalls()
  {
    return $this->remainingCalls;
  }

  /**
   * @param integer $remainingCalls
   */
  public function setRemainingCalls($remainingCalls)
  {
    $this->remainingCalls = $remainingCalls;
  }

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setType($this->getRequestParam('type'));
    $this->setId($this->getRequestParam('id'));
    if ($this->getRequestParam('protect') !== null) {
      $this->setProtect($this->getRequestParam('protect'));
    }
    if ($this->getRequestParam('credentials') !== null) {
      $this->setCredentials($this->getRequestParam('credentials'));
    }
    if ($this->getRequestParam('ticketlifetime') !== null) {
      $this->setTicketLifetime($this->getRequestParam('ticketlifetime'));
    }
    if ($this->getRequestParam('sessionlifetime') !== null) {
      $this->setSessionLifetime($this->getRequestParam('sessionlifetime'));
    }
    if ($this->getRequestParam('remainingcalls') !== null) {
      $this->setRemainingCalls($this->getRequestParam('remainingcalls'));
    }
  }
}
