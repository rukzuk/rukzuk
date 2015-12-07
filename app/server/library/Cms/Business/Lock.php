<?php
namespace Cms\Business;

use Cms\Exception as CmsException;
use Cms\ExceptionStack as CmsExceptionStack;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as SbLog;

/**
 * Lock
 *
 * @package      Cms
 * @subpackage   Business
 *
 * @method \Cms\Service\Lock getService
 */
class Lock extends Base\Service
{
  const LOCK_TYPE_PAGE      = 'page';
  const LOCK_TYPE_TEMPLATE  = 'template';
  const LOCK_TYPE_MODULE    = 'module';
  const LOCK_TYPE_WEBSITE   = 'website';
  
  const LOCK_STATE_NONE     = 'none';
  const LOCK_STATE_NEW      = 'new';
  const LOCK_STATE_UPDATE   = 'update';
  const LOCK_STATE_OVERRIDE = 'override';
  const LOCK_STATE_EXPIRED  = 'expired';
  const LOCK_STATE_ERROR    = 'error';

  const DUMMY_USER_ID = 'USER-00000000-0000-0000-0000-000000000001-USER';

  /**
   * @var array
   */
  private $currentIdentity = null;

  /**
   * @var array
   */
  private $identities = array();

  /**
   * @param string $runId
   * @param string $websiteId
   * @return array
   */
  public function getAll($runId, $websiteId)
  {
    return $this->getService()->getAll($websiteId);
  }

  /**
   * Entfernt den Bearbeitungs-Status von Page, Modul, Template oder Websites
   *
   * @param string  $itemId
   * @param string  $items
   */
  public function unlock($runId, $itemId, $websiteId, $type)
  {
    // Aktueller Benutzer ermitteln
    $identity = null;
    if (!$this->getCurrentUserInfo($identity, true)) {
      return false;
    }

    // init
    $doUnlock = true;

    // Gibt es einen Lock, welcher entfernt werden soll
    $locks = $itemInfo = array();
    if ($this->isLocked($itemId, $websiteId, $type, $itemInfo, $locks, false)) {
    // Lock vorhanden -> erst mal schauen ob es der Lock vom Benutzer selbst ist
      if (is_array($locks)) {
        foreach ($locks as $nextLock) {
          // Unlock noch erlaubt, Gleiches Item, gleicher Benutzer und gleiche RunId?
          if ($doUnlock == true && $type == $nextLock['type']
              && ($type == self::LOCK_TYPE_WEBSITE || $itemId == $itemInfo['id'])
              && $runId == $nextLock['runId']
              && isset($nextLock['user']) && isset($nextLock['user']['id'])
              && $identity['id'] === $nextLock['user']['id'] ) {
          // Ja -> Lock darf entfernt werden
            $doUnlock = true;
          } else {
            // Lock darf NICHT entfernt werden
            $doUnlock = false;
          }
        }
      }

      // Unlock durchfuehren
      if ($doUnlock) {
        $this->getService()->unlock($itemId, $websiteId, $type);
      }
    }
  }

  /**
   * Loescht Locks (ohne Pruefung)
   *
   * @param string  $websiteId
   * @param string  $type
   * @param string  $itemIds
   * @param boolean $allLocks
   */
  public function removeLocks($websiteId, $type, $itemIds, $allLocks = false)
  {
    // Locks der angegebenen Items loeschen
    if (is_array($itemIds)) {
      foreach ($itemIds as $itemId) {
        $locks = $itemInfo = array();
        if ($this->isLocked($itemId, $websiteId, $type, $itemInfo, $locks, $allLocks)) {
        // Lock vorhanden
          if (is_array($locks)) {
            if ($allLocks) {
              // alle locks loeschen
              foreach ($locks as $nextLock) {
                $this->getService()->unlock($nextLock['itemId'], $websiteId, $nextLock['type']);
              }
            } else {
              // nur den angegebenen lock loeschen
              $this->getService()->unlock($itemId, $websiteId, $type);
            }
          }
        }
      }
    }
  }

 /**
  * Remove all locks according to the given user id
  *
  * @param $userId
  */
  public function unlockByUserId($userId)
  {
      $this->getService()->unlockByUserId($userId);
  }

