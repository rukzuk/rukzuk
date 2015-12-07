<?php
namespace Cms\Business;

use Seitenbau\Registry;
use Cms\Exception as CmsException;

/**
 * Stellt die Business-Logik fuer Album zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */
class Album extends Base\Service
{
  /**
   * @param string $sourceWebsiteId
   * @param string $newWebsiteId
   */
  public function copyAlbumsToNewWebsite($sourceWebsiteId, $newWebsiteId)
  {
    $this->getService()->copyAlbumsToNewWebsiteId($sourceWebsiteId, $newWebsiteId);
  }

  /**
   * @param  string $websiteId
   * @param  array  $createValues
   * @return \Orm\Entity\Album
   */
  public function create($websiteId, array $createValues)
  {
    return $this->getService()->create($websiteId, $createValues);
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $updateValues
   * @return \Orm\Entity\Album
   */
  public function edit($id, $websiteId, array $updateValues)
  {
    return $this->getService()->edit($id, $websiteId, $updateValues);
  }

  /**
   * @param  string $websiteId
   * @return array[] \Orm\Entity\Album
   */
  public function getAllByWebsiteId($websiteId)
  {
    return $this->getService()->getAllByWebsiteId($websiteId);
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @return array
   */
  public function delete($id, $websiteId)
  {
    $mediaBusiness = $this->getBusiness('Media');
    $filterAlbumId = array('albumid' => $id);
    $albumMedia = $mediaBusiness->getAllByWebsiteId($websiteId, $filterAlbumId);

    $deletableMediaIds = array();
    foreach ($albumMedia as $media) {
      $deletableMediaIds[] = $media->getId();
    }
    $notDeletableMediaIds = $mediaBusiness->delete($deletableMediaIds, $websiteId);

    if (count($notDeletableMediaIds) === 0) {
      $this->getService()->delete($id, $websiteId);
    }

    return $notDeletableMediaIds;
  }

  /**
   * Pruefung, ob der angemeldete User die Rechte fuer die aufgerufene Aktion
   * besitzt
   *
   * @param array  $identity  Benutzerinformationen
   * @param string $rightname Name des Rechts, auf das geprueft wird
   * @param mixed  $check
   * @return boolean
  */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // Superuser darf alles
    if ($this->isSuperuser($identity)) {
      return true;
    }

    switch ($rightname)
    {
      case 'create':
      case 'edit':
      case 'getAll':
      case 'delete':
            return $this->isUserInAnyWebsiteGroup($identity, $check);
        break;
    }
    
    // Default: Keine Rechte
    return false;
  }
}
