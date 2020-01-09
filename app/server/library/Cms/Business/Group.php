<?php
namespace Cms\Business;

use Seitenbau\Registry;
use Cms\Exception as CmsException;

/**
 * Stellt die Business-Logik fuer Group zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Group extends Base\Service
{
  /**
   * @var array
   */
  protected $defaultNegativeWebsitePrivileges = array(
    'publish' => false,
    'modules' => false,
    'templates' => false,
    'colorscheme' => false,
    'readlog' => false,
    'allpagerights' => false
  );

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $pageRights
   * @return \Orm\Entity\Group
   */
  public function setPageRights($id, $websiteId, array $pageRights)
  {
    return $this->getService()->setPageRights($id, $websiteId, $pageRights);
  }
  /**
   * @param  string $websiteId
   * @param  array  $createValues
   * @return \Orm\Entity\Group
   */
  public function create($websiteId, array $createValues)
  {
    return $this->getService()->create($websiteId, $createValues);
  }
  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  string $name
   * @return \Orm\Entity\Group
   */
  public function copy($id, $websiteId, $name)
  {
    return $this->getService()->copy($id, $websiteId, $name);
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array  $editValues
   * @return \Orm\Entity\Group
   */
  public function edit($id, $websiteId, array $editValues)
  {
    $group = $this->getService()->edit($id, $websiteId, $editValues);

    $usersOfGroup = json_decode($group->getUsers());

    if (is_array($usersOfGroup) && count($usersOfGroup) > 0) {
      $userService = $this->getService('User');
      $userOrms = array();

      foreach ($usersOfGroup as $userId) {
        if ($userId instanceof \Orm\Entity\User) {
          $userId = $userId->getId();
        }

        $user = $userService->getById($userId);
        $groupsOfUser = $this->getService()->getAllByUserId($user->getId());
        $user->setGroups($groupsOfUser);
        $userOrms[] = $user;
      }

      $group->setUsers($userOrms);

    } else {
      $group->setUsers(array());
    }

    return $group;
  }

  /**
   * @param  string $websiteId
   * @return array[] \Orm\Entity\Group
   */
  public function getAllByWebsiteId($websiteId)
  {
    // Website vorhanden?
    $website = $this->getService('Website')->getById($websiteId);

    $groups = $this->getService()->getAllByWebsiteId($website->getId());

    if (is_array($groups) && count($groups) > 0) {
      $entityManager = \Seitenbau\Registry::getEntityManager();

      foreach ($groups as $group) {
        if (is_array($group->getUsers())) {
          $usersOfGroup = $group->getUsers();
        } else {
          $usersOfGroup = json_decode($group->getUsers());
        }

        if (is_array($usersOfGroup) && count($usersOfGroup) > 0) {
          $userService = $this->getService('User');
          $userOrms = array();

          foreach ($usersOfGroup as $userId) {
            if ($userId instanceof \Orm\Entity\User) {
              $userId = $userId->getId();
            }

            $user = $userService->getById($userId);
            $groupsOfUser = $this->getService()->getAllByUserId($user->getId());
            $user->setGroups($groupsOfUser);
            $userOrms[] = $user;

            $entityManager->detach($user);
          }

          $group->setUsers($userOrms);
        } else {
          $group->setUsers(array());
        }

        $entityManager->detach($group);
      }
    }

    return $groups;
  }
  /**
   * @return arrray
   */
  public function getDefaultNegativeWebsitePrivileges()
  {
    return $this->defaultNegativeWebsitePrivileges;
  }

  /**
   * @param  string $userId
   * @param  string $websiteId
   * @return array  The website privileges of the authenticated user.
   */
  public function getWebsitePrivilegesOfAuthenticatedUser($userId, $websiteId)
  {
    $groupsOfAuthenticatedUser = $this->getService()
                                      ->getAllByUserAndWebsiteId(
                                          $userId,
                                          $websiteId
                                      );

    if (count($groupsOfAuthenticatedUser) > 0) {
      $websitePrivileges = $this->getDefaultNegativeWebsitePrivileges();
      $alreadySetPrivileges = array();

      foreach ($groupsOfAuthenticatedUser as $group) {
        if ($group->getRights() === \Cms\Dao\Group::DEFAULT_EMPTY_RIGHTS) {
          continue;
        }
        $rightsAsArray = json_decode($group->getRights(), true);

        foreach ($rightsAsArray as $groupRight) {
          if (in_array(
              array('website', 'modules', 'templates', 'colorscheme', 'readlog', 'allpagerights'),
              $alreadySetPrivileges
          )
          ) {
            break;
          }
          if (!in_array('website', $alreadySetPrivileges)
              && $groupRight['area'] === 'website'
              && $groupRight['privilege'] === 'publish') {
            $websitePrivileges['publish'] = true;
            $alreadySetPrivileges[] = 'website';
          }
          if (!in_array('readlog', $alreadySetPrivileges)
              && $groupRight['area'] === 'readlog'
              && $groupRight['privilege'] === 'all') {
            $websitePrivileges['readlog'] = true;
            $alreadySetPrivileges[] = 'readlog';
          }
          if (!in_array('modules', $alreadySetPrivileges)
              && $groupRight['area'] === 'modules'
              && $groupRight['privilege'] === 'all') {
            $websitePrivileges['modules'] = true;
            $alreadySetPrivileges[] = 'modules';
          }
          if (!in_array('templates', $alreadySetPrivileges)
              && $groupRight['area'] === 'templates'
              && $groupRight['privilege'] === 'all') {
            $websitePrivileges['templates'] = true;
            $alreadySetPrivileges[] = 'templates';
          }
          if (!in_array('colorscheme', $alreadySetPrivileges)
              && $groupRight['area'] === 'colorscheme'
              && $groupRight['privilege'] === 'all') {
            $websitePrivileges['colorscheme'] = true;
            $alreadySetPrivileges[] = 'colorscheme';
          }
          if (!in_array('allpagerights', $alreadySetPrivileges)
              && $groupRight['area'] === 'pages'
              && $groupRight['privilege'] === 'all') {
            $websitePrivileges['allpagerights'] = true;
            $alreadySetPrivileges[] = 'allpagerights';
          }
        }
      }

      return $websitePrivileges;
    }

    return $this->getDefaultNegativeWebsitePrivileges();
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @return \Orm\Entity\Group
   */
  public function getByIdAndWebsiteId($id, $websiteId)
  {
    $group = $this->getService()->getByIdAndWebsiteId($id, $websiteId);

    if (is_array($group->getUsers())) {
      $usersOfGroup = $group->getUsers();
    } else {
      $usersOfGroup = json_decode($group->getUsers());
    }

    if (is_array($usersOfGroup) && count($usersOfGroup) > 0) {
      $userService = $this->getService('User');
      $userOrms = array();

      foreach ($usersOfGroup as $userId) {
        if ($userId instanceof \Orm\Entity\User) {
          $userId = $userId->getId();
        }

        $user = $userService->getById($userId);
        $groupsOfUser = $this->getService()->getAllByUserId($user->getId());
        $user->setGroups($groupsOfUser);
        $userOrms[] = $user;
      }

      $group->setUsers($userOrms);
    } else {
      $group->setUsers(array());
    }

    return $group;
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function delete($id, $websiteId)
  {
    return $this->getService()->delete($id, $websiteId);
  }

  /**
   * @param  string  $userId
   * @return array[] \Orm\Entity\Group
   */
  public function getAllByUserId($userId)
  {
    return $this->getService()->getAllByUserId($userId);
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array $userIds
   * @return boolean
   */
  public function addUsers($id, $websiteId, array $userIds)
  {
    return $this->getService()->addUsers($id, $websiteId, $userIds);
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @param  array $userIds
   * @return boolean
   */
  public function removeUsers($id, $websiteId, array $userIds)
  {
    return $this->getService()->removeUsers($id, $websiteId, $userIds);
  }

  /**
   * Gibt die Navigation der Website mit den entsprechenden Rechten der Gruppe
   * zurueck
   *
   * @param string $id
   * @param string $websiteId
   */
  public function getPageRightsAndNavigation($id, $websiteId)
  {
    $websiteNavigation = $this->getBusiness('Website')
                              ->getNavigationWithDataFromWebsite($websiteId);

    $groupRights = $this->getByIdAndWebsiteId($id, $websiteId)->getRights();
    $groupRights = \Seitenbau\Json::decode($groupRights);

    $this->addRightsToNavigation($websiteNavigation, $groupRights);

    return $websiteNavigation;
  }
  /**
   * Gibt den all(Rights) Wert einer Gruppe zurueck
   *
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function getAllRightsValue($id, $websiteId)
  {
    $groupRights = $this->getByIdAndWebsiteId($id, $websiteId)->getRights();
    $groupRights = \Seitenbau\Json::decode($groupRights);

    if (count($groupRights) > 0) {
      foreach ($groupRights as $groupRight) {
        if (isset($groupRight['area']) && $groupRight['area'] === 'pages') {
          if (isset($groupRight['privilege']) && $groupRight['privilege'] === 'all') {
            return true;
          }
        }
      }

      return false;
    }

    return false;
  }
  /**
   * Setzt die Gruppen-Rechte fuer die Navigationspunkte
   *
   * Funktion wird rekursiv aufgerufen und setzt fuer die einzelnen
   * Navigations und Sub-Navigationspunkte die Rechte
   *
   * @param array $navigation
   * @param array $groupRights
   * @param array $parentRights
   */
  private function addRightsToNavigation(
      &$navigation,
      $groupRights,
      $parentRights = array()
  ) {
    foreach ($navigation as &$navigationEntry) {
      $this->setRights($navigationEntry, $groupRights, $parentRights);

      // Bei Subpages ebenfalls Rechte setzen (rekursiv)
      if (isset($navigationEntry['children'])
          && count($navigationEntry['children']) > 0) {
        $this->addRightsToNavigation(
            $navigationEntry['children'],
            $groupRights,
            $navigationEntry['rights']
        );
      }
    }
  }

  /**
   * Setzt die entsprechenden Rechte fuer den uebergebenen Navigationspunkt
   *
   * @param array $navEntry
   * @param array $groupRights
   * @param array $parentRights
   */
  private function setRights(&$navEntry, $groupRights, $parentRights)
  {
    $this->setDefaultRights($navEntry, $parentRights);

    foreach ($groupRights as $right) {
      if ($right['area'] !== 'pages') {
        continue;
      }
      if (isset($right['ids']) && count($right['ids']) > 0) {
        foreach ($right['ids'] as $rightPage) {
          if ($navEntry['id'] === $rightPage) {
            $navEntry['rights'][$right['privilege']]['value'] = true;
            // Included/Sub Right ist nun auch inherited
            if ($right['privilege'] === 'subAll') {
              $navEntry['rights']['subEdit']['value'] = true;
              $navEntry['rights']['subEdit']['inherited'] = true;
            }
          }
        }
      }
    }
  }

  /**
   * Setzt die Default-Struktur der Rechte fuer eine Page mit entsprechenden
   * Default-Werten
   *
   * Die Rechte werden von dem uebergeordneten Navigationspunkt vererbt
   *
   * @param array $navEntry
   * @param array $parentRights
   */
  private function setDefaultRights(&$navEntry, $parentRights)
  {
    // Moegliche Privileges aus der Config lesen
    $config = Registry::getConfig();
    $privilegeOptions = $config->group->rights->pages->toArray();

    foreach ($privilegeOptions as $privilegeOption) {
      if ($privilegeOption  !== 'none') {
        $navEntry['rights'][$privilegeOption] = array(
          'value' => false,
          'inherited' => false
        );
      }
    }

    if (isset($parentRights['subAll'])
        && ($parentRights['subAll']['value'] === true
            || $parentRights['subAll']['inherited'] === true)) {
      $navEntry['rights']['edit']['inherited'] = true;
      $navEntry['rights']['subEdit']['inherited'] = true;
      $navEntry['rights']['subAll']['inherited'] = true;

      // Nach Anfordung des Clients soll ein inherited auch den value eines
      // Pagerechts auf true setzen
      $navEntry['rights']['edit']['value'] = true;
      $navEntry['rights']['subEdit']['value'] = true;
      $navEntry['rights']['subAll']['value'] = true;
    }

    if (isset($parentRights['subEdit'])
        && ($parentRights['subEdit']['value'] === true
            || $parentRights['subEdit']['inherited'] === true)) {
      $navEntry['rights']['edit']['inherited'] = true;
      $navEntry['rights']['subEdit']['inherited'] = true;

      // Nach Anfordung des Clients soll ein inherited auch den value eines
      // Pagerechts auf true setzen
      $navEntry['rights']['edit']['value'] = true;
      $navEntry['rights']['subEdit']['value'] = true;
    }
  }

  /**
   * Pruefung, ob der angemeldete User die Rechte fuer die aufgerufene Aktion
   * besitzt
   *
   * @param array  $identity
   * @param string $rightname Name des Rechts, auf das geprueft wird
   * @param mixed  $check
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // Alle Aktionen duerfen nur vom Superuser durchgefuehrt werden
    if ($this->isSuperuser($identity)) {
      return true;
    }
    
    return false;
  }
  
  /**
   * @return boolean
   */
  public function deleteAll()
  {
    return $this->getService()->deleteAll();
  }
}