  /**
   * Remove all locks according to the given website id (without lock check)
   *
   * @param $userId
   */
  public function removeLocksByWebsiteId($userId)
  {
    $this->getService()->removeLocksByWebsiteId($userId);
  }

  /**
   * Gibt an, welche Page, Modul, Template vom User bearbeitet wird
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @param boolean $override
   */
  public function lockItem($runId, $itemId, $websiteId, $type, $override = false)
  {
    // Aktuellen Lockstatus ermitteln
    $lockState = $this->checkLock($runId, $itemId, $websiteId, $type, $override);
    $lockState['locked'] = false;

    // Locking durchfuehren
    switch($lockState['action'])
    {
      // Neu
      case self::LOCK_STATE_NEW:
        $lockState['locked'] = ($this->getService()->lockItem(
            $lockState['user']['id'],
            $runId,
            $itemId,
            $websiteId,
            $type
        ) ? true : false);
            break;

      // Ueberschreiben oder Alter Lock abgelaufen
      case self::LOCK_STATE_EXPIRED:
      case self::LOCK_STATE_OVERRIDE:
        $lockState['locked'] = ($this->getService()->override(
            $lockState['user']['id'],
            $runId,
            $itemId,
            $websiteId,
            $type
        ) ? true : false);
            break;

      // Aktualisieren
      case self::LOCK_STATE_UPDATE:
        $lockState['locked'] = ($this->getService()->update(
            $itemId,
            $websiteId,
            $type
        ) ? true : false);
            break;
    }

    // Status zurueckgeben
    return $lockState;
  }

