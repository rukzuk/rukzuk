<?php
namespace Cms\Service;

use Cms\Service\Base\Dao as DaoServiceBase;
use Cms\Exception as CmsException;
use Seitenbau\Registry as Registry;

/**
 * Ticket
 *
 * @package      Cms
 * @subpackage   Service
 */
class Ticket extends DaoServiceBase
{
  /**
   * @param  string   $websiteId
   * @param  boolean  $isRedirect
   * @param  boolean  $isGet
   * @param  string   $requestConfig
   * @param  integer  $ticketLifetime
   * @param  integer  $remainingCalls
   * @param  integer  $sessionLifetime
   * @param  array    $credentials
   * @param  array    $additionalTicketParams
   * @param  bool     $useInternalUrl
   * @return array    'ticket' => Cms\Data\Ticket, 'ticketUrl' => Ticket-Url
  */
  public function createTicket(
      $websiteId,
      $isRedirect,
      $isGet,
      $requestConfig,
      $ticketLifetime = null,
      $remainingCalls = null,
      $sessionLifetime = null,
      $credentials = null,
      $additionalTicketParams = null,
      $useInternalUrl = false
  ) {
    // Alte Tickets entfernen
    $this->execute('deleteInvalidTickets', array());
    
    // Default-Werte setzen
    $config = Registry::getConfig();
    if (!isset($ticketLifetime)) {
      $ticketLifetime = $config->accessticket->ticketLifetime;
    }
    if (!isset($remainingCalls)) {
      $remainingCalls = $config->accessticket->remainingCalls;
    }
    
    // evtl. Schutz umformen
    if (isset($credentials)) {
      if (is_array($credentials)) {
        $credentials = \Zend_Json::encode($credentials);
      } elseif (!is_scalar($credentials)) {
        throw new CmsException(1307, __METHOD__, __LINE__);
      }
    }

    // Neues Ticket erstellen
    $ticket = $this->execute('create', array($websiteId, $isRedirect, $isGet,
      $requestConfig, $ticketLifetime, $remainingCalls, $sessionLifetime, $credentials));
    
    // evtl. Ticket-Parameter umformen
    if (isset($additionalTicketParams)) {
      if (is_array($additionalTicketParams)) {
        $additionalTicketParams = \Zend_Json::encode($additionalTicketParams);
      } elseif (!is_scalar($additionalTicketParams)) {
        throw new CmsException(1301, __METHOD__, __LINE__);
      }
      if (!empty($additionalTicketParams)) {
        $additionalTicketParams =
          Registry::getConfig()->request->parameter . '/' .
          urlencode($additionalTicketParams);
      }
    }
    
    // Basisurl uebernehmen, Ticket ID und weitere Parameter setzen
    $baseUrl = Registry::getBaseUrl($useInternalUrl);
    $ticketUrl = $baseUrl.$config->server->url.$config->accessticket->url;
    $ticketUrl = str_replace(':ticket', $ticket->getId(), $ticketUrl);
    $ticketUrl = str_replace(':params', $additionalTicketParams, $ticketUrl);
    
    // Ticket und Ticket-Url zurueckgeben
    return array(
      'ticket' => $ticket,
      'ticketUrl' => $ticketUrl
    );
  }
    
  /**
   * @param  string   $websiteId
   * @param  boolean  $isRedirect
   * @param  boolean  $isGet
   * @param  string   $requestConfig
   * @param  integer  $ticketLifetime
   * @param  integer  $remainingCalls
   * @param  integer  $sessionLifetime
   * @param  array    $credentials
   * @param  array    $additionalTicketParams
   * @param  bool     $useInternalUrl
   * @return string   Ticket-Url
  */
  public function createTicketUrl(
      $websiteId,
      $isRedirect,
      $isGet,
      $requestConfig,
      $ticketLifetime = null,
      $remainingCalls = null,
      $sessionLifetime = null,
      $credentials = null,
      $additionalTicketParams = null,
      $useInternalUrl = false
  ) {
    
    $ticketData = $this->createTicket(
        $websiteId,
        $isRedirect,
        $isGet,
        $requestConfig,
        $ticketLifetime,
        $remainingCalls,
        $sessionLifetime,
        $credentials,
        $additionalTicketParams,
        $useInternalUrl
    );

    // Ticket-Url zurueckgeben
    return $ticketData['ticketUrl'];
  }

  /**
   * Gibt ein bestimmtes Ticket zurueck
   *
   * @param string  $ticketId
   */
  public function getById($ticketId)
  {
    return $this->execute('getById', array($ticketId));
  }

  /**
   * Prueft ob ein bestimmtes Ticket vorhanden ist
   *
   * @param string  $ticketId
   */
  public function existsTicket($ticketId)
  {
    return $this->execute('existsTicket', array($ticketId));
  }

  /**
   * Reduziert den internen Aufrufzaehler
   *
   * @param string  $ticketId
   */
  public function decreaseCalls($ticketId)
  {
    return $this->execute('decreaseCalls', array($ticketId));
  }

  /**
   * Entfernt die nicht mehr gueltigen Tickets
   */
  public function deleteInvalidTickets()
  {
    return $this->execute('deleteInvalidTickets', array());
  }

  /**
   * Prueft ob das uebergebene Ticket gueltig ist
   */
  public function isValid($ticket)
  {
    // Anzahl verfuegbarer Ticket-Abrufe pruefen
    if ($ticket->getRemainingcalls() <= 0) {
      return false;
    }
    
    // Gueltigkeitsdauer des Tickets pruefen
    if (time() > ($ticket->getTimestamp() + $ticket->getTicketlifetime())) {
      return false;
    }
    
    // Ticket ist gueltig
    return true;
  }

  /**
   * Prueft ob das uebergebene Ticket gueltig ist
   */
  public function checkCredentials($ticket, $credentials)
  {
    // Zugangsdaten pruefen
    $ticketCredentials = $ticket->getCredentials();
    if (!empty($ticketCredentials)) {
      $ticketCredentials = \Zend_Json::decode($ticketCredentials, \Zend_Json::TYPE_ARRAY);
      if (is_array($ticketCredentials)) {
        // Benutzername pruefen
        if (isset($ticketCredentials['username'])) {
          if (!isset($credentials['username'])
            || $ticketCredentials['username'] != $credentials['username']
          ) {
            return false;
          }
        }
        // Passwort pruefen
        if (isset($ticketCredentials['password'])) {
          if (!isset($credentials['password'])
            || $ticketCredentials['password'] != $credentials['password']
          ) {
            return false;
          }
        }
      }
    }
    
    // Zugangsdaten gueltig
    return true;
  }

  /**
   * @param $controller
   * @param $action
   * @param $params
   */
  public function createRequestConfig($controller, $action, $params)
  {

  }
}
