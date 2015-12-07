<?php
namespace Cms\Response\Shortener;

use Cms\Data\Ticket as TicketData;
use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer Shortener createRenderTicket
 *
 * @package      Cms
 * @subpackage   Response
 */
class Ticket implements IsResponseData
{
  /**
   * @var string
   */
  public $id = null;

  /**
   * @var string
   */
  public $url = null;

  /**
   * @var boolean
   */
  public $protect = null;

  /**
   * @var array
   */
  public $credentials = null;

  /**
   * @var integer
   */
  public $ticketLifetime = null;

  /**
   * @var integer
   */
  public $sessionLifetime = null;

  /**
   * @var integer
   */
  public $remainingCalls = null;
  
  /**
   * @param array $lockState
   */
  public function __construct(array $ticketData)
  {
    $this->setValuesFromData($ticketData);
  }
  
  /**
   * @param string $id
   */
  public function setId($id)
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
   * @param string $url
   */
  public function setUrl($url)
  {
    $this->url = $url;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
  
  /**
   * @param boolean $protect
   */
  public function setProtect($protect)
  {
    $this->protect = ($protect ? true : false);
    return $this;
  }
  
  /**
   * @return boolean
   */
  public function getProtect()
  {
    return $this->protect;
  }

  /**
   * @param array $credentials
   */
  public function setCredentials($credentials)
  {
    $this->credentials = $credentials;
    return $this;
  }

  /**
   * @return array
   */
  public function getCredentials()
  {
    return $this->credentials;
  }

  /**
   * @param integer $ticketLifetime
   */
  public function setTicketLifetime($ticketLifetime)
  {
    $this->ticketLifetime = $ticketLifetime;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getTicketLifetime()
  {
    return $this->ticketLifetime;
  }

  /**
   * @param integer $sessionLifetime
   */
  public function setSessionLifetime($sessionLifetime)
  {
    $this->sessionLifetime = $sessionLifetime;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getSessionLifetime()
  {
    return $this->sessionLifetime;
  }

  /**
   * @param integer $remainingCalls
   */
  public function setRemainingCalls($remainingCalls)
  {
    $this->remainingCalls = $remainingCalls;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getRemainingCalls()
  {
    return $this->remainingCalls;
  }

  /**
   * @param Cms\Data\User $data
   */
  protected function setValuesFromData(array $ticketData)
  {
    $credentials = $ticketData['ticket']->decode('credentials');
    
    $this->setId($ticketData['ticket']->getId())
         ->setUrl($ticketData['ticketUrl'])
         ->setProtect((is_array($credentials) && !empty($credentials) ? true : false))
         ->setCredentials($credentials)
         ->setTicketLifetime($ticketData['ticket']->getTicketlifetime())
         ->setSessionLifetime($ticketData['ticket']->getSessionlifetime())
         ->setRemainingCalls($ticketData['ticket']->getRemainingcalls());
  }
}