  /**
   * Gibt an, welche Page, Modul, Template vom User bearbeitet wird
   *
   * @param string  $runId
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @param boolean $override
   * @param boolean $throwError
   */
  public function checkLock(
      $runId,
      $itemId,
      $websiteId,
      $type,
      $override = false,
      $throwError = true
  ) {
    // init
    $itemLocked = false;
    $lockState = array(
        'locked'  => false,
        'action'  => self::LOCK_STATE_NONE,
        'item'    => null
    );
    $lockState['action'] = self::LOCK_STATE_NONE;

    // Aktueller Benutzer ermitteln
    $identity = null;
    if (!$this->getCurrentUserInfo($identity, true)) {
      return false;
    }
    $lockState['user'] = $identity;

    // Ist das Item bereits gesperrt
    $locks = $itemInfo = array();
    if ($this->isLocked($itemId, $websiteId, $type, $lockState['item'], $locks)) {
      $itemInfo = $lockState['item'];
      
      // Alle Lockarten durchlaufen
      if (is_array($locks)) {
        foreach ($locks as $nextLock) {
          // Lock bereits abgelaufen
          if ($nextLock['isExpired']) {
          // Evlt. action auf Abgelaufen setzen
            if ($type == $nextLock['type']
                && ($type == self::LOCK_TYPE_WEBSITE || $itemId == $itemInfo['id'])
            ) {
              // Lock ist abgelaufen
              $lockState['action'] = self::LOCK_STATE_EXPIRED;
            }

            // Lock nicht beachten
            continue;
          }

          // Lock vorhanden
          $itemLocked = true;
          

          // Lock klauen, aktualisieren oder overridable-Status aufnehmen
          if ((!isset($lockState['overridable'])
                || (isset($lockState['overridable']) && $lockState['overridable'] === true))
              && $type == $nextLock['type']
              && ($type == self::LOCK_TYPE_WEBSITE || $itemId == $itemInfo['id'])
              && ($identity['superuser'] === true
                  || (isset($nextLock['user']) && isset($nextLock['user']['id'])
                      && $identity['id'] === $nextLock['user']['id'])
                 )
            ) {
          // Bereits ein locking-Art ermittelt
            if ($lockState['action'] === self::LOCK_STATE_NONE
                || $lockState['action'] === self::LOCK_STATE_EXPIRED) {
            // Lock darf ueberschrieben werden
              $lockState['overridable'] = true;

              // Lock klauen
              if ($override === true) {
                $lockState['action'] = self::LOCK_STATE_OVERRIDE;
                $lockState['overridable'] = true;
                continue;
              } // Lock aktualisieren
              elseif ($runId == $nextLock['runId']) {
                $lockState['action'] = self::LOCK_STATE_UPDATE;
                continue;
              }
            }
          }

          // Ueberschreibstatus vorhanden?
          if (!isset($lockState['overridable'])) {
          // Benutzer darf den Lock NICHT klauen
            $lockState['overridable'] = false;
          }
          
          // Fehler in den ExceptionStack aufnehmen
          if ($throwError) {
          // Fehlerdaten zusammenbauen
            $errData = array(
              'item.id'         => (isset($itemInfo['id']) ? $itemInfo['id'] : '???'),
              'item.name'       => (isset($itemInfo['name']) ? $itemInfo['name'] : '???'),
              'item.type'       => $type,
              'lock.type'       => $nextLock['type'],
              'lock.name'       => $nextLock['itemname'],
              'lock.self'       => false
            );

            // Benutzer vorhanden
            if (isset($nextLock['user']) && isset($nextLock['user']['id'])) {
            // Benutzer aufnehmen
              $errData['user.id']         = $nextLock['user']['id'];
              $errData['user.email']      = $nextLock['user']['email'];
              $errData['user.lastname']   = $nextLock['user']['lastname'];
              $errData['user.firstname']  = $nextLock['user']['firstname'];

              // Es ist der angemeldete Benutzer
              $errData['lock.self'] = ($identity['id'] == $nextLock['user']['id']
                                      ? true : false);
            }

            // Fehlercode ermitteln
            $exeptionCode = 1502;
            switch ($type)
            {
              // Page
              case self::LOCK_TYPE_PAGE:
                switch($nextLock['type'])
                {
                  case self::LOCK_TYPE_PAGE:
                    $exeptionCode = ($errData['lock.self'] ? 1511 : 1512);
                        break;
                  case self::LOCK_TYPE_TEMPLATE:
                    $exeptionCode = ($errData['lock.self'] ? 1513 : 1514);
                        break;
                  case self::LOCK_TYPE_WEBSITE:
                    $exeptionCode = ($errData['lock.self'] ? 1515 : 1516);
                        break;
                }
                    break;

              // Template
              case self::LOCK_TYPE_TEMPLATE:
                switch($nextLock['type'])
                {
                  case self::LOCK_TYPE_TEMPLATE:
                    $exeptionCode = ($errData['lock.self'] ? 1521 : 1522);
                        break;
                  case self::LOCK_TYPE_PAGE:
                    $exeptionCode = ($errData['lock.self'] ? 1523 : 1524);
                        break;
                  case self::LOCK_TYPE_WEBSITE:
                    $exeptionCode = ($errData['lock.self'] ? 1525 : 1526);
                        break;
                }
                    break;

              // Module
              case self::LOCK_TYPE_MODULE:
                switch($nextLock['type'])
                {
                  case self::LOCK_TYPE_MODULE:
                    $exeptionCode = ($errData['lock.self'] ? 1531 : 1532);
                        break;
                  case self::LOCK_TYPE_WEBSITE:
                    $exeptionCode = ($errData['lock.self'] ? 1533 : 1534);
                        break;
                }
                    break;

              // Website
              case self::LOCK_TYPE_WEBSITE:
                switch($nextLock['type'])
                {
                  case self::LOCK_TYPE_WEBSITE:
                    $exeptionCode = ($errData['lock.self'] ? 1541 : 1542);
                        break;
                  case self::LOCK_TYPE_PAGE:
                    $exeptionCode = ($errData['lock.self'] ? 1543 : 1544);
                        break;
                  case self::LOCK_TYPE_TEMPLATE:
                    $exeptionCode = ($errData['lock.self'] ? 1545 : 1546);
                        break;
                  case self::LOCK_TYPE_MODULE:
                    $exeptionCode = ($errData['lock.self'] ? 1547 : 1548);
                        break;
                }
                    break;
            }

            // Fehler aufnehmen
            CmsExceptionStack::addException(
                new CmsException($exeptionCode, __METHOD__, __LINE__, $errData)
            );
          }
        }
      }
    }

    // Lock vorhanden?
    if ($itemLocked) {
      $lockState['locked'] = true;
    } elseif (!$itemLocked && $lockState['action'] == self::LOCK_STATE_NONE) {
    // Lock normal durchfuehren
      $lockState['action'] = self::LOCK_STATE_NEW;
    }

    // Status zurueckgeben
    return $lockState;
  }

