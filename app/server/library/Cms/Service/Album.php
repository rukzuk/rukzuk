<?php
namespace Cms\Service;

use Cms\Exception as CmsException;
use Cms\Service\Base\Dao as DaoServiceBase;

/**
 * Album
 *
 * @package      Cms
 * @subpackage   Service
 */

class Album extends DaoServiceBase
{
  /**
   * Kopiert alle Alben einer Website zu einer anderen Website
   *
   * @param string $fromWebsiteId
   * @param string $toWebsiteId
   * @return true
   * @throws \Cms\Exception
   */
  public function copyAlbumsToNewWebsiteId($fromWebsiteId, $toWebsiteId)
  {
    $result = $this->execute(
        'copyAlbumsToNewWebsite',
        array($fromWebsiteId, $toWebsiteId)
    );
    return $result;
  }
  
  /**
   * @param  string $websiteId
   * @param  array  $createValues
   * @return \Cms\Data\Album
   */
  public function create($websiteId, array $createValues)
  {
    return $this->execute('create', array($websiteId, $createValues));
  }
  
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $updateValues
   * @return \Cms\Data\Album
   */
  public function edit($id, $websiteId, array $updateValues)
  {
    return $this->execute('update', array($id, $websiteId, $updateValues));
  }
  
  /**
   * @param  string $websiteId
   * @return array[] \Cms\Data\Album
   */
  public function getAllByWebsiteId($websiteId)
  {
    return $this->execute('getAllByWebsiteId', array($websiteId));
  }
  
  /**
   * @param  string    $albumId
   * @return \Cms\Data\Album
   */
  public function getById($albumId, $websiteId)
  {
    return $this->execute('getById', array($albumId, $websiteId));
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
}
