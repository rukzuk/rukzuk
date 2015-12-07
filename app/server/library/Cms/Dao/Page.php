<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Page Datenabfrage
 *
 * @package      Cms
 * @subpackage   Dao
 */

interface Page
{
  /**
   * Gibt eine Page anhand der ID und Website-ID zurueck
   * ID und Website-ID ergeben den Primary-Key
   *
   * @param string $id
   * @param string  $websiteId
   */
  public function getById($id, $websiteId);

  /**
   * Kopiert eine vorhandene Page anhand der ID unter einem neuen Namen
   *
   * @param string  $id
   * @param string  $websiteId
   * @param string  $newname
   */
  public function copy($id, $websiteId, $newname = null);

  /**
   * Kopiert alle vorhandenen Pages einer Website zu einer neuen Website
   *
   * @param string  $websiteId
   * @param string  $newWebsiteId
   */
  public function copyPagesToNewWebsite($websiteId, $newWebsiteId);

  /**
   * Loescht eine vorhanden Page anhand der ID und Website-ID
   *
   * @param string  $id
   * @param string  $websiteId
   */
  public function delete($id, $websiteId);

  /**
   * Loescht mehrere via id identifizierte Page-Objekte
   *
   * @param string $websiteId
   * @param array  $ids
   */
  public function deleteByIds($websiteId, array $ids);

  /**
   * deletes all Pages of the given website id
   *
   * @param string $websiteId
   */
  public function deleteByWebsiteId($websiteId);

  /**
   * Aktualisiert in einem Eintrag die angegebenen Attribute
   *
   * @param string  $id
   * @param string  $websiteId
   * @param array $attributes
   */
  public function update($id, $websiteId, $attributes);

  /**
   * erstellt eine neue Page anhand der uerbegebenen Attribute
   *
   * @param string  $websiteId
   * @param array   $attributes
   * @param boolean $useColumnsValuesId Defaults to false
   */
  public function create($websiteId, $attributes, $useColumnsValuesId = false);

  /**
   * Gibt die Page-Namen zu der uebergebenen Website-ID zurueck
   *
   * @param int $websiteId
   */
  public function getInfosByWebsiteId($websiteId);
  
  /**
   * Gibt Pages anhand der vorhandenen/verknuepften Media und Website Id zurueck
   *
   * @param string $mediaId
   * @param string $websiteId
   */
  public function findByMediaAndWebsiteId($mediaId, $websiteId);

  /**
   * Checks if there is a page under the given Page Id and Website Id
   *
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function existsPage($id, $websiteId);
  
  /**
   * returns the page ids that have a relation with the given template id
   *
   * @param string  $websiteId
   * @param string  $templateId
   * @return  array
   */
  public function getIdsByWebsiteIdAndTemplateId($websiteId, $templateId);
  
  /**
   * returns the pages that have a relation with the given module id
   *
   * @param string  $websiteId
   * @param string  $moduleId
   * @return  array
   */
  public function findByWebsiteIdAndModuleId($websiteId, $moduleId);

  /**
   * returns the ids of the pages related to the website given by website id
   *
   * @param string $websiteId
   * @return array
   */
  public function getIdsByWebsiteId($websiteId);
}
