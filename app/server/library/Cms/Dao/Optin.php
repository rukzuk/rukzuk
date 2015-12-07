<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Optin Datenabfrage
 *
 * @package      Cms
 * @subpackage   Dao
 */
interface Optin
{
  /**
   * @param  array     $users
   * @param  string    $mode
   * @return \Orm\Entity\OptIn
   */
  public function create(array $users, $mode);
  /**
   * @param  string  $code
   * @return boolean
   */
  public function deleteByCode($code);
  /**
   * @param  string  $id
   * @return boolean
   */
  public function deleteByUserId($id);
  /**
   * @param  array   $ids
   * @param  string  $mode
   * @return boolean
   */
  public function deleteByUserIdsAndMode(array $ids, $mode);
  /**
   * @param string $code
   * @return \Orm\Entity\Optin
   */
  public function getByCode($code);
  /**
   * @param string $id
   * @return \Orm\Entity\Optin
   */
  public function getByUserId($id);
  /**
   * @return boolean
   */
  public function deleteAll();
  /**
   * @param string $code
   * @return boolean
   */
  public function existsCode($code);
}
