<?php
namespace Cms\Business;

use Cms\Exception as CmsException;
use Cms\ExceptionStack as CmsExceptionStack;
use Cms\Business\Lock as LockBusiness;

/**
 * Lock
 *
 * @package      Cms
 * @subpackage   Business
 */
class Heartbeat extends Base\Service
{
  /**
   * Ueberprueft, ob der aktuelle Benutzer die oeffenen Entitaeten mit der
   * angegebenen runId gesperrt hat
   *
   * @param string  $runId
   * @param string  $websiteId
   * @param string  $openItems
   * @return array  Liste der abgelaufenen bzw. nicht mehr gueltigen Locks
   */
  public function checkOpenItems($runId, $websiteId, $openItems)
  {
    // init
    $faultyLocks = array('expired' => array(), 'invalid' => array());

    // Lock Business ermitteln
    $lockBusiness = $this->getBusiness('Lock');

    // Pages zum Pruefen vorhanden
    if (isset($openItems->pages)) {
      foreach ($openItems->pages as $nextOpenPageId) {
        // Lock Status ermitteln
        $lockState = $lockBusiness->getUserLock(
            $runId,
            $nextOpenPageId,
            $websiteId,
            LockBusiness::LOCK_TYPE_PAGE
        );

        // Lock vorhanden -> updaten
        if (isset($lockState) && is_array($lockState)
            && $lockState['action'] === LockBusiness::LOCK_STATE_UPDATE ) {
          $lockBusiness->update(
              $nextOpenPageId,
              $websiteId,
              LockBusiness::LOCK_TYPE_PAGE
          );
        } // Lock abgelaufen
        elseif (isset($lockState) && is_array($lockState)
            && $lockState['action'] === LockBusiness::LOCK_STATE_EXPIRED ) {
          $faultyLocks['expired']['pages'][] = $nextOpenPageId;
        } // Lock nicht vorhanden
        elseif (!isset($lockState) || !is_array($lockState)
            || $lockState['action'] !== LockBusiness::LOCK_STATE_UPDATE ) {
          $faultyLocks['invalid']['pages'][] = $nextOpenPageId;
        }
      }
    }

    // Templates zum Pruefen vorhanden
    if (isset($openItems->templates)) {
      foreach ($openItems->templates as $nextOpenTemplateId) {
        // Lock Status ermitteln
        $lockState = $lockBusiness->getUserLock(
            $runId,
            $nextOpenTemplateId,
            $websiteId,
            LockBusiness::LOCK_TYPE_TEMPLATE
        );

        // Lock vorhanden -> updaten
        if (isset($lockState) && is_array($lockState)
            && $lockState['action'] === LockBusiness::LOCK_STATE_UPDATE ) {
          $lockBusiness->update(
              $nextOpenTemplateId,
              $websiteId,
              LockBusiness::LOCK_TYPE_TEMPLATE
          );
        } // Lock abgelaufen
        elseif (isset($lockState) && is_array($lockState)
            && $lockState['action'] === LockBusiness::LOCK_STATE_EXPIRED ) {
          $faultyLocks['expired']['templates'][] = $nextOpenTemplateId;
        } // Lock nicht vorhanden
        elseif (!isset($lockState) || !is_array($lockState)
            || $lockState['action'] !== LockBusiness::LOCK_STATE_UPDATE ) {
          $faultyLocks['invalid']['templates'][] = $nextOpenTemplateId;
        }
      }
    }

    // Module zum Pruefen vorhanden
    if (isset($openItems->modules)) {
      foreach ($openItems->modules as $nextOpenModuleId) {
        // Lock Status ermitteln
        $lockState = $lockBusiness->getUserLock(
            $runId,
            $nextOpenModuleId,
            $websiteId,
            LockBusiness::LOCK_TYPE_MODULE
        );

        // Lock vorhanden -> updaten
        if (isset($lockState) && is_array($lockState)
            && $lockState['action'] === LockBusiness::LOCK_STATE_UPDATE ) {
          $lockBusiness->update(
              $nextOpenModuleId,
              $websiteId,
              LockBusiness::LOCK_TYPE_MODULE
          );
        } // Lock abgelaufen
        elseif (isset($lockState) && is_array($lockState)
            && $lockState['action'] === LockBusiness::LOCK_STATE_EXPIRED ) {
          $faultyLocks['expired']['modules'][] = $nextOpenModuleId;
        } // Lock nicht vorhanden
        elseif (!isset($lockState) || !is_array($lockState)
            || $lockState['action'] !== LockBusiness::LOCK_STATE_UPDATE ) {
          $faultyLocks['invalid']['modules'][] = $nextOpenModuleId;
        }
      }
    }

    // Fehlerhafte Locks zurueckgeben
    return $faultyLocks;
  }
}
