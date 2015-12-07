<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Template Datenabfrage
 *
 * @package      Cms
 * @subpackage   Dao
 */

interface Template
{
  /**
   * Gibt ein Array mit allen Template-Objekten einer Website zurueck
   * @param string  $websiteId
   * @return array[] \Orm\Entity\Template
   *
   */
  public function getAll($websiteId);

  /**
   * Array with reduced data of the template
   * @param string  $websiteId
   * @return array[] \Orm\Entity\Template
   *
   */
  public function getInfoByWebsiteId($websiteId);

  /**
   * Gibt ein via id und websiteId identifiziertes Template-Objekte zurueck
   *
   * @param  string $id
   * @param string  $websiteId
   */
  public function getById($id, $websiteId);

  /**
   * Loescht mehrere via id identifizierte Template-Objekte
   *
   * @param string $websiteId
   * @param array  $ids
   */
  public function deleteByIds($websiteId, array $ids);
  
  /**
   * Loescht ein via id und websiteId identifiziertes Template-Objekte
   *
   * @param  string $id
   * @param string  $websiteId
   */
  public function delete($id, $websiteId);

  /**
   * deletes all Templates of the given website id
   *
   * @param string $websiteId
   */
  public function deleteByWebsiteId($websiteId);

  /**
   * Erzeugt eine Template-Objekt
   *
   * @param string  $websiteId
   * @param array   $columnsValues
   * @param boolean $useColumnsValuesId Defaults to false
   */
  public function create($websiteId, array $columnsValues, $useColumnsValuesId = false);

  /**
   * Updated eine Template-Objekt
   *
   * @param string  $id
   * @param string  $websiteId
   * @param array   $columnsValues
   */
  public function update($id, $websiteId, array $columnsValues);

  /**
   * Gibt Templates anhand der gefunden Treffer von $needle im Content und
   * der passenden Website Id zurueck
   *
   * @param string $needle
   * @param string $websiteId
   * @return array[] \Orm\Data\Template
   */
  public function searchInContent($needle, $websiteId);

  /**
   * Kopiert Templates zu einer neuen Website
   *
   * @param string  $websiteId
   * @param string  $newWebsiteId
   */
  public function copyToNewWebsite($websiteId, $newWebsiteId, array $templateIds = array());

  /**
   * Checks if there is a template under the given Template Id and Website Id
   *
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function existsTemplate($id, $websiteId);
  
  /**
   * returns the templates that have a relation with the given module id
   *
   * @param string  $websiteId
   * @param string  $moduleId
   * @return  array
   */
  public function findByWebsiteIdAndModuleId($websiteId, $moduleId);
  
  /**
   * returns the used module ids for the given website and template id
   *
   * @param  string $websiteId
   * @param  string $id
   * @return array
   */
  public function getUsedModuleIds($websiteId, $id);

  /**
   * returns the ids of the templates related to the website given by website id
   *
   * @param string $websiteId
   *
   * @return array
   * @throws \Cms\Exception
   */
  public function getIdsByWebsiteId($websiteId);
}
