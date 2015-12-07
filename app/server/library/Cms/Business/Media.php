<?php
namespace Cms\Business;

use Cms\Quota;
use Seitenbau\Registry as Registry;
use Cms\Service\Media\File as MediaFileService;
use Cms\Service\Media\Cache as MediaCacheService;

/**
 * Stellt die Business-Logik fuer Media zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Media extends Base\Service
{
  /**
   * @return \Cms\Data\MediaQuota
   */
  public function getQuota()
  {
    $quota = new Quota();
    return $quota->getMediaQuota();
  }

  /**
   * @param  string  $websiteId
   * @return integer
   */
  public function getSizeByWebsiteId($websiteId)
  {
    return $this->getService()->getSizeByWebsiteId($websiteId);
  }
  
  /**
   * @param  string  $albumId
   * @param  string  $websiteId
   * @param  array   $mediaIds
   * @return boolean
   */
  public function moveMediasToAlbum($albumId, $websiteId, array $mediaIds)
  {
    return $this->getService()->moveMediasToAlbum($albumId, $websiteId, $mediaIds);
  }

  /**
   * @param  string  $websiteId
   * @param  array   $filterValues
   * @param  boolean $ignoreLimit Defaults to false
   * @return array
   */
  public function getAllByWebsiteId(
      $websiteId,
      array $filterValues = array(),
      $ignoreLimit = false
  ) {
    return $this->getService()->getByWebsiteIdAndFilter(
        $websiteId,
        $filterValues,
        $ignoreLimit
    );
  }

  /**
   * Loescht Media-Items
   *
   * IDs der Media items, welche nicht geloescht werden konnten, werden
   * zurueckgegeben
   *
   * @param  array  $mediaIds
   * @param  string $websiteId
   * @return array
   */
  public function delete(array $mediaIds, $websiteId, $checkNonDeletables = true)
  {
    $notDeletableIds = array();
    if (count($mediaIds) == 0) {
      return $notDeletableIds;
    }

    if ($checkNonDeletables === true) {
      foreach ($mediaIds as $index => $mediaId) {
        $notDeletableInfos = array(
          'modules' => array(),
          'templates' => array(),
          'pages' => array()
        );
        $deleteMediaSuccess = true;

        $mediaRelations = $this->getMediaRelations($mediaId, $websiteId);

        if (count($mediaRelations) > 0) {
          foreach ($notDeletableInfos as $relationKey => $relationValues) {
            if (isset($mediaRelations[$relationKey])) {
              $notDeletableInfos[$relationKey] = $mediaRelations[$relationKey];
              $deleteMediaSuccess = false;
            }
          }
          unset($mediaIds[$index]);
        }

        if ($deleteMediaSuccess == false) {
          try {
            $mediaName = $this->getService()->getById($mediaId, $websiteId)->getName();
          } catch (\Exception $e) {
            $mediaName = 'unknown';
          }

            \Cms\ExceptionStack::addException(
                new \Cms\Exception(236, __METHOD__, __LINE__, array(
                'id' => $mediaId,
                'name' => $mediaName,
                'infos' => $notDeletableInfos
                ))
            );
          $notDeletableIds[] = $mediaId;
        }
      }
    }

    $config = Registry::getConfig();
    $mediaDirectory = $config->media->files->directory;
    $mediaFileService = new MediaFileService($mediaDirectory);

    $mediaCacheDirectory = $config->media->cache->directory;
    $cacheFileService = new MediaCacheService($mediaCacheDirectory);

    foreach ($mediaIds as $index => $id) {
      $media = $this->getService()->getById($id, $websiteId);

      if ($media !== null) {
        $mediaFileService->delete($websiteId, $media->getFile());
        $cacheFileService->delete($websiteId, $media->getFile());
      }
    }

    $this->getService()->delete($mediaIds, $websiteId);


    return $notDeletableIds;
  }

  /**
   * Gibt zurueck, wo das Media-Item verwendet wird
   *
   * Es wird ein Array zurueckgegeben, welches als Keys die Gruppe, in der das
   * Media Item verknuepft ist, zurueckgibt und als Value die jeweiligen IDs
   * innerhalb dieser Gruppe
   * Bsp: array('module' => array('MODUL-111-MODUL', 'MODUL-222-MODUL'),
   *            'templates' => array('TEMPLATE-333-TEMPLATE'))
   *
   * @param string  $mediaId
   * @param string  $websiteId
   * @return  array
   */
  private function getMediaRelations($mediaId, $websiteId)
  {
    $mediaRelations = array();

    $relatedTemplates = $this->getBusiness('Template')->searchInContent(
        $mediaId,
        $websiteId
    );
    if (count($relatedTemplates) > 0) {
      $mediaRelations['templates'] = array();
      foreach ($relatedTemplates as $relatedTemplate) {
        $mediaRelations['templates'][] = array(
          'id' => $relatedTemplate->getId(),
          'name' => $relatedTemplate->getName()
        );
      }
    }

    $relatedPages = $this->getBusiness('Page')->findByMediaAndWebsiteId(
        $mediaId,
        $websiteId
    );
        
    if (count($relatedPages) > 0) {
      $mediaRelations['pages'] = array();
      foreach ($relatedPages as $relatedPage) {
        $mediaRelations['pages'][] = array(
          'id' => $relatedPage->getId(),
          'name' => $relatedPage->getName()
        );
      }
    }

    return $mediaRelations;
  }

  /**
   * @param array  $identity
   * @param string $rightname
   * @param mixed  $check
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    if ($this->isSuperuser($identity)) {
      return true;
    }
    
    switch ($rightname) {
      case 'upload':
        if ($this->checkWebsitePrivilegeForIdentity($identity, $check['websiteId'], 'modules', 'all')) {
          return true;
        }
        if ($this->checkWebsitePrivilegeForIdentity($identity, $check['websiteId'], 'templates', 'all')) {
          return true;
        }
/* can edit min. one page
          if ($this->checkWebsitePrivilegeForIdentity($identity, $check['websiteId'], 'page', 'all')) {
            return true;
          }
*/
        if ($this->isUserInAnyWebsiteGroup($identity, $check['websiteId'])) {
          return true;
        }
            break;
    }
    
    return false;
  }
}
