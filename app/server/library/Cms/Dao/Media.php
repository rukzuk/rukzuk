<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Media Datenabfrage
 *
 * @package      Application
 * @subpackage   Controller
 */
interface Media
{
  /**
   * @param string $websiteId
   * @return integer
   */
  public function getSizeByWebsiteId($websiteId);

  /**
   * Loescht mehrere via id identifizierte Media-Objekte
   *
   * @param string $websiteId
   * @param array  $ids
   */
  public function deleteByIds($websiteId, array $ids);
  
  /**
   * Editiert ein via id identifiziertes Media-Objekt
   *
   * @param string $id
   * @param string $websiteId
   * @param array  $editValues
   */
  public function edit($id, $websiteId, array $editValues);
  
  /**
   * @param  string $websiteId
   * @param  array  $filterValues
   * @param  boolean $ignoreLimit Defaults to false
   * @return array
   */
  public function getByWebsiteIdAndFilter(
      $websiteId,
      array $filterValues = array(),
      $ignoreLimit = false
  );
  
  /**
   * @param string $id
   * @param string  $websiteId
   * @return \Orm\Entity\Media
   */
  public function getById($id, $websiteId);
  
  /**
   * @param string $id
   * @param string $websiteId
   * @return \Orm\Entity\Media
   */
  public function getByIdAndWebsiteId($id, $websiteId);
  
  /**
   * Gibt mehrere Media-Objekte anhand der Ids zurueck
   *
   * @param   array   $ids        Medien-Ids
   * @param   string  $websiteId
   * @return  array   Array mit Media-Objekten
   */
  public function getMultipleByIds(array $ids, $websiteId);
  
  /**
   * @param string   $websiteId
   * @param array    $columnValues
   * @param boolean  $useColumnsValuesId
   * @return \Orm\Entity\Media
   */
  public function create($websiteId, array $columnValues, $useColumnsValuesId = false);

  /**
   * Checks if there is a media under the given Media Id and Website Id
   *
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function existsMedia($id, $websiteId);

  /**
   * Copies all MediaItems to new website id
   *
   * @param  string $sourceWebsiteId
   * @param  string $newWebsiteId
   * @return boolean
   */
  public function copyMediaToNewWebsite($sourceWebsiteId, $newWebsiteId);
  
  /**
   * @param  string  $albumId
   * @param  string  $websiteId
   * @param  array   $ids
   * @return boolean
   */
  public function moveMediasToAlbum($albumId, $websiteId, array $ids);
}
