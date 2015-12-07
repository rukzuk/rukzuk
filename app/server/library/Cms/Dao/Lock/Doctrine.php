<?php
namespace Cms\Dao\Lock;

use Cms\Exception as CmsException;
use Cms\Dao\Lock as Dao;
use Orm\Entity\Lock;
use Cms\Dao\Doctrine as DoctrineBase;

/**
 * Doctrine
 *
 * @package      Cms
 * @subpackage   Dao
 */
class Doctrine extends DoctrineBase implements Dao
{
  /**
   * @param string  $websiteId
   * @return array[] \Orm\Entity\locks
   * @throws \Cms\Exception
   */
  public function getAll($websiteId)
  {
    try {
      $locks = $this->getEntityManager()
                      ->getRepository('Orm\Entity\Lock')
                      ->findByWebsiteId($websiteId);
    } catch (\Exception $e) {
      throw new CmsException(1500, __METHOD__, __LINE__, null, $e);
    }

    return $locks;
  }

  /**
   * Erstellt einen Lock-Eintrag
   *
   * @param string  $userId
   * @param string  $runId
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @return boolean
   */
  public function lockItem($userId, $runId, $itemId, $websiteId, $type)
  {
    try {
      $lock = new Lock();

      $lock->setUserid($userId);
      $lock->setRunid($runId);
      $lock->setItemid($itemId);
      $lock->setWebsiteid($websiteId);
      $lock->setType($type);
      $lock->setStarttime(time());
      $lock->setLastactivity($lock->getStarttime());

      $this->getEntityManager()->persist($lock);
      $this->getEntityManager()->flush();
      $this->getEntityManager()->refresh($lock);
    } catch (\Exception $e) {
      throw new CmsException(1503, __METHOD__, __LINE__, null, $e);
    }

    return $lock;
  }

  /**
   * Aktualisiert einen Lock
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @return boolean
   */
  public function update($itemId, $websiteId, $type)
  {
    try {
      // Lock ermitteln
      $lock = $this->getByIdAndType($itemId, $websiteId, $type);

      // Lock aktualisieren
      $lock->setLastactivity(time());
      $this->getEntityManager()->persist($lock);
      $this->getEntityManager()->flush();
      $this->getEntityManager()->refresh($lock);
    } catch (\Exception $e) {
      throw new CmsException(1505, __METHOD__, __LINE__, null, $e);
    }

    return $lock;
  }

  /**
   * Ueberschreibt einen Lock-Eintrag
   *
   * @param string  $userId
   * @param string  $runId
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @return boolean
   */
  public function override($userId, $runId, $itemId, $websiteId, $type)
  {
    try {
      // Lock ermitteln
      $lock = $this->getByIdAndType($itemId, $websiteId, $type);

      // Neue Lockdaten setzen
      $lock->setUserid($userId);
      $lock->setRunid($runId);
      $lock->setItemid($itemId);
      $lock->setWebsiteid($websiteId);
      $lock->setType($type);
      $lock->setStarttime(time());
      $lock->setLastactivity($lock->getStarttime());

      $this->getEntityManager()->persist($lock);
      $this->getEntityManager()->flush();
      $this->getEntityManager()->refresh($lock);
    } catch (\Exception $e) {
      throw new CmsException(1504, __METHOD__, __LINE__, null, $e);
    }

    return $lock;
  }

  /**
   * Einen Lock entfernen
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   */
  public function unlock($itemId, $websiteId, $type)
  {
    try {
      // Lock ermitteln
      $lock = $this->getByIdAndType($itemId, $websiteId, $type);

      // Lock entfernen
      $this->getEntityManager()->remove($lock);
      $this->getEntityManager()->flush();
    } catch (\Exception $e) {
      throw new CmsException(1506, __METHOD__, __LINE__, null, $e);
    }

    return $lock;
  }

  /**
     * Remove all locks from a user.
     *
     * @param string $userId
     * @throws \Cms\Exception
     */
    public function unlockByUserId($userId)
    {
    try {
        $locks = $this->getEntityManager()
            ->getRepository('Orm\Entity\Lock')
            ->findBy(array( 'userid' => $userId ));

      foreach ($locks as $lock) {
          $this->getEntityManager()->remove($lock);
      }

        $this->getEntityManager()->flush();
    } catch (\Exception $e) {
        throw new CmsException(1506, __METHOD__, __LINE__, null, $e);
    }
    }

  /**
   * Remove all locks of the given website id.
   *
   * @param string $websiteId
   *
   * @throws \Cms\Exception
   */
    public function removeLocksByWebsiteId($websiteId)
    {
      $params = array(
      'websiteid' => $websiteId,
      );
      try {
        $locks = $this->getEntityManager()
        ->getRepository('Orm\Entity\Lock')
        ->findBy($params);

        foreach ($locks as $lock) {
          $this->getEntityManager()->remove($lock);
        }

        $this->getEntityManager()->flush();
      } catch (\Exception $e) {
        throw new CmsException(1, __METHOD__, __LINE__, $params, $e);
      }
    }

  /**
   * Gibt den Lock fuer eine bestimmte Page, Modul, Template oder Website zurueck
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @return \Orm\Entity\Lock
   * @throws \Cms\Exception
   */
    public function getByIdAndType($itemId, $websiteId, $type)
    {
      $data = array(
      'itemid'    => $itemId,
      'websiteid' => $websiteId,
      'type'      => $type
      );

      try {
        $lock = $this->getEntityManager()
                   ->getRepository('Orm\Entity\Lock')
                   ->findOneBy($data);
      } catch (\Exception $e) {
        throw new CmsException(1, __METHOD__, __LINE__, $data, $e);
      }

      return $lock;
    }

  /**
   * Gibt die Lock fuer bestimmte Page, Modul, Template oder Website zurueck
   *
   * @param string  $websiteId
   * @param string  $type
   * @return array[] \Orm\Entity\Lock
   * @throws \Cms\Exception
   */
    public function findByWebsiteIdAndType($websiteId, $type)
    {
      $data = array(
      'websiteid' => $websiteId,
      'type'      => $type
      );

      try {
        $locks = $this->getEntityManager()
                   ->getRepository('Orm\Entity\Lock')
                   ->findByWebsiteIdAndType($websiteId, $type);
      } catch (\Exception $e) {
        throw new CmsException(1, __METHOD__, __LINE__, null, $e);
      }

      return $locks;
    }

  /**
   * Loescht die Locks welche laenger als die uebergebene Zeit untaetig waren
   *
   * @param integer  $maxLifetime
   * @throws \Cms\Exception
   */
    public function deleteByMaxlifetime($maxLifetime)
    {
      try {
        $dql = 'DELETE FROM Orm\Entity\Lock l WHERE l.lastactivity < :lastactivity';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array(
        'lastactivity' => (time() - $maxLifetime),
        ));
        return $query->getResult();
      } catch (\Exception $e) {
        throw new CmsException(1, __METHOD__, __LINE__, null, $e);
      }
    }
}