  /**
   * Gibt zurueck, ob eine Page, Modul, Template oder Website einen Lock besitzt
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @param array   $itemInfo
   * @param array   $lockInfo
   * @return boolean  true: Item ist gesperrt; false: Item ist nicht gesperrt
   */
  public function isLocked($itemId, $websiteId, $type, &$itemInfo, &$lockInfo, $checkAll = true)
  {
    // Je nach type den Lock anderst pruefen
    switch ($type)
    {
      case self::LOCK_TYPE_PAGE:
            return $this->isPageLocked($itemId, $websiteId, $itemInfo, $lockInfo, $checkAll);
        break;
      case self::LOCK_TYPE_TEMPLATE:
            return $this->isTemplateLocked($itemId, $websiteId, $itemInfo, $lockInfo, $checkAll);
        break;
      case self::LOCK_TYPE_MODULE:
            return $this->isModuleLocked($itemId, $websiteId, $itemInfo, $lockInfo, $checkAll);
        break;
      case self::LOCK_TYPE_WEBSITE:
            return $this->isWebsiteLocked($websiteId, $itemInfo, $lockInfo, $checkAll);
        break;
    }

    // Type existiert nicht
    throw new CmsException(1501, __METHOD__, __LINE__);
  }

  /**
   * Gibt zurueck, ob die Page gesperrt ist
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param array   $itemInfo
   * @param array   $lockInfo
   * @param boolean $checkAll     false: Nur die Page pruefen; true: Alles pruefen
   * @return boolean  true: Page ist gesperrt; false: Page ist nicht gesperrt
   */
  public function isPageLocked($itemId, $websiteId, &$itemInfo, &$lockInfo, $checkAll = true)
  {
    // init
    $isLocked = false;

    // Page ermitteln
    try {
      $page = $this->getBusiness('Page')->getById($itemId, $websiteId);
      $itemInfo = array(
          'type'  => self::LOCK_TYPE_PAGE,
          'id'    => $itemId,
          'name'  => $page->getName()
      );
    } catch (CmsException $e) {
      return false;
    }

    // Page gesperrt?
    $lock = $this->getService()->getByIdAndType($itemId, $websiteId, $itemInfo['type']);
    if ($lock instanceof \Cms\Data\Lock) {
    // Page ist gesperrt
      $isLocked = true;

      // Lockinformationen uebernehmen
      $curLockInfo = $lock->toArray();
      $curLockInfo['itemname']  = $itemInfo['name'];
      $curLockInfo['type']      = $itemInfo['type'];

      // Benutzer aufnehmen
      $this->getUserInfo($lock->getUserid(), $curLockInfo['user']);

      // Lock Aufnehmen
      $lockInfo[] = $curLockInfo;
    }

    // Auch weitere Pruefungen durchfuehren
    if ($checkAll === true) {
    // Website oder Template der Page gesperrt?
      if ($this->isWebsiteLocked($websiteId, $websiteInfo, $lockInfo, false) ||
          $this->isTemplateLocked($page->getTemplateid(), $websiteId, $templateInfo, $lockInfo, false)) {
      // Page ist ueber Website oder Template gesperrt
        $isLocked = true;
      }
    }

    // Page Lock-Status zurueckgeben
    return $isLocked;
  }

