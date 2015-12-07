<?php
namespace Cms\Dao\Group;

use Cms\Exception as CmsException;
use Cms\Dao\Group as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Orm\Entity\Group as Group;

/**
 * Doctrine
 *
 * @package      Cms
 * @subpackage   Dao
 */
class Doctrine extends DoctrineBase implements Dao
{
  /**
   * Setzt die Page Rechte einer Gruppe
   *
   * @param  string $id
   * @param  string $websiteId
   * @param  string $pageRights
   * @return \Orm\Entity\Group
   */
  public function setPageRights($id, $websiteId, array $pageRights)
  {
    $groupToAddPageRightsTo = $this->getEntityManager()
                                   ->getRepository('Orm\Entity\Group')
                                   ->findOneBy(array(
                                     'id' => $id,
                                     'websiteid' => $websiteId
                                   ));

    if ($groupToAddPageRightsTo === null) {
      $groupIdData = array('id' => $id);
      throw new CmsException(580, __METHOD__, __LINE__, $groupIdData);
    }

    $formerRightsAsArray = json_decode($groupToAddPageRightsTo->getRights(), true);

    if (is_array($formerRightsAsArray) && count($formerRightsAsArray) > 0) {
      foreach ($formerRightsAsArray as $index => $rightsAreaSettings) {
        if ($rightsAreaSettings['area'] === 'pages') {
          unset($formerRightsAsArray[$index]);
        }
      }
      $groupToAddPageRightsTo->setRights(json_encode($formerRightsAsArray));
    }

    if (isset($pageRights['allrights']) && $pageRights['allrights'] === true) {
      $formerRightsAsArray = json_decode($groupToAddPageRightsTo->getRights(), true);
      $formerRightsAsArray[] = array(
        'area' => 'pages',
        'privilege' => 'all',
        'ids' => null,
      );
      $groupToAddPageRightsTo->setRights(json_encode($formerRightsAsArray));
    }

    if (isset($pageRights['rights']) && count($pageRights['rights']) > 0) {
      $editRightsPages = $subAllRightsPages = $subEditRightsPages = array();
      foreach ($pageRights['rights'] as $id => $rights) {
        foreach ($rights as $right) {
          if ($right === 'edit') {
            $editRightsPages[] = $id;
          }
          if ($right === 'subAll') {
            $subAllRightsPages[] = $id;
          }
          if ($right === 'subEdit') {
            $subEditRightsPages[] = $id;
          }
        }
      }
      $pageRightsToAdd = array();
      if (count($editRightsPages) > 0) {
        $pageRightsToAdd[] = array(
          'area' => 'pages',
          'privilege' => 'edit',
          'ids' => $editRightsPages
        );
      }
      if (count($subAllRightsPages) > 0) {
        $pageRightsToAdd[] = array(
          'area' => 'pages',
          'privilege' => 'subAll',
          'ids' => $subAllRightsPages
        );
      }
      if (count($subEditRightsPages) > 0) {
        $pageRightsToAdd[] = array(
          'area' => 'pages',
          'privilege' => 'subEdit',
          'ids' => $subEditRightsPages
        );
      }

      if (count($pageRights) > 0) {
        $formerRightsAsArray = json_decode($groupToAddPageRightsTo->getRights(), true);
        $newRightsCombinedWithFormerRightsAsArray = array_merge(
            $formerRightsAsArray,
            $pageRightsToAdd
        );
        $groupToAddPageRightsTo->setRights(
            json_encode($newRightsCombinedWithFormerRightsAsArray)
        );
      }
    }

    try {
      $entityManager = $this->getEntityManager();
      $entityManager->persist($groupToAddPageRightsTo);
      $entityManager->flush();
      $entityManager->refresh($groupToAddPageRightsTo);

      return $groupToAddPageRightsTo;
    } catch (Exception $e) {
      $sourceIdData = array('id' => $id);
      throw new CmsException(581, __METHOD__, __LINE__, $sourceIdData, $e);
    }
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  string $name
   * @return string Id der kopierten Gruppe
   */
  public function copy($id, $websiteId, $name)
  {
    $sourceGroup = $this->getEntityManager()
                        ->getRepository('Orm\Entity\Group')
                        ->findOneBy(array(
                          'id' => $id,
                          'websiteid' => $websiteId
                        ));

    if ($sourceGroup === null) {
      $sourceIdData = array('id' => $id);
      throw new CmsException(570, __METHOD__, __LINE__, $sourceIdData);
    }

    if ($sourceGroup->getName() === $name) {
      throw new CmsException(572, __METHOD__, __LINE__);
    }

    $groupWithSameName = $this->getEntityManager()
                        ->getRepository('Orm\Entity\Group')
                        ->findOneBy(array(
                          'name' => $name,
                          'websiteid' => $websiteId
                        ));

    if ($groupWithSameName !== null) {
      throw new CmsException(573, __METHOD__, __LINE__);
    }


    $copiedGroup = clone $sourceGroup;
    $copiedGroup->setNewGeneratedId();
    $copiedGroup->setName($name);

    try {
      $entityManager = $this->getEntityManager();
      $entityManager->persist($copiedGroup);
      $entityManager->flush();

      return $copiedGroup->getId();
    } catch (Exception $e) {
      $sourceIdData = array('id' => $id);
      throw new CmsException(171, __METHOD__, __LINE__, $sourceIdData, $e);
    }

  }

  /**
   * @param  string   $websiteId
   * @param  array    $columnValues
   * @param  boolean  $useColumnsValuesId
   * @return \Orm\Entity\Group
   */
  public function create($websiteId, array $columnValues, $useColumnsValuesId = false)
  {
    $group = new Group();

    if ($useColumnsValuesId && isset($columnValues['id'])) {
      $group->setId($columnValues['id']);
    } else {
      $group->setNewGeneratedId();
    }

    if ($columnValues['name'] !== null) {
      $group->setName($columnValues['name']);
    }

    if (isset($columnValues['rights']) && $columnValues['rights'] !== null) {
      $rights = $columnValues['rights'];
      if (is_array($columnValues['rights'])) {
        $rights = json_encode($columnValues['rights']);
      }
      $group->setRights($rights);
    } else {
      $group->setRights(Dao::DEFAULT_EMPTY_RIGHTS);
    }

    $group->setWebsiteid($websiteId);
    $group->setUsers(Dao::DEFAULT_EMPTY_USERS);

    try {
      $entityManager = $this->getEntityManager();
      $entityManager->persist($group);
      $entityManager->flush();
      $entityManager->refresh($group);
    } catch (Exception $e) {
      throw new CmsException(504, __METHOD__, __LINE__, null, $e);
    }

    return $group;
  }

  /**
   * @param string $id
   * @param string $websiteId
   * @param array  $columnsValues
   */
  public function update($id, $websiteId, array $columnsValues)
  {
    try {
      $group = $this->getEntityManager()
                    ->getRepository('Orm\Entity\Group')
                    ->findOneBy(array('id' => $id,
                                      'websiteid' => $websiteId));
      if ($group === null) {
        throw new CmsException(502, __METHOD__, __LINE__);
      }

      if (isset($columnsValues['name']) && $columnsValues['name'] !== null) {
        $group->setName($columnsValues['name']);
      }

      if (isset($columnsValues['rights']) && $columnsValues['rights'] !== null) {
        $rightsOfGroupJson = $group->getRights();

        if ($rightsOfGroupJson === Dao::DEFAULT_EMPTY_RIGHTS
            || $rightsOfGroupJson === null
        ) {
          $group->setRights(json_encode($columnsValues['rights']));
        } else {
          $actualRights = json_decode($rightsOfGroupJson);

          $mergedRights = $this->mergeRights(
              $actualRights,
              $columnsValues['rights']
          );
          $group->setRights(json_encode($mergedRights));
        }
      }

      $entityManager = $this->getEntityManager();
      $entityManager->persist($group);
      $entityManager->flush();
      $entityManager->refresh($group);

      return $group;
    } catch (\Exception $e) {
      throw new CmsException(506, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  stdClass $actualRights
   * @param  stdClass $updateRights
   * @return array
   */
  private function mergeRights(array $actualRights, array $updateRights)
  {
    foreach ($actualRights as $aIndex => $actualRight) {
      foreach ($updateRights as $uIndex => $updateRight) {
        if ($updateRight->area === $actualRight->area
            && $updateRight->privilege === $actualRight->privilege) {
          $actualRights[$aIndex] = $updateRight;
          unset($updateRights[$uIndex]);
        }
        if ($updateRight->area === $actualRight->area) {
          $actualRights[$aIndex] = $updateRight;
          unset($updateRights[$uIndex]);
        }
      }
    }

    $mergedRights = $actualRights;

    if (count($updateRights) > 0) {
      $mergedRights = array_merge($actualRights, $updateRights);
    }

    return $mergedRights;
  }

  /**
   * @param  string   $userId
   * @param  string   $websiteId
   * @return array[] Orm\Entity\Group
   */
  public function getAllByUserAndWebsiteId($userId, $websiteId)
  {
    return $this->getEntitymanager()->getRepository('Orm\Entity\Group')
                                    ->findByUserAndWebsiteId(
                                        $userId,
                                        $websiteId
                                    );
  }

  /**
   * @param  string  $websiteId
   * @return array[] Orm\Entity\Group
   */
  public function getAllByWebsiteId($websiteId)
  {
    $all = $this->getEntitymanager()->getRepository('Orm\Entity\Group')
                                    ->findByWebsiteIdAndOrderByName($websiteId);

    return $all;
  }

  /**
   * @param  string $websiteId
   * @return boolean
   */
  public function existsGroupsForWebsite($websiteId)
  {
    try {
      $group = $this->getEntityManager()
                    ->getRepository('Orm\Entity\Group')
                    ->findOneBy(array('websiteid' => $websiteId));

    } catch (Exception $e) {
      throw new CmsException(501, __METHOD__, __LINE__, null, $e);
    }

    return $group !== null;
  }

  /**
   * @param  string  $id
   * @param  string  $websiteId
   * @return boolean
   */
  public function existsGroup($id, $websiteId)
  {
    try {
      $group = $this->getEntityManager()
                    ->getRepository('Orm\Entity\Group')
                    ->findOneBy(array(
                      'id' => $id,
                      'websiteid' => $websiteId
                    ));
    } catch (Exception $e) {
      throw new CmsException(501, __METHOD__, __LINE__, null, $e);
    }

    return $group !== null;
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @return \Orm\Entity\Group
   */
  public function getByIdAndWebsiteId($id, $websiteId)
  {
    $group = $this->getEntityManager()
                  ->getRepository('Orm\Entity\Group')
                  ->findOneBy(array('id' => $id,
                                    'websiteid' => $websiteId));

    if ($group === null) {
      throw new CmsException(502, __METHOD__, __LINE__);
    }

    $this->getEntityManager()->clear();

    return $group;
  }

  /**
   * @param  string  $userId
   * @return array[] Orm\Entity\Group
   */
  public function getAllByUserId($userId)
  {
    try {
      $groups = $this->getEntityManager()
                     ->getRepository('Orm\Entity\Group')
                     ->findByUserId($userId);
      return $groups;
    } catch (\Exception $e) {
      throw new CmsException(1, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function delete($id, $websiteId)
  {
    $group = $this->getEntityManager()
                  ->getRepository('Orm\Entity\Group')
                  ->findOneBy(array('id' => $id,
                                    'websiteid' => $websiteId));

    if ($group === null) {
      throw new CmsException(502, __METHOD__, __LINE__);
    }
    try {
      $entityManager = $this->getEntityManager();
      $entityManager->remove($group);
      $entityManager->flush();
      return true;
    } catch (Exception $e) {
      throw new CmsException(510, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $userIds
   *
   * @throws \Cms\Exception
   * @return bool
   */
  public function addUsers($id, $websiteId, array $userIds)
  {
    try {
      $group = $this->getEntityManager()
                    ->getRepository('Orm\Entity\Group')
                    ->findOneBy(array('id' => $id,
                                      'websiteid' => $websiteId));
      if ($group === null) {
        throw new CmsException(515, __METHOD__, __LINE__);
      }

      $usersOfGroupJson = $group->getUsers();
      if (empty($usersOfGroupJson)) {
        $usersOfGroup = array();
      } else {
        $usersOfGroup = json_decode($usersOfGroupJson, true);
      }
      foreach ($userIds as $userId) {
        $usersOfGroup[] = $userId;
      }

      $usersOfGroup = array_unique(array_values($usersOfGroup));
      $group->setUsers(json_encode($usersOfGroup));
      $entityManager = $this->getEntityManager();
      $entityManager->persist($group);
      $entityManager->flush();

      return true;

    } catch (Exception $e) {
      throw new CmsException(514, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $userIds
   * @return boolean
   */
  public function removeUsers($id, $websiteId, array $userIds)
  {
    try {
      $group = $this->getEntityManager()
                    ->getRepository('Orm\Entity\Group')
                    ->findOneBy(array('id' => $id,
                                      'websiteid' => $websiteId));

      if ($group === null) {
        throw new CmsException(517, __METHOD__, __LINE__);
      }

      $usersOfGroupJson = $group->getUsers();
      if (empty($usersOfGroupJson)) {
        $usersOfGroupJson = array();
      }

      $usersOfGroup = json_decode($usersOfGroupJson, true);
      foreach ($usersOfGroup as $index => $userId) {
        if (in_array($userId, $userIds)) {
          unset($usersOfGroup[$index]);
        }
      }
      $usersOfGroup = array_unique(array_values($usersOfGroup));

      $group->setUsers(json_encode($usersOfGroup));
      $entityManager = $this->getEntityManager();
      $entityManager->persist($group);
      $entityManager->flush();

      return true;

    } catch (Exception $e) {
      throw new CmsException(519, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * Loescht alle Gruppen.
   *
   * @return boolean
   */
  public function deleteAll()
  {
    $dql = "DELETE Orm\Entity\Group";
    $query = $this->getEntityManager()->createQuery($dql);
    return $query->getResult();
  }
}
