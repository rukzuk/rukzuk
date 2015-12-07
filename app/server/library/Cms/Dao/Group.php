<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Group Datenabfrage
 *
 * @package      Cms
 * @subpackage   Dao
 */
interface Group
{
  const DEFAULT_EMPTY_RIGHTS = '[]';
  const DEFAULT_EMPTY_USERS = '[]';

  /**
   * Setzt die Page Rechte einer Gruppe
   *
   * @param  string $id
   * @param  string $websiteId
   * @param  string $pageRights
   * @return \Orm\Entity\Group
   */
  public function setPageRights($id, $websiteId, array $pageRights);
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  string $name
   * @return string Id der kopierten Gruppe
   */
  public function copy($id, $websiteId, $name);
  /**
   * @param  string   $websiteId
   * @param  array    $columnValues
   * @param  boolean  $useColumnsValuesId
   * @return \Orm\Entity\Group
   */
  public function create($websiteId, array $columnValues, $useColumnsValuesId = false);
  /**
   * @param string $id
   * @param string $websiteId
   * @param array  $columnsValues
   */
  public function update($id, $websiteId, array $columnsValues);
  /**
   * @param  string  $userId
   * @param  string  $websiteId
   * @return array[] \Orm\Entity\Group
   */
  public function getAllByUserAndWebsiteId($userId, $websiteId);
  /**
   * @param  string  $websiteId
   * @return array[] \Orm\Entity\Group
   */
  public function getAllByWebsiteId($websiteId);
  /**
   * @param  string    $id
   * @param  string    $websiteId
   * @return \Orm\Entity\Group
   */
  public function getByIdAndWebsiteId($id, $websiteId);
  /**
   * @param  string  $userId
   * @return array[] \Orm\Entity\Group
   */
  public function getAllByUserId($userId);
  /**
   * @param  string  $id
   * @param  string  $websiteId
   * @return boolean
   */
  public function delete($id, $websiteId);
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $userIds
   * @return boolean
   */
  public function addUsers($id, $websiteId, array $userIds);
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $userIds
   * @return boolean
   */
  public function removeUsers($id, $websiteId, array $userIds);
  /**
   * @param  string $websiteId
   * @return boolean
   */
  public function existsGroupsForWebsite($websiteId);
  /**
   * @param  string  $id
   * @param  string  $websiteId
   * @return boolean
   */
  public function existsGroup($id, $websiteId);
  /**
   * @return boolean
   */
  public function deleteAll();
}