  /**
   * Gibt zurueck, ob das Template gesperrt ist
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param array   $itemInfo
   * @param array   $lockInfo
   * @param boolean $checkAll     false: Nur die Page pruefen; true: Alles pruefen
   * @return boolean  true: Template ist gesperrt; false: Template ist nicht gesperrt
   */
  public function isTemplateLocked($itemId, $websiteId, &$itemInfo, &$lockInfo, $checkAll = true)
  {
    // init
    $isLocked = false;

    // Template ermitteln
    try {
      $template = $this->getBusiness('Template')->getById($itemId, $websiteId);
      $itemInfo = array(
          'type'  => self::LOCK_TYPE_TEMPLATE,
          'id'    => $itemId,
          'name'  => $template->getName()
      );
    } catch (CmsException $e) {
      return false;
    }

    // Template gesperrt?
    $lock = $this->getService()->getByIdAndType($itemId, $websiteId, $itemInfo['type']);
    if ($lock instanceof \Cms\Data\Lock) {
    // Template ist gesperrt
      $isLocked = true;

      // Lockinformationen uebernehmen
      $curLockInfo = $lock->toArray();
      $curLockInfo['itemname']  = $itemInfo['name'];
      $curLockInfo['type']      = $itemInfo['type'];

      // Benutzer aufnehmen
      $this->getUserInfo($lock->getUserid(), $curLockInfo['user']);

      // Lock Aufnehmen
      $lockInfo[] = $curLockInfo;
    }

    // Auch weitere Pruefungen durchfuehren
    if ($checkAll === true) {
    // Website gesperrt?
      if ($this->isWebsiteLocked($websiteId, $websiteInfo, $lockInfo, false)) {
      // Template ist durch die Website gesperrt
        $isLocked = true;
      }

      // Alle gesperrten Pages dieser Website ermitteln
      $pageLocks = $this->getService()->findByWebsiteIdAndType(
          $websiteId,
          self::LOCK_TYPE_PAGE
      );
      if (is_array($pageLocks)) {
        $pageBusiness = $this->getBusiness('Page');
        foreach ($pageLocks as $nextLockedPage) {
        // Page vorhanden?
          if ($pageBusiness->existsPageAlready($nextLockedPage->getItemid(), $websiteId)) {
          // Ist diese Page aus dem zu pruefende Template erstellt
            $page = $pageBusiness->getById($nextLockedPage->getItemid(), $websiteId);
            if ($itemId == $page->getTemplateid()) {
            // Template ist durch diese Page gesperrt
              $isLocked = true;

              // Lockinformationen uebernehmen
              $curLockInfo = $nextLockedPage->toArray();
              $curLockInfo['itemname']  = $page->getName();
              $curLockInfo['type']      = self::LOCK_TYPE_PAGE;

              // Benutzer aufnehmen
              $this->getUserInfo($nextLockedPage->getUserid(), $curLockInfo['user']);

              // Lock Aufnehmen
              $lockInfo[] = $curLockInfo;
            }
          }
        }
      }
    }

    // Template Lock-Status zurueckgeben
    return $isLocked;
  }

  /**
   * Gibt zurueck, ob das Modul gesperrt ist
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param array   $itemInfo
   * @param array   $lockInfo
   * @param boolean $checkAll     false: Nur die Page pruefen; true: Alles pruefen
   * @return boolean  true: Modul ist gesperrt; false: Modul ist nicht gesperrt
   */
  public function isModuleLocked($itemId, $websiteId, &$itemInfo, &$lockInfo, $checkAll = true)
  {
    // init
    $isLocked = false;

    // Modul ermitteln
    try {
      $module = $this->getBusiness('Modul')->getById($itemId, $websiteId);
      $itemInfo = array(
          'type'  => self::LOCK_TYPE_MODULE,
          'id'    => $itemId,
          'name'  => $module->getName()
      );
    } catch (CmsException $e) {
      return false;
    }

    // Modul gesperrt?
    $lock = $this->getService()->getByIdAndType($itemId, $websiteId, $itemInfo['type']);
    if ($lock instanceof \Cms\Data\Lock) {
    // Template ist gesperrt
      $isLocked = true;

      // Lockinformationen uebernehmen
      $curLockInfo = $lock->toArray();
      $curLockInfo['itemname']  = $itemInfo['name'];
      $curLockInfo['type']      = $itemInfo['type'];

      // Benutzer aufnehmen
      $this->getUserInfo($lock->getUserid(), $curLockInfo['user']);

      // Lock Aufnehmen
      $lockInfo[] = $curLockInfo;
    }

    // Auch weitere Pruefungen durchfuehren
    if ($checkAll === true) {
    // Website gesperrt?
      if ($this->isWebsiteLocked($websiteId, $websiteInfo, $lockInfo, false)) {
      // Modul ist durch die Website gesperrt
        $isLocked = true;
      }
    }

    // Modul Lock-Status zurueckgeben
    return $isLocked;
  }

