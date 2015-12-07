<?php
namespace Cms\Service;

use Cms\Exception as CmsException;
use Cms\Service\Base\Dao as DaoServiceBase;

/**
 * Group
 *
 * @package      Cms
 * @subpackage   Service
 */

class Group extends DaoServiceBase
{
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $pageRights
   * @return \Orm\Entity\Group
   */
  public function setPageRights($id, $websiteId, array $pageRights)
  {
    return $this->execute('setPageRights', array($id, $websiteId, $pageRights));
  }
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  string $name
   * @return string Id der kopierten Gruppe
   */
  public function copy($id, $websiteId, $name)
  {
    return $this->execute('copy', array($id, $websiteId, $name));
  }
  /**
   * @param  string $websiteId
   * @param  array  $createValues
   * @return \Cms\Data\Group
   */
  public function create($websiteId, array $createValues)
  {
    return $this->execute('create', array($websiteId, $createValues));
  }
  
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $editValues
   * @return \Cms\Data\Group
   */
  public function edit($id, $websiteId, array $editValues)
  {
    return $this->execute('update', array($id, $websiteId, $editValues));
  }
  
  /**
   * @param  string $websiteId
   * @return array[] \Cms\Data\Group
   */
  public function getAllByWebsiteId($websiteId)
  {
    return $this->execute('getAllByWebsiteId', array($websiteId));
  }
  /**
   * @param  string $userId
   * @param  string $websiteId
   * @return array[] \Cms\Data\Group
   */
  public function getAllByUserAndWebsiteId($userId, $websiteId)
  {
    return $this->execute('getAllByUserAndWebsiteId', array($userId, $websiteId));
  }
  /**
   * @param  string $id
   * @param  string $websiteId
   * @return \Cms\Data\Group
   */
  public function getByIdAndWebsiteId($id, $websiteId)
  {
    return $this->execute('getByIdAndWebsiteId', array($id, $websiteId));
  }
  
  /**
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function delete($id, $websiteId)
  {
    return $this->execute('delete', array($id, $websiteId));
  }
  
  /**
   * @param  string  $userId
   * @return \Cms\Data\Group[]
   */
  public function getAllByUserId($userId)
  {
    return $this->execute('getAllByUserId', array($userId));
  }
  
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $userIds
   * @return boolean
   */
  public function addUsers($id, $websiteId, array $userIds)
  {
    // check if the users with the given ids exists
    $this->getService('User')->getByIds($userIds);
    return $this->execute('addUsers', array($id, $websiteId, $userIds));
  }
  
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $userIds
   * @return boolean
   */
  public function removeUsers($id, $websiteId, array $userIds)
  {
    return $this->execute('removeUsers', array($id, $websiteId, $userIds));
  }

  /**
   * Prueft, ob Gruppen zu einer Website existieren
   *
   * Wird "getData" als true uebergeben, werden bei vorhandenen Gruppen mit
   * zurueckgegeben
   *
   * @param  string $websiteId
   * @param  boolean $getData
   * @return boolean|array[] \Cms\Data\Group
   */
  public function existsGroupsForWebsite($websiteId, $getData = false)
  {
    if ($getData == true) {
      try {
        return $this->getAllByWebsiteId($websiteId);
      } catch (CmsException $e) {
        return false;
      }
    }
    
    return $this->execute('existsGroupsForWebsite', array($websiteId));
  }
  
  /**
   * @return boolean
   */
  public function deleteAll()
  {
    return $this->execute('deleteAll', array());
  }
}
