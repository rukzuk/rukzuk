<?php
namespace Cms\Business;

use Seitenbau\Registry;
use Cms\Exception as CmsException;
use Cms\Business\Shortener\LoginException;
use Cms\Business\Shortener\InvalidTicketException;
use \Cms\Access\AuthBackend\Type\TicketAuthBackend as TicketAuthBackend;

/**
 * Shortener
 *
 * @package      Cms
 * @subpackage   Business
 */
class Shortener extends Base\Service
{
  /**
   * Bearbeitet ein erhaltenes Ticket
   *
   * @param string  $websiteId
   * @param array $properties
   */
  public function processTicket($ticketId, array $credentials)
  {
    // init
    $ticketBusiness = $this->getBusiness('Ticket');
    $accessManager = $this->getAccessManager();

    if (!$ticketBusiness->existsTicket($ticketId)) {
      throw new InvalidTicketException(2100, __METHOD__, __LINE__);
    }
      
    $ticket = $ticketBusiness->getbyId($ticketId);
    $ticketBusiness->decreaseCalls($ticketId);
    if (!$ticketBusiness->isValid($ticket)) {
      $ticketBusiness->deleteInvalidTickets();
      throw new InvalidTicketException(2100, __METHOD__, __LINE__);
    }
    
    try {
      $accessManager->login('ticketuser', array(
        'ticketid'    => $ticketId,
        'credentials' => $credentials,
      ));
    } catch (\Exception $e) {
      throw new LoginException(2100, __METHOD__, __LINE__, array(), $e);
    }

    return $ticket;
  }
  
  /**
   * @param  string   $websiteId
   * @param  string   $type
   * @param  string   $id
   * @param  boolean  $protect
   * @param  array    $credentials
   * @param  integer  $ticketLifetime
   * @param  integer  $sessionLifetime
   * @param  integer  $remainingCalls
   * @return array    @see \Cms\Business\Ticket::createTicket
  */
  public function createRenderTicket(
      $websiteId,
      $type,
      $id,
      $protect = true,
      $credentials = null,
      $ticketLifetime = null,
      $sessionLifetime = null,
      $remainingCalls = null
  ) {
    // init
    $ticketBusiness = $this->getBusiness('Ticket');
    
    // Request-Daten zusammenstellen
    if ($type == 'template') {
      // Template rendern
      $requestConfig = array(
        'controller' => 'render',
        'action' => 'template',
        'params' => array(
          Registry::getConfig()->request->parameter => \Seitenbau\Json::encode(array(
            'websiteid' => $websiteId,
            'templateid' => $id,
            'mode' => 'preview',
          ))
        ),
      );
    } elseif ($type == 'page') {
      // Page rendern
      $requestConfig = array(
        'controller' => 'render',
        'action' => 'page',
        'params' => array(
          Registry::getConfig()->request->parameter => \Seitenbau\Json::encode(array(
            'websiteid' => $websiteId,
            'pageid' => $id,
            'mode' => 'preview',
          ))
        ),
      );
    } else {
      // Fehlerhafter Render-Typ
      throw new CmsException(2101, __METHOD__, __LINE__, array('type'=>$type));
    }
    
    // Soll das Ticket geschuetzt sein?
    if (!$protect && isset($credentials)) {
      // Zugangsdaten zuruecksetzen
      unset($credentials);
    }

    // Ticket erstellen und zurueckgeben
    return $ticketBusiness->createTicket(
        $websiteId,
        true, // Redirect
        true, // Get-Request
        $requestConfig,
        $ticketLifetime,
        $remainingCalls,
        $sessionLifetime,
        $credentials
    );
  }
  
  /**
   * Pruefung, ob der angemeldete User die Rechte fuer die aufgerufene Aktion
   * besitzt
   *
   * @param array  $identity
   * @param string $rightname Name des Rechts, auf das geprueft wird
   * @param mixed  $check
   * @return boolean
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // Superuser darf alles
    if ($this->isSuperuser($identity)) {
      return true;
    }
    
    switch ($rightname)
    {
      // Ticket-Action darf generell aufgerufen werden
      case 'ticket':
            return true;
        break;
      
      // Benutzer muss in einer Gruppe dieser Website sein
      case 'createrenderticket':
            return $this->isUserInAnyWebsiteGroup($identity, $check['websiteId']);
        break;
    }
    
    // Default: Keine Rechte
    return false;
  }
}
