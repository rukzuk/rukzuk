<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Lock Datenabfragen
 *
 * @package      Cms
 * @subpackage   Dao
 */
interface Lock
{
  /**
   * Gibt ein Array mit allen Lock-Objekten einer Website zurueck
   *
   * @param string  $websiteId
   * @return array[] \Orm\Entity\Lock
   */
  public function getAll($websiteId);

  /**
   * Erstellt einen Lock-Eintrag
   *
   * @param string  $userId
   * @param string  $runId
   * @param string  $id
   * @param string  $websiteId
   * @param string  $type
   * @return boolean
   */
  public function lockItem($userId, $runId, $id, $websiteId, $type);

  /**
   * Aktualisiert einen Lock-Eintrag
   *
   * @param string  $id
   * @param string  $websiteId
   * @param string  $type
   * @return boolean
   */
  public function update($id, $websiteId, $type);

  /**
   * Ueberschreibt einen Lock-Eintrag
   *
   * @param string  $userId
   * @param string  $runId
   * @param string  $id
   * @param string  $websiteId
   * @param string  $type
   * @return boolean
   */
  public function override($userId, $runId, $id, $websiteId, $type);

  /**
   * Einen Lock entfernen
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   */
  public function unlock($id, $websiteId, $type);

  /**
   * Gibt den Lock fuer eine bestimmte Page, Modul, Template oder Website zurueck
   *
   * @param string  $id
   * @param string  $websiteId
   * @param string  $type
   * @return array[] \Orm\Entity\Lock
   */
  public function getByIdAndType($id, $websiteId, $type);

  /**
   * Gibt die Lock fuer bestimmte Page, Modul, Template oder Website zurueck
   *
   * @param string  $websiteId
   * @param string  $type
   * @return array[] \Orm\Entity\Lock
   */
  public function findByWebsiteIdAndType($websiteId, $type);

  /**
   * Loescht die Locks welche laenger als die uebergebene Zeit untaetig waren
   *
   * @param integer  $maxLifetime
   * @throws \Cms\Exception
   */
  public function deleteByMaxlifetime($maxLifetime);


  /**
   * Remove all locks from a user.
   *
   * @param string $userId
   * @throws \Cms\Exception
   */
  public function unlockByUserId($userId);

  /**
   * Remove all locks of the given website id.
   *
   * @param string $websiteId
   * @throws \Cms\Exception
   */
  public function removeLocksByWebsiteId($websiteId);
}
