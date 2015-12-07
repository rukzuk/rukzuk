<?php
namespace Cms\Dao\Optin;

use Cms\Exception as CmsException;
use Cms\Dao\Optin as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Seitenbau\OptinCodeGenerator as OptinCode;
use Orm\Entity\OptIn as OptIn;

/**
 * Doctrine
 *
 * @package      Cms
 * @subpackage   Dao
 */
class Doctrine extends DoctrineBase implements Dao
{
  /**
   * @param  array     $users
   * @param  string    $mode
   * @return \Orm\Entity\OptIn
   */
  public function create(array $users, $mode)
  {
    $entityManager = $this->getEntityManager();
    $entityManager->beginTransaction();
    
    $optins = array();
    
    foreach ($users as $user) {
      $optin = new OptIn;
      $optin->setUser($user);
      $optin->setUserid($user->getId());
      $optin->setTimestamp(new \DateTime);
      $optin->setMode($mode);
      $optin->setCode(OptinCode::generate());
      
      try {
        $entityManager->persist($optin);
        $entityManager->flush();
      } catch (Exception $e) {
        $entityManager->rollback();
        throw new CmsException(1004, __METHOD__, __LINE__, null, $e);
      }
      $optins[] = $optin;
    }
    $entityManager->commit();
    
    return $optins;
  }
  /**
   * @param  string  $code
   * @return boolean
   */
  public function deleteByCode($code)
  {
    $optin = $this->getByCode($code);
    
    try {
      $this->getEntityManager()->remove($optin);
      $this->getEntityManager()->flush();
    } catch (Exception $e) {
      throw new CmsException(1036, __METHOD__, __LINE__, null, $e);
    }
    
    return true;
  }
  /**
   * Loescht OptIns via der userid. Es wird keine Exception geworfen wenn fuer
   * die userid keine Optins vorhanden sind.
   *
   * @param  string  $id
   * @return boolean
   */
  public function deleteByUserId($id)
  {
    $entityManager = $this->getEntityManager();
    $optin = $entityManager->getRepository('Orm\Entity\OptIn')
                           ->findOneBy(array('userid' => $id));

    if ($optin === null) {
      return true;
    }
    try {
      $entityManager->remove($optin);
      $entityManager->flush();
      return true;
    } catch (Exception $e) {
      throw new CmsException(1032, __METHOD__, __LINE__, null, $e);
    }
  }
  /**
   * @param  array   $ids
   * @param  string  $mode
   * @return boolean
   */
  public function deleteByUserIdsAndMode(array $ids, $mode)
  {
    if (!in_array($mode, array(\Orm\Entity\OptIn::MODE_REGISTER, \Orm\Entity\OptIn::MODE_PASSWORD))) {
      throw new \InvalidArgumentException('Invalid mode');
    }
    try {
      return $this->getEntityManager()
                  ->getRepository('Orm\Entity\OptIn')
                  ->deleteByUserIdsAndMode($ids, $mode);
    } catch (Exception $e) {
      throw new CmsException(1032, __METHOD__, __LINE__, null, $e);
    }
  }
  /**
   * @param string $code
   * @return \Orm\Entity\Optin
   */
  public function getByCode($code)
  {
    try {
      $optin = $this->getEntityManager()
                    ->getRepository('Orm\Entity\OptIn')
                    ->findOneBy(array('code' => $code));
      
      if ($optin === null) {
        throw new CmsException(1035, __METHOD__, __LINE__);
      }
      return $optin;
    } catch (Exception $e) {
      throw new CmsException(1033, __METHOD__, __LINE__, null, $e);
    }
  }
  /**
   * @param string $id
   * @return \Orm\Entity\Optin
   */
  public function getByUserId($id)
  {
    try {
      $optin = $this->getEntityManager()
                    ->getRepository('Orm\Entity\OptIn')
                    ->findOneBy(array('userid' => $id));
      
      if ($optin === null) {
        throw new CmsException(1035, __METHOD__, __LINE__);
      }
      return $optin;
    } catch (Exception $e) {
      throw new CmsException(1033, __METHOD__, __LINE__, null, $e);
    }
  }
  /**
   * Loescht alle OptIns.
   *
   * @return boolean
   */
  public function deleteAll()
  {
    $dql = "DELETE Orm\Entity\OptIn";
    $query = $this->getEntityManager()->createQuery($dql);
    return $query->getResult();
  }

  /**
   * @param string $code
   * @return boolean
   */
  public function existsCode($code)
  {
    try {
      $optin = $this->getEntityManager()
                    ->getRepository('Orm\Entity\OptIn')
                    ->findOneBy(array('code' => $code));
    } catch (Exception $e) {
      throw new CmsException(1033, __METHOD__, __LINE__, null, $e);
    }
    return $optin !== null;
  }
}