  /**
   * Gibt zurueck, ob die Website gesperrt ist
   *
   * @param string  $websiteId
   * @param array   $itemInfo
   * @param array   $lockInfo
   * @param boolean $checkAll         false: Nur die Website pruefen; true: Alles pruefen
   * @param boolean $checkIfExpired   false: Nur die Website pruefen; true: Alles pruefen
   * @return boolean  true: Website ist gesperrt; false: Website ist nicht gesperrt
   */
  public function isWebsiteLocked($websiteId, &$itemInfo, &$lockInfo, $checkAll = true, $checkIfExpired = false)
  {
    // init
    $isLocked = false;

    // Website ermitteln
    try {
      $website = $this->getBusiness('Website')->getById($websiteId);
      $itemInfo = array(
          'type'  => self::LOCK_TYPE_WEBSITE,
          'id'    => $websiteId,
          'name'  => $website->getName()
      );
    } catch (CmsException $e) {
      return false;
    }

    // Website gesperrt?
    $lock = $this->getService()->getByIdAndType('', $websiteId, $itemInfo['type']);
    if ($lock instanceof \Cms\Data\Lock) {
    // Website ist gesperrt
      if (!$checkIfExpired || !$lock->isExpired()) {
        $isLocked = true;
      }

      // Lockinformationen uebernehmen
      $curLockInfo = $lock->toArray();
      $curLockInfo['itemname']  = $itemInfo['name'];
      $curLockInfo['type']      = $itemInfo['type'];

      // Benutzer aufnehmen
      $this->getUserInfo($lock->getUserid(), $curLockInfo['user']);

      // Lock Aufnehmen
      $lockInfo[] = $curLockInfo;
    }

    // Auch weitere Pruefungen durchfuehren
    if ($checkAll === true) {
    // Alle gesperrten Pages dieser Website ermitteln
      $pageLocks = $this->getService()->findByWebsiteIdAndType(
          $websiteId,
          self::LOCK_TYPE_PAGE
      );
      if (is_array($pageLocks) && count($pageLocks) > 0) {
        $pageBusiness = $this->getBusiness('Page');
        foreach ($pageLocks as $nextLockedPage) {
        // Page vorhanden?
          if ($pageBusiness->existsPageAlready($nextLockedPage->getItemid(), $websiteId)) {
          // Website ist durch diese Page gesperrt
            if (!$checkIfExpired || !$nextLockedPage->isExpired()) {
              $isLocked = true;
            }

            // Lockinformationen uebernehmen
            $page         = $pageBusiness->getById($nextLockedPage->getItemid(), $websiteId);
            $curLockInfo  = $nextLockedPage->toArray();
            $curLockInfo['itemname']  = $page->getName();
            $curLockInfo['type']      = self::LOCK_TYPE_PAGE;

            // Benutzer aufnehmen
            $this->getUserInfo($nextLockedPage->getUserid(), $curLockInfo['user']);

            // Lock Aufnehmen
            $lockInfo[] = $curLockInfo;
          }
        }
      }

      // Alle gesperrten Templates dieser Website ermitteln
      $templateLocks = $this->getService()->findByWebsiteIdAndType(
          $websiteId,
          self::LOCK_TYPE_TEMPLATE
      );
      if (is_array($templateLocks) && count($templateLocks) > 0) {
        $templateBusiness = $this->getBusiness('Template');
        foreach ($templateLocks as $nextLockedTemplate) {
        // Template vorhanden?
          if ($templateBusiness->existsTemplateAlready($nextLockedTemplate->getItemid(), $websiteId)) {
          // Website ist durch dieses Template gesperrt
            if (!$checkIfExpired || !$nextLockedTemplate->isExpired()) {
              $isLocked = true;
            }

            // Lockinformationen uebernehmen
            $template     = $templateBusiness->getById($nextLockedTemplate->getItemid(), $websiteId);
            $curLockInfo  = $nextLockedTemplate->toArray();
            $curLockInfo['itemname']  = $template->getName();
            $curLockInfo['type']      = self::LOCK_TYPE_TEMPLATE;

            // Benutzer aufnehmen
            $this->getUserInfo($nextLockedTemplate->getUserid(), $curLockInfo['user']);

            // Lock Aufnehmen
            $lockInfo[] = $curLockInfo;
          }
        }
      }

      // Alle gesperrten Module dieser Website ermitteln
      $moduleLocks = $this->getService()->findByWebsiteIdAndType(
          $websiteId,
          self::LOCK_TYPE_MODULE
      );
      if (is_array($moduleLocks) && count($moduleLocks) > 0) {
        $moduleBusiness = $this->getBusiness('Modul');
        foreach ($moduleLocks as $nextLockedModule) {
        // Modul vorhanden?
          if ($moduleBusiness->existsModulAlready($nextLockedModule->getItemid(), $websiteId)) {
          // Website ist durch dieses Modul gesperrt
            if (!$checkIfExpired || !$nextLockedModule->isExpired()) {
              $isLocked = true;
            }

            // Lockinformationen uebernehmen
            $module       = $moduleBusiness->getById($nextLockedModule->getItemid(), $websiteId);
            $curLockInfo  = $nextLockedModule->toArray();
            $curLockInfo['itemname']  = $module->getName();
            $curLockInfo['type']      = self::LOCK_TYPE_MODULE;

            // Benutzer aufnehmen
            $this->getUserInfo($nextLockedModule->getUserid(), $curLockInfo['user']);

            // Lock Aufnehmen
            $lockInfo[] = $curLockInfo;
          }
        }
      }
    }

    // Website Lock-Status zurueckgeben
    return $isLocked;
  }

