<?php
namespace Cms\Data;

/**
 * Ticket Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
 */

class Ticket
{
  /**
   * @var string $id
   */
  private $id;

  /**
   * @var integer $timestamp
   */
  private $timestamp = '';

  /**
   * @var string $websiteid
   */
  private $websiteid;

  /**
   * @var boolean $isredirect
   */
  private $isredirect = true;

  /**
   * @var boolean $isget
   */
  private $isget = true;

  /**
   * @var string $requestconfig
   */
  private $requestconfig = '';

  /**
   * @var integer $ticketlifetime
   */
  private $ticketlifetime = 0;

  /**
   * @var integer $remainingcalls
   */
  private $remainingcalls = 0;

  /**
   * @var integer $sessionlifetime
   */
  private $sessionlifetime = 0;

  /**
   * @var string $credentials
   */
  private $credentials = '';

  /**
   * Get id
   *
   * @return int $id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set id
   *
   * @param int $id
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * Get timestamp
   *
   * @return int $timestamp
   */
  public function getTimestamp()
  {
    return $this->timestamp;
  }

  /**
   * Set timestamp
   *
   * @param int $timestamp
   */
  public function setTimestamp($timestamp)
  {
    $this->timestamp = $timestamp;
    return $this;
  }

  /**
   * Get website id
   *
   * @return string $websiteid
   */
  public function getWebsiteid()
  {
    return $this->websiteid;
  }

  /**
   * Set website id
   *
   * @param string $websiteid
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
    return $this;
  }

  /**
   * Is redirect
   *
   * @return boolean $isredirect
   */
  public function isRedirect()
  {
    return $this->isredirect;
  }

  /**
   * Set redirect
   *
   * @param boolean $isredirect
   */
  public function setIsredirect($isredirect)
  {
    $this->isredirect = ($isredirect ? true : false);
    return $this;
  }

  /**
   * Is GET redirect
   *
   * @return boolean $isget
   */
  public function isGet()
  {
    return $this->isget;
  }

  /**
   * Set GET redirect
   *
   * @param boolean $isget
   */
  public function setIsget($isget)
  {
    $this->isget = ($isget ? true : false);
    return $this;
  }

  /**
   * Get requestconfig
   *
   * @return string $requestconfig
   */
  public function getRequestconfig()
  {
    return $this->requestconfig;
  }

  /**
   * Set requestconfig
   *
   * @param string $requestconfig
   */
  public function setRequestconfig($requestconfig)
  {
    $this->requestconfig = $requestconfig;
    return $this;
  }

  /**
   * Get ticket lifetime
   *
   * @return int $ticketlifetime
   */
  public function getTicketlifetime()
  {
    return $this->ticketlifetime;
  }

  /**
   * Set ticket lifetime
   *
   * @param int $ticketlifetime
   */
  public function setTicketlifetime($ticketlifetime)
  {
    $this->ticketlifetime = $ticketlifetime;
    return $this;
  }

  /**
   * Get remaining calls
   *
   * @return int $remainingcalls
   */
  public function getRemainingcalls()
  {
    return $this->remainingcalls;
  }

  /**
   * Set remaining calls
   *
   * @param int $remainingcalls
   */
  public function setRemainingcalls($remainingcalls)
  {
    $this->remainingcalls = $remainingcalls;
    return $this;
  }

  /**
   * Get session lifetime
   *
   * @return int $sessionLifetime
   */
  public function getSessionlifetime()
  {
    return $this->sessionLifetime;
  }

  /**
   * Set session lifetime
   *
   * @param int $sessionLifetime
   */
  public function setSessionlifetime($sessionLifetime)
  {
    $this->sessionLifetime = $sessionLifetime;
    return $this;
  }

  /**
   * Get credentials
   *
   * @return int $credentials
   */
  public function getCredentials()
  {
    return $this->credentials;
  }

  /**
   * Set credentials
   *
   * @param int $sessionLifetime
   */
  public function setCredentials($credentials)
  {
    $this->credentials = $credentials;
    return $this;
  }

  /**
   * Get internal redirect contoller
   *
   * @return int $credentials
   */
  public function getInternalController()
  {
    $internalRedirect = $this->decode('requestconfig');
    if (isset($internalRedirect['controller'])) {
      return $internalRedirect['controller'];
    }
    return '';
  }

  /**
   * Get internal redirect action
   *
   * @return int $credentials
   */
  public function getInternalAction()
  {
    $internalRedirect = $this->decode('requestconfig');
    if (isset($internalRedirect['action'])) {
      return $internalRedirect['action'];
    }
    return '';
  }

  /**
   * * Get internal redirect params
   *
   * @return int $credentials
   */
  public function getInternalParams()
  {
    $internalRedirect = $this->decode('requestconfig');
    if (isset($internalRedirect['params'])) {
      return $internalRedirect['params'];
    }
    return '';
  }

  /**
   * Liefert alle Columns und deren Values
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'id' => $this->getId(),
      'timestamp' => $this->getTimestamp(),
      'websiteId' => $this->getWebsiteId(),
      'isRedirect' => $this->isRedirect(),
      'isGet' => $this->isGet(),
      'requestConfig' => $this->getRequestconfig(),
      'ticketLifetime' => $this->getTicketlifetime(),
      'remainingCalls' => $this->getRemainingcalls(),
      'sessionLifetime' => $this->getSessionlifetime(),
      'credentials' => $this->getCredentials(),
    );
  }

  public function decode($fieldName)
  {
    $functionName = 'get' . ucfirst($fieldName);
    $value = $this->$functionName();

    // Wert vorhanden
    if (!empty($value)) {
      try {
        // Wert Normal decodieren
        return \Seitenbau\Json::decode($value, \Zend_Json::TYPE_ARRAY);
      } catch (Exception $e) {
      // Fehler -> Keine Daten zurueckgeben
      }
    }
    return;
  }
}
