<?php
namespace Cms\Controller\Plugin\Auth;

use \Cms\Controller\Plugin\Base as PluginBase;
use \Cms\Access\Manager as AccessManager;

/**
 * Access Ticket
 *
 * @package      Cms
 * @subpackage   Controller\Plugin\Auth
 */
class TicketAuth extends PluginBase
{
  public function __construct()
  {
  }

  public function preDispatch(\Zend_Controller_Request_Abstract $request)
  {
    if ($this->getRequest()->getParam('fromticket')) {
      $this->setTicketParamsFromSession();
    }
  }

  protected function setTicketParamsFromSession()
  {
    $accessManager = AccessManager::singleton();
    $identity = $accessManager->getIdentityAsArray();
    if ($accessManager->hasIdentityRoleTicket($identity)) {
      if (isset($identity['ticketParams'])) {
        $this->getRequest()->setParams($identity['ticketParams']);
      }
    }
  }
}