  /**
   * Gibt zurueck, ob der angegebene Lock fuer den angemeldete Benutzer existiert
   *
   * @param string  $runId
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @param boolean $noLock     true: Lock muss fuer den Benutzer vorhanden sein
   *                            false: gibt true zurueck auch wenn kein Lock existiert
   * @param boolean $throwError
   * @return boolean  true: Lock vorhanden; false: Lock nicht vorhanden
   */
  public function checkUserLock(
      $runId,
      $itemId,
      $websiteId,
      $type,
      $noLock = true,
      $throwError = true
  ) {
    // Lockstatus ermitteln
    $lockState = $this->checkLock($runId, $itemId, $websiteId, $type, false, $throwError);

    // Lock vorhanden?
    if (isset($lockState['action'])
        && ($lockState['action'] == self::LOCK_STATE_NEW
            || $lockState['action'] == self::LOCK_STATE_EXPIRED)
        && $noLock == true && $throwError == true) {
    // Nein -> Fehler aufnehmen
      switch($type)
      {
        case self::LOCK_TYPE_PAGE:
          $exeptionCode = ($lockState['action'] == self::LOCK_STATE_EXPIRED ? 1517 : 1510);
              break;
        case self::LOCK_TYPE_TEMPLATE:
          $exeptionCode = ($lockState['action'] == self::LOCK_STATE_EXPIRED ? 1527 : 1520);
              break;
        case self::LOCK_TYPE_MODULE:
          $exeptionCode = ($lockState['action'] == self::LOCK_STATE_EXPIRED ? 1535 : 1530);
              break;
        case self::LOCK_TYPE_WEBSITE:
          $exeptionCode = ($lockState['action'] == self::LOCK_STATE_EXPIRED ? 1549 : 1540);
              break;
        default:
          $exeptionCode = ($lockState['action'] == self::LOCK_STATE_EXPIRED ? 1509 : 1508);
              break;
      }

      // Fehlerdaten zusammenbauen
      $errData = array(
        'item.id'         => (isset($lockState['item']['id']) ? $lockState['item']['id'] : '???'),
        'item.name'       => (isset($lockState['item']['name']) ?$lockState['item']['name'] : '???'),
        'item.type'       => $type
      );

      // Fehler aufnehmen
      CmsExceptionStack::addException(
          new CmsException($exeptionCode, __METHOD__, __LINE__, $errData)
      );
    }

    return ((isset($lockState['action'])
            && ($lockState['action'] == self::LOCK_STATE_UPDATE)
                || ($noLock !== true && $lockState['action'] == self::LOCK_STATE_NEW))
              ? true : false);
  }

