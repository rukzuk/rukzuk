<?php
namespace Cms\Dao\Media;

use Cms\Dao\Media as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Orm\Entity\Media as Media;
use Cms\Exception as CmsException;
use Exception;

/**
 * Doctrine Dao fuer Media
 *
 * @package      Application
 * @subpackage   Controller
 */
class Doctrine extends DoctrineBase implements Dao
{
  /**
   * @param string $websiteId
   * @return integer
   */
  public function getSizeByWebsiteId($websiteId)
  {
    return  $this->getEntityManager()
                 ->getRepository('Orm\Entity\Media')
                 ->getSizeByWebsiteId($websiteId);
  }

  /**
   * @param  string  $albumId
   * @param  string  $websiteId
   * @param  array   $ids
   * @return boolean
   */
  public function moveMediasToAlbum($albumId, $websiteId, array $ids)
  {
    /** @var \Orm\Entity\Media[] $mediasToMove */
    $mediasToMove = array();

    if (!count($ids) === 0) {
      return false;
    }

    try {
      /** @var \Orm\Entity\Album $album */
      $album = $this->getEntityManager()
                    ->getRepository('Orm\Entity\Album')
                    ->findOneBy(array('id' => $albumId,
                                      'websiteid' => $websiteId));
      if ($album === null) {
        throw new CmsException('265', __METHOD__, __LINE__);
      }

      foreach ($ids as $id) {
        $media = $this->getEntitymanager()
                      ->getRepository('Orm\Entity\Media')
                      ->findOneBy(array('id' => $id,
                                        'websiteid' => $websiteId));
        if ($media === null) {
          throw new CmsException(266, __METHOD__, __LINE__);
        }
        $mediasToMove[] = $media;
      }

      if (count($mediasToMove) > 0) {
        $entityManager = $this->getEntityManager();

        foreach ($mediasToMove as $mediaToMove) {
          $mediaToMove->setAlbumId($album->getId());
          $entityManager->persist($mediaToMove);
        }

        $entityManager->flush();

        return true;
      }

      return false;
    } catch (Exception $e) {
      throw new CmsException(264, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param string   $websiteId
   * @param array    $columnValues
   * @param boolean  $useColumnsValuesId
   * @return \Orm\Entity\Media
   * @throws \Cms\Exception
   */
  public function create($websiteId, array $columnValues, $useColumnsValuesId = false)
  {
    if (!$useColumnsValuesId) {
      $website = $this->getEntityManager()
                     ->getRepository('Orm\Entity\Website')
                     ->findOneById($websiteId);

      if ($website === null) {
        throw new CmsException('268', __METHOD__, __LINE__);
      }

      $albumId = isset($columnValues['albumid']) ? $columnValues['albumid'] : null;
      $album = $this->getEntityManager()
                    ->getRepository('Orm\Entity\Album')
                    ->findOneBy(array('id' => $albumId,
                                      'websiteid' => $websiteId));
      if ($album === null) {
        throw new CmsException('267', __METHOD__, __LINE__);
      }
    }

    $media = new Media();
    if ($useColumnsValuesId && isset($columnValues['id'])) {
      $media->setId($columnValues['id']);
    } else {
      $media->setNewGeneratedId();
    }
    if (isset($columnValues['dateuploaded'])) {
      $media->setDateUploaded($columnValues['dateuploaded']);
    } else {
      $media->setDateUploaded(time());
    }
    if (isset($columnValues['albumid'])) {
    /** @var \Orm\Entity\Album $album */
      $album = $this->getEntitymanager()
                    ->getRepository('Orm\Entity\Album')
                    ->findOneBy(array(
                        'id' => $columnValues['albumid'],
                        'websiteid' => $websiteId));
      $media->setAlbumId($album->getId());
    }

    $media->setWebsiteId($websiteId);
    $media->setName($columnValues['name']);
    if (isset($columnValues['filename'])) {
      $media->setFilename($columnValues['filename']);
    }
    if (isset($columnValues['file'])) {
      $media->setFile($columnValues['file']);
    }
    $media->setExtension($columnValues['extension']);
    $media->setSize($columnValues['size']);
    if (isset($columnValues['lastmod'])) {
      $media->setLastmod($columnValues['lastmod']);
    } else {
      $media->setLastmod(time());
    }
    $media->setType($columnValues['type']);
    if (isset($columnValues['mimetype'])) {
      $media->setMimetype($columnValues['mimetype']);
    }

    try {
      $entityManager = $this->getEntityManager();
      $entityManager->persist($media);
      $entityManager->flush();
      $entityManager->refresh($media);
      $this->clearEntityManager();
    } catch (Exception $e) {
      throw new CmsException(104, __METHOD__, __LINE__, null, $e);
    }

    return $media;
  }

  /**
   * @param  array $ids
   * @param  string $websiteId
   * @throws \Cms\Exception
   */
  public function deleteByIds($websiteId, array $ids)
  {
    $dql = sprintf(
        "DELETE FROM Orm\Entity\Media m
                    WHERE m.id IN ('%s')
                    AND m.websiteid LIKE '%s'",
        implode("','", $ids),
        $websiteId
    );

    $query = $this->getEntityManager()->createQuery($dql);
    return $query->getResult();
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $editValues
   * @throws \Cms\Exception
   */
  public function edit($id, $websiteId, array $editValues)
  {
    try {
      /** @var \Orm\Entity\Media $media */
      $media = $this->getEntitymanager()
                    ->getRepository('Orm\Entity\Media')
                    ->findByIdAndWebsiteId($id, $websiteId);
    } catch (Exception $e) {
      throw new CmsException(263, __METHOD__, __LINE__, null, $e);
    }

    if ($media === null) {
      throw new CmsException(261, __METHOD__, __LINE__, array('mediaId' => $id));
    }
    
    try {
      $media->setLastmod(time());

      if (isset($editValues['name'])) {
        $media->setName($editValues['name']);
      }
      if (isset($editValues['dateuploaded'])) {
        $media->setDateUploaded($editValues['dateuploaded']);
      }
      if (isset($editValues['file'])) {
        $media->setFile($editValues['file']);
      }
      if (isset($editValues['filename'])) {
        $media->setFilename($editValues['filename']);
      }
      if (isset($editValues['size'])) {
        $media->setSize($editValues['size']);
      }
      if (isset($editValues['type'])) {
        $media->setType($editValues['type']);
      }
      if (isset($editValues['mimetype'])) {
        $media->setMimetype($editValues['mimetype']);
      }
      if (isset($editValues['albumid'])) {
        /** @var \Orm\Entity\Album $album */
        $album = $this->getEntitymanager()
                      ->getRepository('Orm\Entity\Album')
                      ->findOneBy(array(
                          'id' => $editValues['albumid'],
                          'websiteid' => $websiteId));
        $media->setAlbumid($album->getId());
      }

      $this->getEntitymanager()->persist($media);
      $this->getEntitymanager()->flush();
      $this->getEntitymanager()->detach($media);
      unset($media);
      $media = $this->getEntitymanager()
                    ->getRepository('Orm\Entity\Media')
                    ->findByIdAndWebsiteId($id, $websiteId);
      $this->clearEntityManager();
      return $media;
    } catch (Exception $e) {
      throw new CmsException(263, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string  $websiteId
   * @param  array   $filter
   * @param  boolean $ignoreLimit Defaults to false
   * @return array
   */
  public function getByWebsiteIdAndFilter(
      $websiteId,
      array $filter = array(),
      $ignoreLimit = false
  ) {
    $data = $this->getEntitymanager()
                ->getRepository('Orm\Entity\Media')
                ->findByWebsiteIdAndFilter(
                    $websiteId,
                    $filter,
                    $ignoreLimit
                );
    $this->clearEntityManager();
    return $data;
  }

  /**
   * @param  string $id
   * @param string  $websiteId
   * @return \Orm\Entity\Media
   * @throws \Cms\Exception
   */
  public function getById($id, $websiteId)
  {
    $media = $this->getEntitymanager()
                ->getRepository('Orm\Entity\Media')
                ->findByIdAndWebsiteId($id, $websiteId);
    $this->clearEntityManager();
    if ($media === null) {
      throw new CmsException('232', __METHOD__, __LINE__);
    }
    
    return $media;
  }

  /**
   * Gibt mehrere Medien-Objekte zurueck
   *
   * @param array $ids
   * @param type $websiteId
   * @return array
   */
  public function getMultipleByIds(array $ids, $websiteId)
  {
    $data = $this->getEntityManager()
                ->getRepository('Orm\Entity\Media')
                ->findMultipleByIds($ids, $websiteId);
    $this->clearEntityManager();
    return $data;
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @return \Orm\Entity\Media
   * @throws \Cms\Exception
   */
  public function getByIdAndWebsiteId($id, $websiteId)
  {
    $data = $this->getEntitymanager()
                ->getRepository('Orm\Entity\Media')
                ->findByIdAndWebsiteId($id, $websiteId);
    $this->clearEntityManager();
    return $data;
  }

  /**
   * @param  string  $id
   * @param  string  $websiteId
   * @return boolean
   */
  public function existsMedia($id, $websiteId)
  {
    try {
      $data = $this->getEntityManager()
                   ->getRepository('Orm\Entity\Media')
                   ->findOneBy(array('id' => $id,
                                     'websiteid' => $websiteId));
      $this->clearEntityManager();
    } catch (Exception $e) {
      throw new CmsException(202, __METHOD__, __LINE__, null, $e);
    }
    return $data !== null;
  }

  /**
   * Copies all MediaItems to new website id
   *
   * @param  string $sourceWebsiteId
   * @param  string $newWebsiteId
   * @return boolean
   */
  public function copyMediaToNewWebsite($sourceWebsiteId, $newWebsiteId)
  {
    /** @var \Orm\Entity\Media[] $sourceMedias */
    $sourceMedias = $this->getEntitymanager()
                         ->getRepository('Orm\Entity\Media')
                         ->findBy(array('websiteid' => $sourceWebsiteId));

    if ($sourceMedias === null) {
      throw new CmsException('232', __METHOD__, __LINE__);
    }

    foreach ($sourceMedias as $sourceMedia) {
      $newMedia = new Media();
      $newMedia->setId($sourceMedia->getId());

      $newMedia->setWebsiteid($newWebsiteId);
      $newMedia->setAlbumId($sourceMedia->getAlbumId());
      $newMedia->setName($sourceMedia->getName());
      $newMedia->setFilename($sourceMedia->getFilename());
      $newMedia->setSize($sourceMedia->getSize());
      $newMedia->setLastmod(time());
      $newMedia->setFile($sourceMedia->getFile());
      $newMedia->setType($sourceMedia->getType());
      $newMedia->setMimetype($sourceMedia->getMimetype());
      $newMedia->setDateUploaded($sourceMedia->getDateUploaded());
      $newMedia->setExtension($sourceMedia->getExtension());

      $this->getEntitymanager()->persist($newMedia);
      $this->getEntitymanager()->flush();
    }
    $this->clearEntityManager();

    return true;
  }
}
