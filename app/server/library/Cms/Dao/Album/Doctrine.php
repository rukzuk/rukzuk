<?php
namespace Cms\Dao\Album;

use Cms\Exception as CmsException;
use Cms\Dao\Album as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Orm\Entity\Album as Album;

/**
 * Doctrine
 *
 * @package      Cms
 * @subpackage   Dao
 */

class Doctrine extends DoctrineBase implements Dao
{
  /**
   * Kopiert alle vorhandenen Alben einer Website zu einer neuen Website
   *
   * @param string $websiteId
   * @param string $newWebsiteId
   * @return true
   * @throws \Cms\Exception
   */
  public function copyAlbumsToNewWebsite($websiteId, $newWebsiteId)
  {
    try {
      $albums = $this->getEntityManager()
                     ->getRepository('Orm\Entity\Album')
                     ->findByWebsiteid($websiteId);
    } catch (Exception $e) {
      throw new CmsException(703, __METHOD__, __LINE__, null, $e);
    }

    foreach ($albums as $album) {
      $newAlbum = clone $album;
      $newAlbum->setWebsiteId($newWebsiteId);
      $this->getEntityManager()->persist($newAlbum);
    }
    $this->getEntityManager()->flush();

    return true;
  }

  /**
   * @param  string   $websiteId
   * @param  array    $columnValues
   * @param  boolean  $useColumnsValuesId
   * @return \Orm\Entity\Album
   */
  public function create($websiteId, array $columnValues, $useColumnsValuesId = false)
  {
    $album = new Album();

    if ($useColumnsValuesId && isset($columnValues['id'])) {
      $album->setId($columnValues['id']);
    } else {
      $album->setNewGeneratedId();
    }

    if (isset($columnValues['name'])) {
      $album->setName($columnValues['name']);
    }
    $album->setWebsiteid($websiteId);

    try {
      $entityManager = $this->getEntityManager();
      $entityManager->persist($album);
      $entityManager->flush();
      $entityManager->refresh($album);
    } catch (Exception $e) {
      throw new CmsException(404, __METHOD__, __LINE__, null, $e);
    }

    return $album;
  }

  /**
   * @param string $id
   * @param string $websiteId
   * @param array  $columnsValues
   */
  public function update($id, $websiteId, array $columnsValues)
  {
    try {
      $album = $this->getEntityManager()
                    ->getRepository('Orm\Entity\Album')
                    ->findOneBy(array('id' => $id,
                                      'websiteid' => $websiteId));

      if ($album === null) {
        throw new CmsException('402', __METHOD__, __LINE__);
      }

      if (isset($columnsValues['name'])) {
        $album->setName($columnsValues['name']);
      }

      $entityManager = $this->getEntityManager();
      $entityManager->persist($album);
      $entityManager->flush();
      $entityManager->refresh($album);

      return $album;
    } catch (Exception $e) {
      throw new CmsException(406, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string $websiteId
   * @return array
   */
  public function getAllByWebsiteId($websiteId)
  {
    $all = $this->getEntitymanager()->getRepository('Orm\Entity\Album')
                                    ->findByWebsiteIdAndOrderByName($websiteId);

    if ($all === null) {
      throw new CmsException('402', __METHOD__, __LINE__);
    }

    return $all;
  }

  /**
   * @param  string $id
   * @return \Orm\Entity\Album
   */
  public function getById($id, $websiteId)
  {
    $data = array(
      'id' => $id,
      'websiteid' => $websiteId
    );

    try {
      $album = $this->getEntityManager()
                    ->getRepository('Orm\Entity\Album')
                    ->findOneBy($data);
    } catch (\Exception $e) {
      throw new CmsException(408, __METHOD__, __LINE__, $data, $e);
    }

    if ($album === null) {
      throw new CmsException(402, __METHOD__, __LINE__);
    }

    return $album;
  }

  /**
   * @param  string $websiteId
   * @param  string $albumName
   * @return array
   */
  public function getByWebsiteIdAndName($websiteId, $albumName)
  {
    $foundAlbum = $this->getEntitymanager()->getRepository('Orm\Entity\Album')
                          ->findByWebsiteIdAndName($websiteId, $albumName);

    if ($foundAlbum === null) {
      throw new CmsException(405, __METHOD__, __LINE__);
    }

    return $foundAlbum;
  }
  

  /**
   * @param  string  $id
   * @param  string  $websiteId
   * @return boolean
   */
  public function existsAlbum($id, $websiteId)
  {
    try {
      $data = $this->getEntityManager()
                   ->getRepository('Orm\Entity\Album')
                   ->findOneBy(array(
                      'id' => $id,
                      'websiteid' => $websiteId
                   ));
    } catch (Exception $e) {
      throw new CmsException(408, __METHOD__, __LINE__, null, $e);
    }
    return $data !== null;
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function delete($id, $websiteId)
  {
    $album = $this->getEntityManager()
                  ->getRepository('Orm\Entity\Album')
                  ->findOneBy(array('id' => $id,
                                    'websiteid' => $websiteId));

    if ($album === null) {
      throw new CmsException(402, __METHOD__, __LINE__);
    }
    try {
      $entityManager = $this->getEntityManager();
      $entityManager->remove($album);
      $entityManager->flush();
      return true;
    } catch (Exception $e) {
      throw new CmsException(410, __METHOD__, __LINE__, null, $e);
    }
  }
}