  /**
   * GGibt zurueck, ob der angegebene Lock fuer den angemeldete Benutzer existiert
   *
   * @param string  $runId
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @return array  Informationen zu dem Lock
   */
  public function getUserLock($runId, $itemId, $websiteId, $type)
  {
    // Lockstatus ermitteln
    return $this->checkLock($runId, $itemId, $websiteId, $type, false, false);
  }

  /**
   * Gibt die Inforamtionen zu einem bestimmten Benutzer zurueck
   *
   * @param array   $userId     Id des zu ermittelnden Benutzer
   * @param array   $identity   Ermittelte Benutzerinformation
   * @param boolean $reload     Benutzer neu ermitteln
   * @return boolean            true: Benutzer vorhanden; false: Benutzer nicht vorhanden
   */
  protected function getUserInfo($userId, &$identity, $reload = false)
  {
    // Bereits ermittelter Benutzer verwenden
    if ($reload === false && isset($this->identities[$userId])
        && is_array($this->identities[$userId])
        && isset($this->identities[$userId]['id'])) {
      $identity = $this->identities[$userId];
      return true;
    }

    // Benutzer ermitteln
    try {
      $user = $this->getBusiness('User')->getById($userId);
      $identity = $user->toArray();
    } catch (\Exception $logOnly) {
      Registry::getLogger()->log(__METHOD__, __LINE__, $logOnly->getMessage(), SbLog::NOTICE);
      $identity = $this->getNoIdentityUserInfo($userId);
    }
    if (is_array($identity) && isset($identity['id'])) {
      $this->identities[$userId] = $identity;
      return true;
    }

    return false;
  }

  /**
   * Gibt den aktuell angemeldeten Benutzer zurueck
   *
   * @param array   $identity   Angemeldeter Benutzer
   * @param boolean $reload     Benutzer neu ermitteln
   * @return boolean            true: Benutzer vorhanden; false: Nicht angemeldet
   */
  protected function getCurrentUserInfo(&$identity, $reload = false)
  {
    // Bereits ermittelter Benutzer verwenden
    if ($reload === false
        && is_array($this->currentIdentity) && isset($this->currentIdentity['id'])) {
      $identity = $this->currentIdentity;
      return true;
    }

    // Anmeldung aktiv
    if (Registry::getConfig()->group->check->activ == true) {
    // Aktueller Benutzer ermitteln
      $identity = $this->getIdentityAsArray();
      if (is_array($identity) && isset($identity['id'])) {
        $this->currentIdentity = $identity;
        $this->identities[$identity['id']] = $identity;
        return true;
      }
    } // Anmeldung NICHT aktiv
    else {
      // Simulierter Benutzer verwenden
      $identity = $this->getDummyUserInfo();
      $this->currentIdentity = $identity;
      $this->identities[$identity['id']] = $identity;
      return true;
    }
    return false;
  }

  /**
   * @param string  $userId
   * @return array
   */
  protected function getNoIdentityUserInfo($userId = null)
  {
    if (Registry::getConfig()->group->check->activ !== true && $userId == self::DUMMY_USER_ID) {
      return $this->getDummyUserInfo();
    }
    
    return array(
      'id'        => $userId,
      'firstname' => 'no',
      'lastname'  => 'user information',
      'email'     => 'n/a'
    );
  }

  
  /**
   * @return array
   */
  protected function getDummyUserInfo()
  {
    return array(
        'id'        => self::DUMMY_USER_ID,
        'firstname' => 'dummy',
        'lastname'  => 'group check not activ',
        'email'     => 'dummy'
    );
  }
}
