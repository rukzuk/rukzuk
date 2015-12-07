<?php
namespace Cms\Controller\Plugin\Auth;

use \Cms\Controller\Plugin\Base as PluginBase;
use \Seitenbau\Registry as Registry;
use \Cms\Access\Manager as AccessManager;

/**
 * Plugin zur Pruefung, ob der aktuelle User die Rechte fuer den Aufruf besitzt
 *
 * @package      Cms
 * @subpackage   Controller\Plugin\Auth
 */
class DefaultAuth extends PluginBase
{
  public function __construct()
  {
  }

  /**
   * checks if actual role has access for this request
   *
   * @param \Zend_Controller_Request_Abstract $request
   */
  public function preDispatch(\Zend_Controller_Request_Abstract $request)
  {
    $accessManager = AccessManager::singleton();
    if ($this->isGroupCheckEnabled()) {
      $resource = strtolower($request->getControllerName());
      $privilege = strtolower($request->getActionName());
      
      if (!$accessManager->isAllowed($resource, $privilege)) {
        if ($accessManager->hasIdentityRoleGuest($accessManager->getIdentityAsArray(), true)) {
          \Cms\ExceptionStack::addException(new \Cms\Exception(5));
        } else {
          \Cms\ExceptionStack::addException(new \Cms\Exception(
              4,
              __METHOD__,
              __LINE__,
              array(
              'resource' => $resource,
              'privilege' => $request->getActionName())
          ));
        }
      }
    }
    
    /**
     * Sind Fehler aufgetreten, so muss hier explizit auf den Error-Controller
     * verwiesen werden.
     * Da wir im preDespatch sind greift unsere normale Weiterleitung auf den
     * Error-Controller bei nicht gefangenen Exception noch nicht.
     */
    if (count(\Cms\ExceptionStack::getExceptions()) > 0) {
      $request->setControllerName('Error');
      $request->setActionName('error');
    }
  }

  /**
   * Erstellt die komplette Ausgabe der Actions als JSON
   * Wirft evtl. aufgetretene Fehler
   */
  public function postDispatch(\Zend_Controller_Request_Abstract $request)
  {
    // Exceptions ausgeben, wenn welche im Stack liegen
    $exceptions = \Cms\ExceptionStack::getExceptions();

    if (count($exceptions) > 0) {
      \Cms\ExceptionStack::throwErrors();
    }

    parent::postDispatch($request);
  }

  protected function isGroupCheckEnabled()
  {
    return (Registry::getConfig()->group->check->activ == true);
  }
}
