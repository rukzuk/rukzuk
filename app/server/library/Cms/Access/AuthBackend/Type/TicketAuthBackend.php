<?php
namespace Cms\Access\AuthBackend\Type;

use \Cms\Access\AuthBackend\Base as AuthBackendBase;
use \Cms\Access\Acl\Base as AclBase;
use \Seitenbau\Registry as Registry;

/**
 * @package      Cms
 * @subpackage   Access\AuthBackend
 */
class TicketAuthBackend extends AuthBackendBase
{
  const BACKEND_NAME = 'Ticket';

  /**
   * @return  string
   */
  public function getBackendName()
  {
    return self::BACKEND_NAME;
  }

  /**
   * @return  array of \Cms\Controller\Plugin\Base
   */
  public function getControllerPlugins()
  {
    return array(new \Cms\Controller\Plugin\Auth\TicketAuth());
  }

  /**
   * @return  boolean
   */
  protected function validateRegisteredUserOnStart()
  {
    $this->validateRegisteredTicketUser();
  }

  /**
   * @return boolean
   */
  public function logout()
  {
    $identity = $this->getIdentityAsArray();
    if (!$this->isIdentityCreatedFromBackend(self::BACKEND_NAME, $identity)) {
      return false;
    }

    if (isset($identity['tickets'])) {
      unset($identity['tickets']);
    }
    $this->setAuthResultToManager(
        $this->createTicketUserAuthResult($identity, 'Ticket-Logout successful.'),
        true
    );
    return true;
  }

  /**
   * @param   string $identity
   * @param   mixed  $credentials
   *
   * @return  \Cms\Access\Auth\Result
   */
  public function checkLogin($identity, $credentials)
  {
    if ($identity != 'ticketuser' || !is_array($credentials)
      || !isset($credentials['ticketid']) || !isset($credentials['credentials'])
    ) {
      return null;
    }

    $ticketBusiness = new \Cms\Business\Ticket('Ticket');
    if (!$ticketBusiness->existsTicket($credentials['ticketid'])) {
      return null;
    }
    $ticket = $ticketBusiness->getbyId($credentials['ticketid']);
    $ticketBusiness->decreaseCalls($credentials['ticketid']);
    if (!$ticketBusiness->isValid($ticket)) {
      return null;
    }
    if (!$ticketBusiness->checkCredentials($ticket, $credentials['credentials'])) {
      return null;
    }

    return $this->addTicketUserToAuthResult($ticket);
  }

  protected static function hasIdentityTickets(array $identity)
  {
    return (isset($identity['tickets']) && is_array($identity['tickets']) && count($identity['tickets']) > 0);
  }

  protected static function isIdentityTicketUser(array $identity)
  {
    return (isset($identity['ticketuser']) && true == $identity['ticketuser']);
  }

  protected static function isTicketAuthEnabled()
  {
    return (Registry::getConfig()->accessticket->activ == true);
  }

  protected function isTicketSessionExpired(array $ticketData)
  {
    if (!$this->hasSessionLifetime($ticketData)) {
      return false;
    }

    $expiredTime = intval($ticketData['sessionStart']) + intval($ticketData['sessionLifetime']);
    return ($expiredTime < time());
  }

  protected function hasSessionLifetime(array $ticketData)
  {
    return isset($ticketData['sessionLifetime']) && !empty($ticketData['sessionLifetime']);
  }

  /**
   * @param   array $identity
   *
   * @return  array
   */
  public function getRoles(array $identity)
  {
    $roles = array();
    if (self::isIdentityTicketUser($identity)) {
      $roles[] = AclBase::ROLE_TICKETUSER;
    }
    return $roles;
  }

  /**
   * @param   array $identity
   *
   * @return  array
   */
  public function getWebsitePrivileges(array $identity)
  {
    return array();
  }

  /**
   * @params array  $identity
   * @params string $websiteId
   *
   * @return boolean
   */
  public function isInAnyWebsiteGroup(array $identity, $websiteId)
  {
    return false;
  }

  /**
   * @param   array  $identity
   * @param   string $websiteId
   * @param   string $area
   * @param   string $privilege
   *
   * @return  boolean
   */
  public function checkWebsitePrivilegeForIdentity($identity, $websiteId, $area, $privilege)
  {
    if (self::isIdentityTicketUser($identity)) {
      if (isset($identity['tickets']) && isset($identity['tickets'][$websiteId])) {
        if ($area == 'render' && $privilege == 'all') {
          return true;
        }
        if ($area == 'creator' && $privilege == 'all') {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * @param   /Cms/Data/Ticket  $ticket
   *
   * @return  \Cms\Access\Auth\Result
   */
  protected function addTicketUserToAuthResult(\Cms\Data\Ticket $ticket)
  {
    $identity = $this->getIdentityAsArray();
    $identity['ticketuser'] = true;
    $identity['tickets'][$ticket->getWebsiteid()] = array(
      'websiteId' => $ticket->getWebsiteid(),
      'sessionLifetime' => $ticket->getSessionlifetime(),
      'sessionStart' => time(),
    );
    $identity['ticketParams'] = (!$ticket->isGet() ? $ticket->getInternalParams() : null);

    return $this->createTicketUserAuthResult($identity, 'Ticket-Authentication successful.');
  }

  protected function createTicketUserAuthResult(array $identity, $message)
  {
    $authResult = $this->createAuthResult(
        \Cms\Access\Auth\Result::SUCCESS,
        $identity,
        array($message)
    );

    if (self::hasIdentityTickets($identity)) {
      $authResult->addBackendName(self::BACKEND_NAME);
    } else {
      $authResult->removeBackendName(self::BACKEND_NAME);
    }

    return $authResult;
  }

  protected function validateRegisteredTicketUser()
  {
    $identity = $this->getIdentityAsArray();
    $hasChanges = false;
    if (self::isIdentityTicketUser($identity)) {
      if (isset($identity['tickets']) && is_array($identity['tickets'])) {
        foreach ($identity['tickets'] as $websiteId => $ticketData) {
          if (!self::isTicketAuthEnabled() || $this->isTicketSessionExpired($ticketData)) {
            unset($identity['tickets'][$websiteId]);
            $hasChanges = true;
          }
        }
      }
    }
    if ($hasChanges) {
      $this->setAuthResultToManager(
          $this->createTicketUserAuthResult($identity, 'Ticket-Session expired.'),
          true
      );
    }
  }

  /**
   * @param   array $identity
   *
   * @return  string|null
   */
  public function getUserId(array $identity)
  {
    return null;
  }
}
