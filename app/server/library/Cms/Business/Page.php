<?php
namespace Cms\Business;

use Seitenbau\Registry as Registry;
use Seitenbau\Log as SbLog;

/**
 * Stellt die Business-Logik fuer Page zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Page extends Base\Service
{
  const PAGE_CREATE_ACTION = 'PAGE_CREATE_ACTION';
  const PAGE_COPY_ACTION = 'PAGE_COPY_ACTION';
  const PAGE_DELETE_ACTION = 'PAGE_DELETE_ACTION';
  const PAGE_EDIT_ACTION = 'PAGE_EDIT_ACTION';
  const PAGE_EDIT_META_ACTION = 'PAGE_EDIT_META_ACTION';
  const PAGE_MOVE_ACTION = 'PAGE_MOVE_ACTION';
  /**
   * @var array
   */
  protected $defaultNegativeUserPagePrivileges = array(
    'edit' => false,
    'delete' => false,
    'createChildren' => false
  );
  /**
   * @var array
   */
  protected $defaultPositiveUserPagePrivileges = array(
    'edit' => true,
    'delete' => true,
    'createChildren' => true
  );

  /**
   * @param string  $id
   * @param string  $websiteId
   *
   * @return \Cms\Data\Page
   */
  public function getById($id, $websiteId)
  {
    return $this->getService()->getById($id, $websiteId);
  }

  /**
   * Loescht eine Page, samt Subpages, aus dem System
   *
   * @param string $pageId
   * @param string $websiteId
   */
  public function delete($pageId, $websiteId)
  {
    $this->getService()->delete($pageId, $websiteId);
    $subPages = $this->getSubPages($pageId, $websiteId);
    foreach ($subPages as $page) {
      $this->getService()->delete($page, $websiteId);
    }
    $this->getBusiness('Website')->removePageFromNavigation($websiteId, $pageId);
  }

  /**
   * Gibt die Subpages einer Page zurueck anhand der Website-Navigation
   *
   * @param string $pageId
   * @param string $websiteId
   * @return array
   */
  public function getSubPages($pageId, $websiteId)
  {
    return $this->getService('Website')->getSubPagesFromNavigation($websiteId, $pageId);
  }

  /**
   * Erstellt eine neue Page
   *
   * Die neu erstelle Page wird direkt in die Navigationsstruktur der
   * zugehoerigen Website eingefuegt
   *
   * @param array $properties
   * @param string  $websiteId
   * @return  \Cms\Data\Page
   */
  public function create(array $properties, $websiteId)
  {
    $templateId = isset($properties['templateid'])
                ? $properties['templateid']
                : null;
    $template = $this->getService('Template')->getById($templateId, $websiteId);

    $properties['templatecontent'] = $template->getContent();
    $properties['templatecontentchecksum'] = $template->getContentchecksum();
    
    // Content der Page ueber den Reparser ermitteln
    $properties['content'] = $this->getBusiness('Reparse')->generateNewPageContent($template);
    $properties['globalcontent'] = $this->getGlobalContentFromContent($websiteId, $properties['content']);
    
    // Page anlegen
    $page = $this->getService()->create($websiteId, $properties);

    // Page in Navigation einhaengen
    $this->getBusiness('Website')->addPageToNavigation(
        $page,
        $websiteId,
        $properties['parentid'],
        $properties['insertbeforeid']
    );

    return $page;
  }

  /**
   * Verschiebt eine Page innerhalb der Website-Navigation
   *
   * @param string $pageId
   * @param string $websiteId
   * @param string $parentPageId
   * @param string $insertBeforePageId
   * @return  array
   */
  public function movePageInNavigation(
      $pageId,
      $websiteId,
      $parentPageId,
      $insertBeforePageId
  ) {
    // zur pruefung, ob Page vorhanden ist
    $page = $this->getService()->getById($pageId, $websiteId);

    $this->getBusiness('Website')->movePageInNavigation(
        $websiteId,
        $pageId,
        $parentPageId,
        $insertBeforePageId
    );

    $navigation = $this->getBusiness('Website')->getNavigationWithDataFromWebsite($websiteId);

    return $navigation;
  }

  /**
   * Kopiert eine Page der angegebenen Website unter einem neuen Namen
   *
   * @param string $pageId
   * @param string $websiteId
   * @param string $pageName
   * @return  \Cms\Data\Page
   */
  public function copy($pageId, $websiteId, $pageName)
  {
    $newPage = $this->getService()->copy($pageId, $websiteId, $pageName);

    $navigation = $this->getBusiness('Website')->addPageToNavigationAfterPageId(
        $newPage,
        $pageId
    );

    return $newPage;
  }

  /**
   * Aktualisiert eine vorhandene Page
   *
   * @param string $pageId
   * @param string $websiteId
   * @param array $attributes
   */
  public function update($pageId, $websiteId, $attributes)
  {
    $attributes['globalcontent'] = array();
    if (isset($attributes['content'])) {
      $attributes['globalcontent'] = $this->getGlobalContentFromContent($websiteId, $attributes['content']);
    }

    // Page mit Ursprungswerten auslesen
    $pageBeforeUpdate = $this->getById($pageId, $websiteId);

    // Page aktualisieren
    $pageAfterUpdate = $this->getService()->update($pageId, $websiteId, $attributes);

    // Page reparsen bei neuem Template
    if ($pageBeforeUpdate->getTemplateId() != $pageAfterUpdate->getTemplateId()) {
      $this->getBusiness('Reparse')->reparsePage($pageAfterUpdate);
      $this->updateGlobalVars($pageId, $websiteId);
    }

    return $pageAfterUpdate;
  }

  /**
   * Aktualisiert die globalen Variablen einer vorhandenen Page
   *
   * @param string $pageId
   * @param string $websiteId
   */
  public function updateGlobalVars($pageId, $websiteId)
  {
    $attributes = array();
    $content = $this->getService()->getById($pageId, $websiteId)->getContent();
    if (isset($content)) {
      $attributes['globalcontent'] = $this->getGlobalContentFromContent($websiteId, $content);
    }
    $result = $this->getService()->update($pageId, $websiteId, $attributes);

    return $result;
  }

  /**
   * Gibt Namen der zur uebergebenen Website ID gehoerigen Pages zurueck
   *
   * @param string  $websiteId
   * @param boolean $addAdditionalAttributes
   * @return array
   */
  public function getInfosByWebsiteId($websiteId, $addAdditionalAttributes = true)
  {
    $pageInfos = $this->getService()->getInfosByWebsiteId($websiteId);

    /** @var $templateInfo array */
    $templateInfo = $this->getBusiness('Template')->getInfoByWebsiteId($websiteId);

    $result = array();

    foreach ($pageInfos as $pageId => $pageInfo) {
      $result[$pageId] = $pageInfo;
      if ($addAdditionalAttributes) {
        $result[$pageId]['privileges'] = $this->getUserPriviliges(
            $websiteId,
            $pageId
        );
        if (isset($templateInfo[$pageInfo['templateId']])) {
          $result[$pageId]['templateName'] = $templateInfo[$pageInfo['templateId']]['name'];
        }
      }
    }

    return $result;
  }

  /**
   * returns all ids of pages related to the website given by website id
   *
   * @param string $websiteId
   * @return array
   */
  public function getIdsByWebsiteId($websiteId)
  {
    return $this->getService()->getIdsByWebsiteId($websiteId);
  }

  /**
   * Gibt die User-Rechte fuer eine bestimmte Page zurueck
   */
  private function getUserPriviliges($websiteId, $pageId)
  {
    $userPrivileges = $this->defaultNegativeUserPagePrivileges;

    if (\Seitenbau\Registry::getConfig()->group->check->activ == true) {
      $accessManager = $this->getAccessManager();
      if ($accessManager->hasIdentity()) {
        $identity = $accessManager->getIdentityAsArray();
        if ($this->isSuperuser($identity)) {
          return $this->defaultPositiveUserPagePrivileges;
        }
        $userIdOfAuthenticatedUser = $identity['id'];

        if (!isset($identity['id']) || empty($identity['id'])) {
          return $this->defaultNegativeUserPagePrivileges;
        }

        $groupsOfAuthenticatedUser = $this->getBusiness('Group')
                                          ->getAllByUserAndWebsiteId(
                                              $identity['id'],
                                              $websiteId
                                          );

        if (is_array($groupsOfAuthenticatedUser)
            && count($groupsOfAuthenticatedUser) > 0) {
          $pageUserPrivileges = $this->defaultNegativeUserPagePrivileges;

          foreach ($groupsOfAuthenticatedUser as $index => $group) {
            if ($this->hasGroupAllPagesRights($group)) {
              return $this->defaultPositiveUserPagePrivileges;
            }

            $pageRightsAndNavigation = $this->getBusiness('Group')
                                            ->getPageRightsAndNavigation(
                                                $group->getId(),
                                                $websiteId
                                            );

            $flattenedPages = $this->flattenPagesInNavigation(
                $pageRightsAndNavigation
            );

            foreach ($flattenedPages as $page) {
              if ($page['id'] === $pageId) {
                if ($this->hasFlattenedPageEditRights($page)) {
                  $pageUserPrivileges['edit'] = true;
                }
                if ($this->hasFlattenedPageSubAllRights($page)) {
                  $pageUserPrivileges['createChildren'] = true;
                }
                if ($this->hasFlattenedPageItsParentSubAllRights($page)) {
                  $pageUserPrivileges['delete'] = true;
                }
              }
            }
          }

          return $pageUserPrivileges;
        } else {
          return $this->defaultNegativeUserPagePrivileges;
        }
      }
    }

    return $userPrivileges;
  }
  /**
   * @param  array $page
   * @return boolean
   */
  private function hasFlattenedPageItsParentSubAllRights($page)
  {
    if (isset($page['child']) && isset($page['parent_rights'])) {
      if (isset($page['parent_rights']['subAll']['value'])
          && isset($page['parent_rights']['subAll']['inherited'])) {
        $parentPageSubAllRight = $page['parent_rights']['subAll']['value'];
        $parentPageSubAllInherited = $page['parent_rights']['subAll']['inherited'];
        return $parentPageSubAllRight === true || $parentPageSubAllInherited === true;
      }
    }

    return false;
  }
  /**
   * @param  array $page
   * @return boolean
   */
  private function hasFlattenedPageSubAllRights($page)
  {
    if (isset($page['rights']['subAll']['value'])
        && isset($page['rights']['subAll']['inherited'])) {
      $pageSubAllRight = $page['rights']['subAll']['value'];
      $pageSubAllInherited = $page['rights']['subAll']['inherited'];
      return $pageSubAllRight === true || $pageSubAllInherited === true;
    }

    return false;
  }
  /**
   * @param  array $page
   * @return boolean
   */
  private function hasFlattenedPageEditRights($page)
  {
    if (isset($page['rights']['edit']['value'])
        && isset($page['rights']['edit']['inherited'])) {
      $pageEditRight = $page['rights']['edit']['value'];
      $pageEditInherited = $page['rights']['edit']['inherited'];
      return $pageEditRight === true || $pageEditInherited === true;
    }

    return false;
  }
  /**
   * @param  array $pagesInNavigation
   * @param  array $flattenPages
   * @return array
   */
  private function flattenPagesInNavigation(
      $pagesInNavigation,
      $flattenPages = array(),
      $isChild = false,
      $parentRights = null,
      $parentId = null
  ) {
    foreach ($pagesInNavigation as $index => $page) {
      if ($isChild && $parentRights !== null && $parentId !== null) {
        $page['child'] = true;
        $page['parent_rights'] = $parentRights;
        $page['parent_id'] = $parentId;
      }

      if (isset($page['children'])
          && count($page['children']) > 0) {
        $copiedPage = $page;
        unset($copiedPage['children']);
        $flattenPages[] = $copiedPage;
        $flattenPages = $this->flattenPagesInNavigation(
            $page['children'],
            $flattenPages,
            true,
            $page['rights'],
            $page['id']
        );
      } else {
        $flattenPages[] = $page;
        unset($pagesInNavigation[$index]);
      }
    }

    return $flattenPages;
  }
  /**
   * @param  \Cms\Data\Group $group
   * @return boolean
   */
  private function hasGroupAllPagesRights(\Cms\Data\Group $group)
  {
    $groupRights = $group->getRights();
    if ($groupRights === \Cms\Dao\Group::DEFAULT_EMPTY_RIGHTS) {
      return false;
    }
    $groupRightsAsArray = json_decode($groupRights, true);
    if (!is_array($groupRightsAsArray)) {
      return  false;
    }
    foreach ($groupRightsAsArray as $groupRightsArray) {
      if (isset($groupRightsArray['area'])
          && $groupRightsArray['area'] === 'pages'
          && $groupRightsArray['privilege'] === 'all') {
        return true;
      }
    }

    return false;
  }


  /**
   * Ermittelt die globaleb Variablen aus einem Page-Content
   *
   * @param string $websiteId
   * @param string $content
   * @param boolean $isTemplate
   * @param array
   */
  public function getGlobalContentFromContent($websiteId, $content, $isTemplate = false)
  {
    $globalContent = array();
    $globalModuleVars = array();

    if (isset($content) && !is_array($content)) {
      $content = (is_string($content))
                  ? \Zend_Json::decode($content)
                  : $content;
    }

    $this->getGlobalContentFromContentRecursive(
        $websiteId,
        $content,
        $globalContent,
        $globalModuleVars,
        $isTemplate
    );

    return $globalContent;
  }

  /**
   * Ermittelt die globaleb Variablen aus einem Page-Content
   *
   * @param string $websiteId
   * @param array $content
   * @param array $globalcontent
   * @param array $globalModuleVars
   * @param array $isTemplate
   * @param array
   */
  protected function getGlobalContentFromContentRecursive(
      $websiteId,
      array $content,
      array &$globalcontent,
      array &$globalModuleVars,
      $isTemplate = false
  ) {
    if (is_array($content)) {
      foreach ($content as $unitData) {
        if (is_object($unitData)) {
          $unitData = get_object_vars($unitData);
        }
        
        // Modul-Id vorhanden?
        if (isset($unitData['moduleId'])) {
        // Muss noch die globalen Variablen-Namen dieses Moduls ermittelt werden?
          if (isset($unitData['moduleId']) && !empty($unitData['moduleId'])
              && !isset($globalModuleVars[$unitData['moduleId']])) {
          // Globale Variablen-Namen des Moduls ermitteln
            try {
              $variableNames = $this->getBusiness('Modul')->getGlobalVariableNamesByModulId(
                  $unitData['moduleId'],
                  $websiteId
              );
            } catch (\Exception $logOnly) {
              Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
              $variableNames = array();
            }
            $globalModuleVars[$unitData['moduleId']] = $variableNames;
          }

          // Globale Felder vorhanden
          if (is_array($globalModuleVars[$unitData['moduleId']])
              && count($globalModuleVars[$unitData['moduleId']]) > 0 ) {
            foreach ($globalModuleVars[$unitData['moduleId']] as $globalVarName => $globalVarField) {
              if (is_object($unitData['formValues'])) {
                $unitData['formValues'] = get_object_vars($unitData['formValues']);
              }
              
              // Unit-Variable ermitteln
              $isUnitValue = true;
              $unitGlobalData = null;
              if (isset($unitData['formValues'])
                  && array_key_exists($globalVarName, $unitData['formValues'])) {
                $isUnitValue = true;
                $unitGlobalData = $unitData['formValues'][$globalVarName];
              } else {
                // Module-Default Wert verwenden
                $isUnitValue = false;
                $unitGlobalData = $globalVarField['default'];
              }

                // Globale Varaibalen aufnehmen
              if (!isset($globalcontent[$globalVarName])) {
                $globalcontent[$globalVarName] = array();
              }
                $globalcontent[$globalVarName][] = array(
                'unitId'          => ($isTemplate ? null : $unitData['id']),
                'templateUnitId'  => ($isTemplate ? $unitData['id'] : $unitData['templateUnitId']),
                'moduleId'        => $unitData['moduleId'],
                'value'           => $unitGlobalData,
                'isUnitValue'     => $isUnitValue
                );
            }
          }
        }

        // Children durchlaufen
        if (isset($unitData['children'])) {
          $this->getGlobalContentFromContentRecursive(
              $websiteId,
              $unitData['children'],
              $globalcontent,
              $globalModuleVars,
              $isTemplate
          );
        }
      }
    }
  }

  /**
   * Liefert alle zum angegebenen Template verlinkte Pages zurueck
   *
   * @param string $templateId
   * @param string $websiteId
   * @param array
   */
  public function getTemplateLinkedPages($templateId, $websiteId)
  {
    return $this->getService('Page')->getTemplateLinkedPages($templateId, $websiteId);
  }

  /**
   * @param $websiteId
   *
   * @return \Cms\Data\PageType[]
   */
  public function getAllPageTypes($websiteId)
  {
    return $this->getPageTypeService()->getAll($websiteId);
  }

  /**
   * @return \Cms\Service\PageType
   */
  protected function getPageTypeService()
  {
    return $this->getService('PageType');
  }

  /**
   * @param array  $identity
   * @param string $rightName
   * @param mixed  $check
   *
   * @return bool
   */
  protected function hasUserRights($identity, $rightName, $check)
  {
    // superuser has all rights
    if ($this->isSuperuser($identity)) {
      return true;
    }

    switch ($rightName) {
      case 'GetAllPageTypes':
        if ($this->isUserInAnyWebsiteGroup($identity, $check['websiteId'])) {
          return true;
        }
            break;

      case 'createChildren':
      case 'edit':
      case 'delete':
        if ($this->checkUserGroupRights($identity, $check['websiteId'], 'pages', 'all')) {
          return true;
        }
        if (isset($check['websiteId']) && isset( $check['id'])) {
          if ($this->checkUserHasPageRights($check['websiteId'], $check['id'], $rightName)) {
            return true;
          }
        }
            break;
    }

    // default: no rights
    return false;
  }

  /**
   * @param $websiteId
   * @param $pageId
   * @param $rightName
   *
   * @return bool
   */
  protected function checkUserHasPageRights($websiteId, $pageId, $rightName)
  {
    $privileges = $this->getUserPriviliges($websiteId, $pageId);
    return (isset($privileges[$rightName]) && $privileges[$rightName] === true);
  }
}
