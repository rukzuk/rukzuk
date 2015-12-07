<?php
namespace Cms\Dao\User;

use Cms\Dao\Orm;
use Cms\Dao\User as UserDaoInterface;

class All implements UserDaoInterface
{

  private $daoConfig;
  private $daoDoctrine;

  public function __construct()
  {
    $this->daoConfig = new Config();
    $this->daoDoctrine = new Doctrine();
  }

  /**
   * @param  array   $columnValues
   * @param  boolean $useColumnsValuesId
   *
   * @return \Cms\Data\User
   */
  public function create(array $columnValues, $useColumnsValuesId = false)
  {
    return $this->daoDoctrine->create($columnValues, $useColumnsValuesId);
  }

  /**
   * @param string $id
   * @param array  $columnsValues
   *
   * @return \Cms\Data\User
   */
  public function update($id, array $columnsValues)
  {
    return $this->daoDoctrine->update($id, $columnsValues);
  }

  /**
   * @param  string $id
   *
   * @return boolean
   */
  public function delete($id)
  {
    return $this->daoDoctrine->delete($id);
  }

  /**
   * @param  string $websiteId
   *
   * @return \Cms\Data\User[]
   */
  public function getAll($websiteId = null)
  {
    $configUsers = $this->daoConfig->getAll($websiteId);
    $doctrineUsers = $this->daoDoctrine->getAll($websiteId);
    return array_merge($doctrineUsers, $configUsers);
  }

  /**
   * @param string $id
   *
   * @return \Cms\Data\User
   */
  public function getById($id)
  {
    try {
      return $this->daoConfig->getById($id);
    } catch (\Exception $e) {
    }
    return $this->daoDoctrine->getById($id);
  }

  /**
   * @param array $ids
   *
   * @return \Cms\Data\User[]
   */
  public function getByIds(array $ids)
  {
    $users = array();
    foreach ($ids as $id) {
      $users[] = $this->getById($id);
    }
    return $users;
  }

  /**
   * @param string $email
   * @param string $id
   *
   * @return \Cms\Data\User
   */
  public function getByEmailAndIgnoredId($email, $id = null)
  {
    try {
      return $this->daoConfig->getByEmailAndIgnoredId($email, $id);
    } catch (\Exception $ignore) {
    }

    return $this->daoDoctrine->getByEmailAndIgnoredId($email, $id);
  }

  /**
   * @return \Cms\Data\User[]
   */
  public function getAllSuperusers()
  {
    $configUsers = $this->daoConfig->getAllSuperusers();
    $doctrineUsers = $this->daoDoctrine->getAllSuperusers();
    return array_merge($doctrineUsers, $configUsers);
  }

  /**
   * @return boolean
   */
  public function deleteAll()
  {
    return $this->daoDoctrine->deleteAll();
  }

  /**
   * Owner of the space
   *
   * @return \Cms\Data\User
   */
  public function getOwner()
  {
    return $this->daoConfig->getOwner();
  }
}
