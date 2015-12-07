<?php
namespace Cms\Service;

/**
 * Lock
 *
 * @package      Cms
 * @subpackage   Service
 */
class Lock extends Base\Dao
{
  /**
   * @param string $websiteId
   * @return array
   */
  public function getAll($websiteId)
  {
    return $this->execute('getAll', array($websiteId));
  }
  
  /**
   * Gibt an, welche Page, Modul, Template oder Website vom User bearbeitet wird
   *
   * @param string  $userId
   * @param string  $runId
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   */
  public function lockItem($userId, $runId, $itemId, $websiteId, $type)
  {
    $this->checkItemId($itemId, $type);
    return $this->execute('lockItem', array($userId, $runId, $itemId, $websiteId, $type));
  }

  /**
   * Einen Lock aktualisiert
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   */
  public function update($itemId, $websiteId, $type)
  {
    $this->checkItemId($itemId, $type);
    return $this->execute('update', array($itemId, $websiteId, $type));
  }

  /**
   * Einen Lock ueberschrieben
   *
   * @param string  $userId
   * @param string  $runId
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   */
  public function override($userId, $runId, $itemId, $websiteId, $type)
  {
    $this->checkItemId($itemId, $type);
    return $this->execute('override', array($userId, $runId, $itemId, $websiteId, $type));
  }

  /**
   * Einen Lock entfernen
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   */
  public function unlock($itemId, $websiteId, $type)
  {
    $this->deleteInvalidLocks($websiteId);
    $this->checkItemId($itemId, $type);
    return $this->execute('unlock', array($itemId, $websiteId, $type));
  }

    /**
     * Remove all locks according to the given user id
     *
     * @param string $userId
     * @return void
     */
    public function unlockByUserId($userId)
    {
        $this->execute('unlockByUserId', array($userId));
    }

  /**
   * Remove all locks according to the given website id
   *
   * @param string $websiteId
   */
    public function removeLocksByWebsiteId($websiteId)
    {
      $this->execute('removeLocksByWebsiteId', array($websiteId));
    }

  /**
   * Gibt den Lock fuer eine bestimmte Page, Modul, Template oder Website zurueck
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   */
    public function getByIdAndType($itemId, $websiteId, $type)
    {
      $this->checkItemId($itemId, $type);
      return $this->execute('getByIdAndType', array($itemId, $websiteId, $type));
    }

  /**
   * Gibt die Lock fuer bestimmte Page, Modul, Template oder Website zurueck
   *
   * @param string  $websiteId
   * @param string  $type
   */
    public function findByWebsiteIdAndType($websiteId, $type)
    {
      $this->checkItemId($itemId, $type);
      return $this->execute('findByWebsiteIdAndType', array($websiteId, $type));
    }

  /**
   * Ueberprueft und korrigiert die Item-Id anhand des Typs
   *
   * @param string  &$itemId
   * @param string  $type
   */
    protected function checkItemId(&$itemId, $type)
    {
      // Bei Typ 'website' -> itemId leeren
      if ($type == 'website') {
        $itemId = '';
      }
    }

  /**
   * Loesch invalide Ticket (z.B. gc_maxlifetime)
   *
   * @param string  $websiteId
   */
    public function deleteInvalidLocks($websiteId)
    {
      $maxLifeTime = \Seitenbau\Registry::getConfig()->lock->gc_maxlifetime;
      return $this->execute('deleteByMaxlifetime', array($maxLifeTime));
    }
}
