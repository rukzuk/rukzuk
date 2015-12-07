<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer User Datenabfrage
 *
 * @package      Cms
 * @subpackage   Dao
 */
interface User
{
  /**
   * @param  array    $columnValues
   * @param  boolean  $useColumnsValuesId
   * @return \Cms\Data\User
   */
  public function create(array $columnValues, $useColumnsValuesId = false);
  /**
   * @param string $id
   * @param array  $columnsValues
   * @return \Cms\Data\User
   */
  public function update($id, array $columnsValues);
  /**
   * @param  string  $id
   * @return boolean
   */
  public function delete($id);
  /**
   * @param  string  $websiteId
   * @return \Cms\Data\User[]
   */
  public function getAll($websiteId = null);
  /**
   * @param string $id
   * @return \Cms\Data\User
   */
  public function getById($id);
  /**
   * @param array $ids
   * @return \Cms\Data\User[]
   */
  public function getByIds(array $ids);
  /**
   * @param string $email
   * @param string $id
   * @return \Orm\Entity\User
   */
  public function getByEmailAndIgnoredId($email, $id = null);
  /**
   * @return \Cms\Data\User[]
   */
  public function getAllSuperusers();
  /**
   * @return boolean
   */
  public function deleteAll();

  /**
   * Owner of the space
   * @return \Cms\Data\User
   */
  public function getOwner();
}
