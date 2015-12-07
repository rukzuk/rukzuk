<?php
namespace Cms\Business\Base;

use Cms\Business\Iface;
use \Cms\Access\Manager as AccessManager;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as SbLog;

/**
 * Stellt die Basis fuer Plain Business Objekte
 *
 * @package      Cms
 * @subpackage   Business
 */

abstract class Plain implements Iface\Marker
{
  protected $business       = array();

  public function __construct()
  {
    $this->init();
  }
  
  /**
   * Funktion zum initialisieren von Variablen innerhalb der Business-Klassen
   */
  protected function init()
  {
  }
  
  /**
   * Gibt ein Business Objekt zurueck
   *
   * Mit dieser Methode kann quer innerhalb des Business Objekt auf ein anderes
   * Business Objekt zugegriffen werden. Es muss selbst darauf geachtet werden,
   * dass die Zugriffe terminiert sind. (Verhinderung von Endlosschleifen)
   *
   * @param string $businessname
   * @return \Cms\Business\Base\Plain
   */
  protected function getBusiness($businessname)
  {
    if (isset($this->business[$businessname])) {
      return $this->business[$businessname];
    } else {
      $business = 'Cms\Business\\' . $businessname;

      if (class_exists($business)) {
        $this->business[$businessname] = new $business($businessname);
        return $this->business[$businessname];
      }
    }
  }

  /**
   * @return \Cms\Access\Manager
   */
  protected function getAccessManager()
  {
    return AccessManager::singleton();
  }

  /**
   * @return \Cms\Access\Manager
   */
  protected function getIdentityAsArray()
  {
    return $this->getAccessManager()->getIdentityAsArray();
  }

  /**
   * Prueft die Rechte des angemeldeten Users fuer die aufgerufene Aktion im
   * Zusammenhang mit der Website-ID und Page-ID
   * Hat der User keine Rechte, so wird eine Exception geworfen
   *
   * @param string $rightname
   * @param mixed $check
   * @return boolean
   * @throws  \Cms\Exception
   */
  final public function checkUserRights($rightname, $check = null)
  {
    if (Registry::getConfig()->group->check->activ == true) {
      $identity = $this->getIdentityAsArray();
      $userHaveRights = $this->hasUserRights($identity, $rightname, $check);
      if ($userHaveRights == false) {
        Registry::getLogger()->logData(
            __METHOD__,
            __LINE__,
            'User has no privilege',
            array(
            'check'           => array(
              'business'        => get_class($this),
              'right'           => $rightname,
              'data'            => $check,
            ),
            'identity'        => array(
              'id'              => (isset($identity['id']) ? $identity['id'] : 'n/a'),
            ),
            ),
            SbLog::ERR
        );
        throw new \Cms\Exception(7, __METHOD__, __LINE__);
      }
    }

    return true;
  }

  /**
   * Pruefung, ob der angemeldete User die Rechte fuer die aufgerufene Aktion
   * besitzt
   *
   * Die Methode muss in den einzelnen Business ueberschrieben werden, sobald
   * eine individuelle Pruefung auf einzelne Rechte erfolgen soll
   *
   * Als Standart wird "Nicht Berechtigt" zurueckgegeben
   *
   * @param array  $identity
   * @param string $rightname Name des Rechts, auf das geprueft wird
   * @param mixed  $check
   * @return boolean
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    return false;
  }

  /**
   * Pruefung, ob der angemeldete User einer Gruppe der Website zugeordnet ist
   * bzw. ein Ticketuser auf die Website zugreifen darf
   *
   * @param array   $identity     Benutzerinformationen
   * @param array   $websiteId
   * @return boolean
   */
  protected function isUserInAnyWebsiteGroup($identity, $websiteId)
  {
    return $this->getAccessManager()->isInAnyWebsiteGroup($identity, $websiteId);
  }

  /**
   * Ist der Benutzer Superuser
   *
   * @param array  $identity  Benutzerinformationen
   * @return boolean
   */
  protected function isSuperuser($identity)
  {
    return $this->getAccessManager()->isSuperuser($identity);
  }
 
  /**
   * Prueft ob der Benutzer bestimmte Gruppen dieser Website besitzt
   *
   * @param array   $identity   Benutzerinformationen
   * @param string  $websiteId  ID der Website
   * @param string  $area       Zu pruefender Bereich
   * @param string  $privilege  Zu pruefendes Recht
   * @return boolean
   */
  protected function checkUserGroupRights($identity, $websiteId, $area, $privilege)
  {
    return $this->checkWebsitePrivilegeForIdentity($identity, $websiteId, $area, $privilege);
  }
  

  /**
   * @param array   $identity
   * @param string  $websiteId
   * @param string  $area       area to check
   * @param string  $privilege  privilege to check in the area
   * @return boolean
   */
  protected function checkWebsitePrivilegeForIdentity($identity, $websiteId, $area, $privilege)
  {
    return $this->getAccessManager()->checkWebsitePrivilegeForIdentity($identity, $websiteId, $area, $privilege);
  }
}
