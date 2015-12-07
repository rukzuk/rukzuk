<?php
namespace Cms\Dao\UserStatus;

use Cms\Dao\UserStatus as UserStatusDaoInterface;
use Cms\Dao\Doctrine as DoctrineBase;
use Cms\Exception as CmsException;
use Orm\Entity\UserStatus as OrmUserStatus;
use Exception;

class Doctrine extends DoctrineBase implements UserStatusDaoInterface
{
  /**
   * Owner of the space
   *
   * @return \DateTime
   * @throws \Cms\Exception
   */
  public function getLastLogin()
  {
    try {
      $queryBuilder = $this->getEntityManager()->createQueryBuilder();
      $queryBuilder->add('select', 'u')
        ->add('from', 'Orm\Entity\UserStatus u')
        ->orderBy('u.lastlogin', 'DESC');

      $queryBuilder->setMaxResults(1);

      $query = $queryBuilder->getQuery();
      $result = $query->getResult();

    } catch (Exception $e) {
      throw new CmsException(1, __METHOD__, __LINE__, array($e->getMessage()), $e);
    }

    if (count($result) !== 1) {
      return null;
    }

    return $result[0]->getLastlogin();
  }

  /**
   * @param string    $userId
   * @param string    $authBackend
   * @param \DateTime $lastLogin
   *
   * @return \Cms\Data\UserStatus
   * @throws CmsException
   */
  public function setLastLogin($userId, $authBackend, \DateTime $lastLogin)
  {
    $orm = $this->getOrmByUserIdAndAuthBackend($userId, $authBackend, false);
    if (is_null($orm)) {
      $orm = new OrmUserStatus();
      $orm->setUserId($userId);
      $orm->setAuthbackend($authBackend);
    }
    try {
      $orm->setLastlogin($lastLogin);

      $entityManager = $this->getEntityManager();
      $entityManager->persist($orm);
      $entityManager->flush();
      $entityManager->refresh($orm);
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(2811, __METHOD__, __LINE__, null, $e);
    }

    return $orm->toCmsData();
  }

  /**
   * @param string $userId
   * @param string $authBackend
   * @param bool   $throwExceptionIfNotExists
   *
   * @return OrmUserStatus|null
   * @throws CmsException
   */
  protected function getOrmByUserIdAndAuthBackend(
      $userId,
      $authBackend,
      $throwExceptionIfNotExists = true
  ) {
    try {
      $ormEntity = $this->getEntityManager()
        ->getRepository('Orm\Entity\UserStatus')
        ->findOneBy(array(
          'userid' => $userId,
          'authbackend' => $authBackend,
        ));
    } catch (\Exception $e) {
      throw new CmsException(2803, __METHOD__, __LINE__, null, $e);
    }

    if ($throwExceptionIfNotExists && $ormEntity === null) {
      throw new CmsException(2802, __METHOD__, __LINE__);
    }

    return $ormEntity;
  }
}
