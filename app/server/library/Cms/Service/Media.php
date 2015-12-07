<?php
namespace Cms\Service;

use Cms\Service\Base\Dao as DaoServiceBase;
use Cms\Service\Media\File as MediaFileService;
use Seitenbau\Registry as Registry;

/**
 * Media service
 *
 * @package      Application
 * @subpackage   Controller
 */
class Media extends DaoServiceBase
{
  /**
   * @param  string  $websiteId
   * @return integer
   */
  public function getSizeByWebsiteId($websiteId)
  {
    return $this->execute('getSizeByWebsiteId', array($websiteId));
  }

  /**
   * @param  string  $albumId
   * @param  string  $websiteId
   * @param  array   $mediaIds
   * @return boolean
   */
  public function moveMediasToAlbum($albumId, $websiteId, array $mediaIds)
  {
    return $this->execute(
        'moveMediasToAlbum',
        array($albumId, $websiteId, $mediaIds)
    );
  }
  
  /**
   * @param string $sourceWebsiteId
   * @param string $newWebsiteId
   */
  public function copyMediaToNewWebsite($sourceWebsiteId, $newWebsiteId)
  {
    $config = Registry::getConfig();
    $mediaDirectory = $config->media->files->directory;
    $mediaFileService = new MediaFileService($mediaDirectory);

    $sourceMediaDirectory= $mediaDirectory
      . DIRECTORY_SEPARATOR . $sourceWebsiteId;

    if (is_dir($sourceMediaDirectory)) {
      $mediaFileService->copyMediaFileToNewWebsite($sourceWebsiteId, $newWebsiteId);
    }

    return $this->execute('copyMediaToNewWebsite', array($sourceWebsiteId, $newWebsiteId));
  }

  /**
   * @param string $websiteId
   * @param array  $columnValues
   * @return \Cms\Data\User
   */
  public function create($websiteId, $columnValues, $useColumnsValuesId = false)
  {
    return $this->execute('create', array($websiteId, $columnValues, $useColumnsValuesId));
  }
  
  /**
   * @param array  $ids
   * @param string $websiteId
   */
  public function delete(array $ids, $websiteId)
  {
    if (count($ids) > 0) {
      return $this->execute('deleteByIds', array($websiteId, $ids));
    }
  }

  /**
   * @param string $id
   * @param string $websiteId
   * @param array  $editValues
   */
  public function edit($id, $websiteId, array $editValues)
  {
    return $this->execute('edit', array($id, $websiteId, $editValues));
  }

  /**
   * @param string $id
   * @param string  $websiteId
   * @return \CMS\Data\Media
   */
  public function getById($id, $websiteId)
  {
    $media = $this->execute('getById', array($id, $websiteId));
    return $media;
  }

  /**
   * Gibt mehrere Medien-Objekte zurueck
   *
   * @param array   $ids
   * @param string  $websiteId
   * @return \CMS\Data\Media[] Medien-Objekte
   */
  public function getMultipleByIds(array $ids, $websiteId)
  {
    $medias = $this->execute('getMultipleByIds', array($ids, $websiteId));
    return $medias;
  }
  
  /**
   * @param  string  $websiteId
   * @param  array   $filterValues
   * @param  boolean $ignoreLimit Whether to ignore a limit or not. Defaults to false.
   * @return array
   */
  public function getByWebsiteIdAndFilter(
      $websiteId,
      array $filterValues = array(),
      $ignoreLimit = false
  ) {
    $medias = $this->execute(
        'getByWebsiteIdAndFilter',
        array($websiteId, $filterValues, $ignoreLimit)
    );
    return $medias;
  }

  /**
   * Gibt die Anzahl der Media-Items zurueck. Optional koennen die Media-Items
   * mit Filter eingeschraenkt werden
   *
   * @param string $websiteId
   * @param array $filterValues
   * @return  int
   */
  public function getCountMedia($websiteId, array $filterValues = array())
  {
    $medias = $this->execute(
        'getByWebsiteIdAndFilter',
        array(
        $websiteId,
        $filterValues,
        true
        )
    );
    
    return count($medias);
  }

  /**
   * @param string $websiteId
   * @param string $id
   *
   * @return boolean
   */
  public function existsMedia($websiteId, $id)
  {
    return $this->execute('existsMedia', array($id, $websiteId));
  }
}
