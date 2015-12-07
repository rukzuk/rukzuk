<?php
namespace Cms\Dao\ActionLog;

use Cms\Exception as CmsException;
use Cms\Dao\ActionLog as Dao;
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
   * Get log by website id
   * @param string $websiteId
   * @param int $limit
   * @return array
   * @throws \Cms\Exception
   */
  public function getByWebsiteId($websiteId, $limit)
  {
    try {
      $queryBuilder = $this->getEntityManager()->createQueryBuilder();
      $queryBuilder->add('select', 'l')
                   ->add('from', 'Orm\Entity\ActionLog l')
                   ->add('where', 'l.websiteid = :websiteid')
                   ->orderBy('l.timestamp', 'DESC')
                   ->orderBy('l.logid', 'DESC')
                   ->setParameter('websiteid', $websiteId);

      if ($limit !== null) {
        $queryBuilder->setMaxResults($limit);
      }

      $query = $queryBuilder->getQuery();
      $result = $query->getResult();
      
    } catch (Exception $e) {
      throw new CmsException(1201, __METHOD__, __LINE__, null, $e);
    }
    
    return $result;
  }

  /**
   * Fetch the latest entry of a given action
   * @param $action
   * @return ORM (single result)
   * @throws \Cms\Exception
   */
  private function getLatestLogEntryByAction($action)
  {
    try {
      $queryBuilder = $this->getEntityManager()->createQueryBuilder();
      $queryBuilder->add('select', 'l')
        ->add('from', 'Orm\Entity\ActionLog l')
        ->add('where', ' l.action = :action')
        ->orderBy('l.timestamp', 'DESC')
        ->orderBy('l.logid', 'DESC')
        ->setParameter('action', $action);

      $queryBuilder->setMaxResults(1);


      $query = $queryBuilder->getQuery();
      $result = $query->getResult();

    } catch (Exception $e) {
      throw new CmsException(1201, __METHOD__, __LINE__, null, $e);
    }

    if (count($result) !== 1) {
      throw new CmsException(1203, __METHOD__, __LINE__, array('action' =>  $action));
    }

    return $result[0];
  }


  /**
   * Get log with some filter magic
   * @param string $startAfterAction - fetch only log entries newer then the last entry of this given action
   * @param int $limit - max results
   * @return mixed
   * @throws \Cms\Exception
   */
  public function getLogSinceLastAction($startAfterAction, $limit = null)
  {
    try {
      $queryBuilder = $this->getEntityManager()->createQueryBuilder();
      $queryBuilder->add('select', 'l')
                   ->add('from', 'Orm\Entity\ActionLog l');

      if (!is_null($startAfterAction)) {
        $startLogId = $this->getLatestLogEntryByAction($startAfterAction)->getLogid();
        $queryBuilder->add('where', 'l.logid > :startLogId')
                     ->setParameter('startLogId', $startLogId);
      }

      $queryBuilder->orderBy('l.timestamp', 'DESC')
                   ->orderBy('l.logid', 'DESC');

      if ($limit !== null) {
        $queryBuilder->setMaxResults($limit);
      }

      $query = $queryBuilder->getQuery();
      $result = $query->getResult();

    } catch (Exception $e) {
      throw new CmsException(1201, __METHOD__, __LINE__, null, $e);
    }

    return $result;
  }

  /**
   * @param  string  $websiteId
   * @param  integer $lifetimeBoundary
   * @return boolean
   * @throws \Cms\Exception
   */
  public function deleteLogEntriesBelowLifetimeBoundary($websiteId, $lifetimeBoundary)
  {
    try {
      $queryBuilder = $this->getEntityManager()->createQueryBuilder();
      $queryBuilder->add('select', 'l')
                   ->add('from', 'Orm\Entity\ActionLog l')
                   ->add('where', 'l.timestamp <= :lifetimeboundary')
                   ->andWhere('l.websiteid = :websiteid')
                   ->setParameter('lifetimeboundary', $lifetimeBoundary)
                   ->setParameter('websiteid', $websiteId);

      $query = $queryBuilder->getQuery();
      $result = $query->getResult();
      
      if (is_array($result) && count($result) > 0) {
        $entityManager = $this->getEntityManager();
        foreach ($result as $logEntry) {
          $entityManager->remove($logEntry);
        }
        $entityManager->flush();
      }
    } catch (Exception $e) {
      throw new CmsException(1202, __METHOD__, __LINE__, null, $e);
    }
    
    return true;
  }
}
