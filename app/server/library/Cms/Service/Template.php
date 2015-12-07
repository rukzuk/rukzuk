<?php
namespace Cms\Service;

use Cms\Service\Base\Dao as DaoServiceBase;
use Orm\Data;

/**
 * Template service
 *
 * @package      Cms
 * @subpackage   Service
 */

class Template extends DaoServiceBase
{
  /**
   * @param $websiteId
   *
   * @return \Cms\Data\Template[]
   */
  public function getAll($websiteId)
  {
    return $this->execute('getAll', array($websiteId));
  }

  public function getInfoByWebsiteId($websiteId)
  {
    return $this->execute('getInfoByWebsiteId', array($websiteId));
  }

  public function copyToNewWebsite(
      $fromWebsiteId,
      $toWebsiteId,
      array $templateIds = array()
  ) {
    if (count($templateIds) > 0) {
      $this->deleteTemplates($templateIds, $toWebsiteId);
    }
    
    $result = $this->execute(
        'copyToNewWebsite',
        array($fromWebsiteId, $toWebsiteId, $templateIds)
    );

    return $result;
  }

  /**
   * Gibt ein Template-Objekt anhand der ID und Website-Zugehoerigkeit zurueck
   *
   * @param string $id
   * @param string $websiteId
   *
   * @return \Cms\Data\Template
   */
  public function getById($id, $websiteId)
  {
    return $this->execute('getById', array($id, $websiteId));
  }

  /**
   * gibt mehrere Templates einer Website anhand ihrer IDs zurueck
   *
   * @param  array  $templateIds
   * @param  string $websiteId
   * @return array[] \Cms\Data\Template
   */
  public function getByIds(array $templateIds, $websiteId)
  {
    $templates = $this->execute(
        'getByIds',
        array($templateIds, $websiteId)
    );

    return $templates;
  }

  /**
   * Loescht ein Template anhand der Id und WebsiteId aus der DB
   *
   * @param string $id
   * @param string $websiteId
   */
  public function delete($id, $websiteId)
  {
    return $this->execute('delete', array($id, $websiteId));
  }
  /**
   * Loescht mehrere Templates anhand der Ids und WebsiteId aus der DB
   *
   * @param array $ids
   * @param type $websiteId
   */
  public function deleteTemplates(array $ids, $websiteId)
  {
    return $this->execute('deleteByIds', array($websiteId, $ids));
  }

  /**
   * @param string  $websiteid
   */
  public function deleteByWebsiteId($websiteId)
  {
    return $this->execute('deleteByWebsiteId', array($websiteId));
  }

  /**
   * Erstellt ein neues Template in der DB
   *
   * @param string $websiteId
   * @param array  $columnsValues
   *
   * @return \Cms\Data\Template
   */
  public function create($websiteId, array $columnsValues)
  {
    return $this->execute('create', array($websiteId, $columnsValues));
  }

  /**
   * @param string $id
   * @param string $websiteId
   * @param array  $columnsValues
   */
  public function update($id, $websiteId, array $columnsValues)
  {
    return $this->execute('update', array($id, $websiteId, $columnsValues));
  }

  /**
   * Gibt Templates anhand der gefunden Treffer von $needle im Content und
   * der passenden Website Id zurueck
   *
   * @param string $needle
   * @param string $websiteId
   * @return  array[] \Data\Template
   */
  public function searchInContent($needle, $websiteId)
  {
    return $this->execute(
        'searchInContent',
        array($needle, $websiteId)
    );
  }
  
  /**
   * returns the used module ids for the given website and template id
   *
   * @param  string $websiteId
   * @param  string $id
   * @return array
   */
  public function getUsedModuleIds($websiteId, $id)
  {
    return $this->getDao()->getUsedModuleIds($websiteId, $id);
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function existsTemplateAlready($id, $websiteId)
  {
    return $this->execute('existsTemplate', array($id, $websiteId));
  }

  /**
   * @param  string $websiteId
   * @param  string $id
   * @return array
   */
  public function findByWebsiteIdAndModuleId($websiteId, $moduleId)
  {
    return $this->execute(
        'findByWebsiteIdAndModuleId',
        array($websiteId, $moduleId)
    );
  }

  /**
   * returns all ids of templates related to the website given by website id
   *
   * @param string $websiteId
   * @return array
   */
  public function getIdsByWebsiteId($websiteId)
  {
    return $this->getDao()->getIdsByWebsiteId($websiteId);
  }
}
