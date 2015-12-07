<?php
namespace Cms\Service;

use Cms\Service\Base\Dao as DaoServiceBase;
use Cms\Exception as CmsException;
use Cms\ExceptionStack as CmsExceptionStack;

/**
 * User
 *
 * @package      Cms
 * @subpackage   Service
 *
 * @method       \Cms\Dao\User getDao
 */
class User extends DaoServiceBase
{
  /**
   * @param string $userId
   * @param string $passwordHash
   *
   * @return boolean
   */
  public function changePassword($userId, $passwordHash)
  {
    return $this->edit($userId, array(
      'password' => $passwordHash));
  }

  /**
   * @param  array $createValues
   *
   * @throws CmsException
   * @return \Cms\Data\User
   */
  public function create(array $createValues)
  {
    if ($this->userLoginExists($createValues['email'])) {
      throw new CmsException(1001, __METHOD__, __LINE__);
    }
    return $this->getDao()->create($createValues);
  }

  /**
   * @param  string $id
   * @param  array  $editValues
   *
   * @return \Cms\Data\User
   */
  public function edit($id, array $editValues)
  {
    return $this->getDao()->update($id, $editValues);
  }

  /**
   * @param  string $id
   *
   * @return boolean
   */
  public function delete($id)
  {
    $this->getDao()->delete($id);

    /** @var $groupService \Cms\Service\Group */
    $groupService = $this->getService('Group');
    $groups = $groupService->getAllByUserId($id);
    foreach ($groups as $group) {
      try {
        $groupService->removeUsers($group->getId(), $group->getWebsiteid(), array($id));
      } catch (\Exception $e) {
        CmsExceptionStack::addException($e);
      }
    }
    if (CmsExceptionStack::hasErrors()) {
      CmsExceptionStack::throwErrors();
    }
  }

  /**
   * @param  string $websiteId
   *
   * @return array[] \Cms\Data\User
   */
  public function getAll($websiteId = null)
  {
    return $this->getDao()->getAll($websiteId);
  }

  /**
   * @param  string $id
   *
   * @return \Cms\Data\User
   */
  public function getById($id)
  {
    return $this->getDao()->getById($id);
  }

  /**
   * @param array $ids
   *
   * @return \Cms\Data\User[]
   */
  public function getByIds(array $ids)
  {
    return $this->getDao()->getByIds($ids);
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $groupIds
   *
   * @return boolean
   */
  public function addGroups($id, $websiteId, array $groupIds)
  {
    // check if user exists
    $this->getById($id);

    /** @var $groupService \Cms\Service\Group */
    $groupService = $this->getService('Group');
    foreach ($groupIds as $groupId) {
      try {
        $groupService->addUsers($groupId, $websiteId, array($id));
      } catch (\Exception $e) {
        CmsExceptionStack::addException($e);
      }
    }
    if (CmsExceptionStack::hasErrors()) {
      CmsExceptionStack::throwErrors();
    }

    return true;
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $groupIds
   *
   * @return boolean
   */
  public function removeGroups($id, $websiteId, array $groupIds)
  {
    /** @var $groupService \Cms\Service\Group */
    $groupService = $this->getService('Group');
    foreach ($groupIds as $groupId) {
      try {
        $groupService->removeUsers($groupId, $websiteId, array($id));
      } catch (\Exception $e) {
        CmsExceptionStack::addException($e);
      }
    }

    if (CmsExceptionStack::hasErrors()) {
      CmsExceptionStack::throwErrors();
    }

    return true;
  }

  /**
   * @return \Cms\Data\User[]
   */
  public function getAllSuperusers()
  {
    return $this->getDao()->getAllSuperusers();
  }

  /**
   * @return \Cms\Data\User
   */
  public function getOwner()
  {
    return $this->getDao()->getOwner();
  }


  /**
   * @return boolean
   */
  public function deleteAll()
  {
    return $this->getDao()->deleteAll();
  }

  /**
   * Get User by E-Mail
   *
   * @param string $email
   *
   * @return \Cms\Data\User
   */
  public function getByEmail($email)
  {
    return $this->getDao()->getByEmailAndIgnoredId($email);
  }

  /**
   * @param string $email
   *
   * @return bool
   */
  protected function userLoginExists($email)
  {
    try {
      $this->getDao()->getByEmailAndIgnoredId($email);
      return true;
    } catch (\Exception $e) {
      return false;
    }
  }
}
