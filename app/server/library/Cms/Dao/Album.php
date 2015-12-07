<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Album Datenabfragen
 *
 * @package      Cms
 * @subpackage   Dao
 */
interface Album
{
  /**
   * @param string   $websiteId
   * @param array    $columnValues
   * @param  boolean  $useColumnsValuesId
   * @return \Orm\Entity\Album
   */
  public function create($websiteId, array $columnValues, $useColumnsValuesId = false);
  
  /**
   * @param string $id
   * @param string $websiteId
   * @param array  $columnsValues
   */
  public function update($id, $websiteId, array $columnsValues);
  
  /**
   * @param  string $websiteId
   * @return array
   */
  public function getAllByWebsiteId($websiteId);

  /**
   * @param  string $id
   * @param  string $werbsiteId
   * @return array
   */
  public function getById($id, $websiteId);

  /**
   * @param  string $websiteId
   * @param  string $albumName
   * @return array
   */
  public function getByWebsiteIdAndName($websiteId, $albumName);

  /**
   * @param string $id
   * @param string $websiteId
   */
  public function delete($id, $websiteId);
  
  /**
   * Checks if there is a album under the given Album Id and Website Id
   *
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function existsAlbum($id, $websiteId);
}
