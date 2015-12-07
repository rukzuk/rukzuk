<?php
namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Orm\Entity\Ticket
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
  private $isredirect;

  /**
   * @var boolean $isget
   */
  private $isget;

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
   * @param Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('ticket');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\TicketRepository');
    
    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'id',
      'type' => 'string',
      'length' => 100,
    ));
    
    $metadata->mapField(array(
      'fieldName' => 'timestamp',
      'type' => 'integer',
      'length' => 11,
    ));
    
    $metadata->mapField(array(
      'fieldName' => 'websiteid',
      'type' => 'string',
      'length' => 100,
    ));
    
    $metadata->mapField(array(
      'fieldName' => 'isredirect',
      'type' => 'boolean',
    ));

    $metadata->mapField(array(
      'fieldName' => 'isget',
      'type' => 'boolean',
    ));
    
    $metadata->mapField(array(
      'fieldName' => 'requestconfig',
      'type' => 'text',
    ));
    
    $metadata->mapField(array(
      'fieldName' => 'ticketlifetime',
      'type' => 'integer',
    ));
    
    $metadata->mapField(array(
      'fieldName' => 'remainingcalls',
      'type' => 'integer',
    ));
    
    $metadata->mapField(array(
      'fieldName' => 'sessionlifetime',
      'type' => 'integer',
      'nullable' => true,
    ));
    
    $metadata->mapField(array(
      'fieldName' => 'credentials',
      'type' => 'text',
      'nullable' => true,
    ));
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
   * Set id
   *
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
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
    $this->isredirect = $isredirect;
  }

  /**
   * Is GET request
   *
   * @return boolean $isget
   */
  public function isGet()
  {
    return $this->isget;
  }

  /**
   * Set GET request
   *
   * @param boolean $isget
   */
  public function setIsget($isget)
  {
    $this->isget = $isget;
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
   * @param int $remainingCalls
   */
  public function setRemainingcalls($remainingcalls)
  {
    $this->remainingcalls = $remainingcalls;
  }

  /**
   * Get session lifetime
   *
   * @return int $sessionlifetime
   */
  public function getSessionlifetime()
  {
    return $this->sessionlifetime;
  }

  /**
   * Set session lifetime
   *
   * @param int $sessionlifetime
   */
  public function setSessionlifetime($sessionlifetime)
  {
    $this->sessionlifetime = $sessionlifetime;
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
  
  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\Ticket
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\Ticket();
    $dataObject->setId($this->getId())
               ->setTimestamp($this->getTimestamp())
               ->setWebsiteid($this->getWebsiteid())
               ->setIsredirect($this->isRedirect())
               ->setIsget($this->isGet())
               ->setRequestconfig($this->getRequestconfig())
               ->setTicketlifetime($this->getTicketlifetime())
               ->setRemainingcalls($this->getRemainingcalls())
               ->setSessionlifetime($this->getSessionlifetime())
               ->setCredentials($this->getCredentials());
    return $dataObject;
  }
}
