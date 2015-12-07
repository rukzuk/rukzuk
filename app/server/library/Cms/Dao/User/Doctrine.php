<?php
namespace Cms\Dao\User;

use Cms\Exception as CmsException;
use Cms\Dao\User as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Orm\Entity\User as User;

/**
 * Doctrine
 *
 * @package      Cms
 * @subpackage   Dao
 */
class Doctrine extends DoctrineBase implements Dao
{
  /**
   * @param  array   $columnValues
   * @param  boolean $useColumnsValuesId
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\User
   */
  public function create(array $columnValues, $useColumnsValuesId = false)
  {
    $user= new User();
    
    if ($this->isEmailAlreadyTaken($columnValues['email'])) {
      throw new CmsException(1001, __METHOD__, __LINE__);
    }
    
    if ($useColumnsValuesId && isset($columnValues['id'])) {
      $user->setId($columnValues['id']);
    } else {
      $user->setNewGeneratedId();
    }
    
    $user->setFirstname($columnValues['firstname']);
    $user->setLastname($columnValues['lastname']);
    $user->setEmail($columnValues['email']);
    if (isset($columnValues['gender'])) {
      $user->setGender($columnValues['gender']);
    }
    if (isset($columnValues['language'])) {
      $user->setLanguage($columnValues['language']);
    }
    $user->setPassword('');
    $user->setSuperuser($columnValues['isSuperuser']);
    $user->setDeletable($columnValues['isDeletable']);
    
    try {
      $entityManager = $this->getEntityManager();
      $entityManager->persist($user);
      $entityManager->flush();
      $entityManager->refresh($user);
    } catch (Exception $e) {
      throw new CmsException(1004, __METHOD__, __LINE__, null, $e);
    }
    
    return $this->convertToCmsDataObject($user);
    
  }
  /**
   * @param  string $email
   * @param  string $id
   * @return boolean
   */
  private function isEmailAlreadyTaken($email, $id = null)
  {
    try {
      $this->getByEmailAndIgnoredId($email, $id);
      return true;
    } catch (\Exception $ignore) {
    }
    return false;
  }

  /**
   * @param string $id
   * @param array  $columnsValues
   *
   * @throws \Cms\Exception
   *
   * @return \Cms\Data\User
   */
  public function update($id, array $columnsValues)
  {
    $user = $this->getOrmById($id);
    try {
      if (isset($columnsValues['firstname']) &&  $columnsValues['firstname'] !== null) {
        $user->setFirstname($columnsValues['firstname']);
      }
      if (isset($columnsValues['lastname']) && $columnsValues['lastname'] !== null) {
        $user->setLastname($columnsValues['lastname']);
      }
      if (isset($columnsValues['email']) && $columnsValues['email'] !== null) {
        if ($this->isEmailAlreadyTaken($columnsValues['email'], $id)) {
          throw new CmsException(1001, __METHOD__, __LINE__);
        }
         $user->setEmail($columnsValues['email']);
      }
      if (isset($columnsValues['gender']) && $columnsValues['gender'] !== null) {
        $user->setGender($columnsValues['gender']);
      }
      if (isset($columnsValues['language'])  && $columnsValues['language'] !== null) {
        $user->setLanguage($columnsValues['language']);
      }
      if (isset($columnsValues['password']) && $columnsValues['password'] !== null) {
        $user->setPassword($columnsValues['password']);
      }
      if (isset($columnsValues['isSuperuser']) && $columnsValues['isSuperuser'] !== null) {
        $user->setSuperuser($columnsValues['isSuperuser']);
      }
      if (isset($columnsValues['isDeletable']) && $columnsValues['isDeletable'] !== null) {
        $user->setDeletable($columnsValues['isDeletable']);
      }
      
      $entityManager = $this->getEntityManager();
      $entityManager->persist($user);
      $entityManager->flush();
      $entityManager->refresh($user);
      return $this->convertToCmsDataObject($user);

    } catch (Exception $e) {
      throw new CmsException(1006, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string $id
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function delete($id)
  {
    $user = $this->getOrmById($id);
    if (!$user->isDeletable()) {
      throw new CmsException(1011, __METHOD__, __LINE__);
    }

    try {
      $entityManager = $this->getEntityManager();
      $entityManager->remove($user);
      $entityManager->flush();
    } catch (\Exception $e) {
      throw new CmsException(1010, __METHOD__, __LINE__, null, $e);
    }

    return true;
  }

  /**
   * @param string $websiteId
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\User[]
   */
  public function getAll($websiteId = null)
  {
    try {
      if ($websiteId === null) {
        $users = $this->getEntityManager()
                      ->getRepository('Orm\Entity\User')
                      ->findAll();
      } else {
        $users = $this->getEntityManager()
                      ->getRepository('Orm\Entity\User')
                      ->findAllByWebsiteId($websiteId);
      }
      
      return $this->convertToCmsDataObject($users);
      
    } catch (Exception $e) {
      throw new CmsException(1012, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string $id
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\User
   */
  public function getById($id)
  {
    return $this->convertToCmsDataObject($this->getOrmById($id));
  }

  /**
   * @param  string $id
   *
   * @throws \Cms\Exception
   * @return \Orm\Entity\User
   */
  protected function getOrmById($id)
  {
    try {
      $user = $this->getEntityManager()->getRepository('Orm\Entity\User')->findOneById($id);
    } catch (\Exception $e) {
      throw new CmsException(1013, __METHOD__, __LINE__, null, $e);
    }
    if ($user === null) {
      throw new CmsException(1002, __METHOD__, __LINE__);
    }
    return $user;
  }

  /**
   * @param  array $ids
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\User[]
   */
  public function getByIds(array $ids)
  {
    try {
      $users = $this->getEntityManager()
                  ->getRepository('Orm\Entity\User')
                  ->findByIds($ids);
      return $this->convertToCmsDataObject($users);
    } catch (Exception $e) {
      throw new CmsException(1020, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string $email
   * @param  string $id
   *
   * @throws \Cms\Exception - if no user could be found
   * @return \Cms\Data\User
   */
  public function getByEmailAndIgnoredId($email, $id = null)
  {
    try {
      $userRepository = $this->getEntityManager()
                             ->getRepository('Orm\Entity\User');
      if ($id === null) {
        $user = $userRepository->findOneByEmail($email);
      } else {
        $user = $userRepository->findOneByEmailAndIgnoreId($email, $id);
      }

      // no user found
      if (is_null($user)) {
        throw new CmsException(1002, __METHOD__, __LINE__);
      }

      return $this->convertToCmsDataObject($user);
    } catch (Exception $e) {
      throw new CmsException(1013, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @throws \Cms\Exception
   * @return \Cms\Data\User[]
   */
  public function getAllSuperusers()
  {
    try {
      $users = $this->getEntityManager()
                    ->getRepository('Orm\Entity\User')
                    ->findAllSuperusers();
      
      return $this->convertToCmsDataObject($users);

    } catch (Exception $e) {
      throw new CmsException(1022, __METHOD__, __LINE__, null, $e);
    }
  }
  
  /**
   * Loescht alle Benutzer.
   *
   * @return boolean
   */
  public function deleteAll()
  {
    $dql = "DELETE Orm\Entity\User";
    $query = $this->getEntityManager()->createQuery($dql);
    return $query->getResult();
  }

  /**
   * Owner of the space
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\User
   */
  public function getOwner()
  {
    throw new CmsException(1002, __METHOD__, __LINE__);
  }
}
