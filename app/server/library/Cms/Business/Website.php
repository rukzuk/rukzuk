<?php
namespace Cms\Business;

use Seitenbau\Registry;
use Cms\Data;
use Seitenbau\FileSystem as FS;
use Seitenbau\Log as SbLog;

/**
 * Stellt die Business-Logik fuer Website zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 *
 * @method \Cms\Service\Website getService
 */

class Website extends Base\Service
{
  const WEBSITE_CREATE_ACTION = 'WEBSITE_CREATE_ACTION';
  const WEBSITE_COPY_ACTION = 'WEBSITE_COPY_ACTION';
  const WEBSITE_DELETE_ACTION = 'WEBSITE_DELETE_ACTION';
  const WEBSITE_LIVE_DELETE_ACTION = 'WEBSITE_LIVE_DELETE_ACTION';
  const WEBSITE_DISABLE_PUBLISHING_ACTION = 'WEBSITE_DISABLE_PUBLISHING_ACTION';
  const WEBSITE_EDIT_ACTION = 'WEBSITE_EDIT_ACTION';
  const WEBSITE_EDIT_COLORSCHEME_ACTION = 'WEBSITE_EDIT_COLORSCHEME_ACTION';
  const WEBSITE_EDIT_RESOLUTION_ACTION = 'WEBSITE_EDIT_RESOLUTION_ACTION';
  const WEBSITE_EXPORT_ACTION = 'WEBSITE_EXPORT_ACTION';
  const WEBSITE_UPDATE_CONTENT_ACTION = 'WEBSITE_UPDATE_CONTENT_ACTION';

  /**
   * Gibt alle Websites zurueck
   *
   * Ist eine User-identity vorhanden, so werden nur die Websites zurueck-
   * gegeben, zu welchen der User Rechte hat
   *
   * @return  array
   */
  public function getAll()
  {
    $websites = $this->getService()->getAll();

    $accessManager = $this->getAccessManager();
    if (!$accessManager->isGroupCheckActiv()) {
      return $websites;
    }
    
    $identity = $accessManager->getIdentityAsArray();

    if ($this->isSuperuser($identity)) {
      return $websites;
    }
    
    $returnWebsites = array();
    foreach ($websites as $website) {
      if ($accessManager->isInAnyWebsiteGroup($identity, $website->getId())) {
        $returnWebsites[] = $website;
      }
    }

    return $returnWebsites;
  }

  /**
   * get the website data object for given website id
   *
   * @param string $websiteId
   *
   * @return \Cms\Data\Website
   */
  public function getById($websiteId)
  {
    return $this->getService()->getById($websiteId);
  }

  /**
   * creates a new website with the given attributes
   *
   * @param array $attributes
   * @param bool  $useIdFromAttributes
   *
   * @return  \Cms\Data\Website
   */
  public function create(array $attributes, $useIdFromAttributes = false)
  {
    $website = $this->getService()->create($attributes, $useIdFromAttributes);
    $this->getModuleService()->createStorageForWebsite($website->getId());
    return $website;
  }

  /**
   * Kopiert eine Website samt zugehoeriger Relationen
   *
   * @param string  $websiteId  ID der zu kopierenden Website
   * @param string  $newName  Name der neuen Website
   * @return \Cms\Data\Website
   */
  public function copy($websiteId, $newName)
  {
    $newWebsite = $this->getService()->copy($websiteId, $newName);
    $newWebsiteId = $newWebsite->getId();

    $this->getService('Page')->copyPagesToNewWebsite($websiteId, $newWebsiteId);
    $this->getService('Template')->copyToNewWebsite($websiteId, $newWebsiteId);
    $this->getService('TemplateSnippet')->copyToNewWebsite($websiteId, $newWebsiteId);
    $this->getService('Modul')->copyToNewWebsite($websiteId, $newWebsiteId);
    $this->getService('Album')->copyAlbumsToNewWebsiteId($websiteId, $newWebsiteId);
    $this->getService('Media')->copyMediaToNewWebsite($websiteId, $newWebsiteId);
    $this->getWebsiteSettingsService()->copyToNewWebsite($websiteId, $newWebsiteId);
    $this->getPackageService()->copyToNewWebsite($websiteId, $newWebsiteId);

    return $newWebsite;
  }

  /**
   * Entfernt eine Website samt Relationen und Medien aus dem System
   *
   * @param string $websiteId
   */
  public function delete($websiteId)
  {
    // remove live site first
    $this->deletePublishedWebsite($websiteId);
    // remove stuff from db
    $this->deleteAssociationsFromWebsite($websiteId);
    $this->getService()->deleteById($websiteId);
  }

  /**
   * Removes a live website with the given website id
   * @param $websiteId
   */
  public function deletePublishedWebsite($websiteId)
  {
    // publisher triggers external call to remove live site
    $this->getBusiness('Publisher')->deletePublishedWebsite($websiteId);
  }

  /**
   * Loescht alle Verbindungen zu einer Website
   *
   * @param string $websiteId
   */
  private function deleteAssociationsFromWebsite($websiteId)
  {
    // this order is importent proper deletion (relation checks)
    $this->deletePagesFromWebsite($websiteId);
    $this->deleteTemplatesFromWebsite($websiteId);
    $this->deleteTemplateSnippetsFromWebsite($websiteId);
    $this->deleteModulesFromWebsiteId($websiteId);
    $this->deleteMediaFromWebsiteId($websiteId);
    $this->deleteWebsiteSettingsFromWebsiteId($websiteId);
    $this->deletePackagesFromWebsite($websiteId);
  }

  /**
   * Entfernt alle Pages einer Website
   *
   * @param string $websiteId
   */
  protected function deletePagesFromWebsite($websiteId)
  {
    $pageService = $this->getService('Page');
    $pageService->deleteByWebsiteId($websiteId);
  }

  /**
   * Entfernt alle Templates einer Website
   *
   * @param string $websiteId
   */
  protected function deleteTemplatesFromWebsite($websiteId)
  {
    $templateService = $this->getService('Template');
    $templateService->deleteByWebsiteId($websiteId);
  }

  /**
   * Entfernt alle TemplateSnippets einer Website
   *
   * @param string $websiteId
   */
  protected function deleteTemplateSnippetsFromWebsite($websiteId)
  {
    $templateSnippetService = $this->getService('TemplateSnippet');
    $templateSnippetService->deleteByWebsiteId($websiteId);
  }

  /**
   * Entfernt alle Module einer Website
   * @param string $websiteId
   */
  protected function deleteModulesFromWebsiteId($websiteId)
  {
    $this->getModuleService()->deleteByWebsiteId($websiteId, true);
  }

  /**
   * @param $websiteId
   */
  protected function deleteWebsiteSettingsFromWebsiteId($websiteId)
  {
    $this->getWebsiteSettingsService()->deleteByWebsiteId($websiteId);
  }

  /**
   * @param $websiteId
   */
  protected function deletePackagesFromWebsite($websiteId)
  {
    $this->getPackageService()->deleteByWebsiteId($websiteId);
  }

  /**
   * Entfernt alle Media Items einer Website
   *
   * @param string $websiteId
   */
  protected function deleteMediaFromWebsiteId($websiteId)
  {
    $mediaService = $this->getService('Media');
    $medias = $mediaService->getByWebsiteIdAndFilter($websiteId);
    $mediaIds = array();

    if (is_array($medias)) {
      foreach ($medias as $media) {
        $mediaIds[] = $media->getId();
      }
    }

    $this->getBusiness('Media')->delete($mediaIds, $websiteId, false);

    $config = Registry::getConfig();

    $websiteMediaDirectory = $config->media->files->directory
      . DIRECTORY_SEPARATOR . $websiteId;

    if (is_dir($websiteMediaDirectory)) {
      rmdir($websiteMediaDirectory);
    }
  }

  /**
   * Holt anhand der Website-ID Daten zu den in der Navigation verwendeten Pages
   *
   * @param \Orm\Entity\Website|string $website
   * @return string
   */
  public function getNavigationWithDataFromWebsite($websiteParam)
  {
    if ($websiteParam instanceof \Orm\Entity\Website) {
      $website = $websiteParam;
    } elseif (\is_string($websiteParam)) {
      $website = $this->getService()->getById($websiteParam);
    } else {
      return;
    }

    $pageInfos = $this->getService('Page')->getInfosByWebsiteId($website->getId());

    $navigation = \Seitenbau\Json::decode($website->getNavigation());
    if (!is_array($navigation)) {
      $navigation = array();
    }

    $arrayVerwalter = new \Seitenbau\ArrayData();
    if (is_array($pageInfos) && count($pageInfos) > 0 && is_array($navigation)) {
      $arrayVerwalter->mergeData($navigation, $pageInfos);
    }

    return $navigation;
  }

  /**
   * Verschiebt eine Page innerhalb einer Website Navigation
   *
   * @param string $websiteId
   * @param string $pageId
   * @param string $parentId
   * @param string $beforeId
   * @return array
   */
  public function movePageInNavigation($websiteId, $pageId, $parentId, $beforeId)
  {
    $website = $this->getService()->getById($websiteId);
    $navigation = \Seitenbau\Json::decode($website->getNavigation());

    $data = new \Seitenbau\ArrayData();
    $newNavigation = $data->move($navigation, $pageId, $parentId, $beforeId);
    if ($newNavigation == false) {
      throw new \Cms\Exception(752, __METHOD__, __LINE__);
    }
    $newNavigation = \Zend_Json::encode($newNavigation);

    $attributes = array('navigation' => $newNavigation);
    $this->getService()->update($websiteId, $attributes);

    return $newNavigation;
  }

  /**
   * Fuegt in der Website-Navigation eine Page hinter eine andere angegebene
   * Page-ID ein
   *
   * @param \Orm\Entity\Page $insertPage
   * @param string $pageId
   * @return array  Neue Navigation
   */
  public function addPageToNavigationAfterPageId(Data\Page $insertPage, $pageId)
  {
    $dataPage = array(
      'id'  => $insertPage->getId()
    );

    $website = $this->getService()->getById($insertPage->getWebsiteid());
    $navigation = \Seitenbau\Json::decode($website->getNavigation());

    $data = new \Seitenbau\ArrayData();
    $newNavigation = $data->insertAfter($navigation, $dataPage, $pageId);
    $newNavigation = \Zend_Json::encode($newNavigation);

    $attributes = array('navigation' => $newNavigation);
    $this->getService()->update($insertPage->getWebsiteid(), $attributes);

    return $newNavigation;
  }

  /**
   * Loescht eine Page aus der Navigation einer Website
   *
   * @param string $websiteId
   * @param string $pageid
   */
  public function removePageFromNavigation($websiteId, $pageid)
  {
    $website = $this->getService()->getById($websiteId);
    $navigation = \Seitenbau\Json::decode($website->getNavigation());

    if (is_array($navigation)) {
      $data = new \Seitenbau\ArrayData();
      $data->remove($navigation, $pageid);

      $result = \Zend_Json::encode($navigation);

      $attributes = array('navigation' => $result);
      $this->getService()->update($websiteId, $attributes);

      return $result;
    }
  }

  /**
   * updates the website given by id
   *
   * @param string  $id
   * @param array   $attributes
   *
   * @return \Cms\Data\Website
   */
  public function update($id, array $attributes)
  {
    if (isset($attributes['publish'])
        && !$this->isPublishDataPasswordNewSet($attributes['publish'])) {
      $oldPassword = $this->getPasswordFromOrm($this->getById($id));

      $attributes['publish'] = str_replace(
          '"password":"*****"',
          '"password":"' . $oldPassword . '"',
          $attributes['publish']
      );
    }

    return $this->getService()->update($id, $attributes);
  }

  /**
   * @param string $id
   *
   * @return \Cms\Data\Website
   */
  public function disablePublishing($id)
  {
    $this->getById($id);
    $this->deletePublishedWebsite($id);
    $this->deleteAllWebsiteBuilds($id);
    $website = $this->getService()->disablePublishing($id);
    return $website;
  }

  /**
   * @param string  $id
   */
  protected function deleteAllWebsiteBuilds($id)
  {
    /** @var $builderBusiness \Cms\Business\Builder */
    $builderBusiness = $this->getBusiness('Builder');
    $builderBusiness->deleteAllWebsiteBuilds($id);
  }

  /**
   * Prueft, ob in den Publish Daten, dass Passwort neu gesetzt wurde
   *
   * @return  boolean
   */
  protected function isPublishDataPasswordNewSet($publishData)
  {
    if (!is_array($publishData)) {
      $data = \Seitenbau\Json::decode($publishData);
    } else {
      $data = $publishData;
    }

    if (isset($data['password']) && $data['password'] == '*****') {
      return false;
    }

    return true;
  }

  /**
   * Gibt das Password aus dem Daten Objekt zurueck
   *
   * @param \Cms\Data\Website $website
   * @return  string
   */
  protected function getPasswordFromOrm(\Cms\Data\Website $website)
  {
    $publishData = $website->getPublish();
    $publishArr = \Seitenbau\Json::decode($publishData);
    if (isset($publishArr['password'])) {
      return $publishArr['password'];
    } else {
      return '';
    }
  }

  /**
   * Gibt die ID der ersten Page in der Navigation der Website zurueck
   *
   * @param string  $websiteId
   * @return  string|false  Die Page-Id oder false
   */
  public function getFirstPageFromWebsite($websiteId)
  {
    $website = $this->getById($websiteId);

    $navigation = json_decode($website->getNavigation());
    if (is_array($navigation) && count($navigation) > 0) {
      $id = $navigation[0]->id;
    } else {
      // keine Page vorhanden (evtl. leere Website)
      return false;
    }

    return $id;
  }

  /**
   * @return \Cms\Service\Modul
   */
  protected function getModuleService()
  {
    return $this->getService('Modul');
  }

  /**
   * @return \Cms\Service\WebsiteSettings
   */
  protected function getWebsiteSettingsService()
  {
    return $this->getService('WebsiteSettings');
  }

  /**
   * @return \Cms\Service\Package
   */
  protected function getPackageService()
  {
    return $this->getService('Package');
  }

  /**
   * Pruefung, ob der angemeldete User die Rechte fuer die aufgerufene Aktion
   * besitzt
   *
   * @param array  $identity
   * @param string $rightname Name des Rechts, auf das geprueft wird
   * @param mixed  $check
   *
   * @return bool
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // ausschliesslich Superuser haben bestimmte Rechte
    if ($this->isSuperuser($identity)) {
      return true;
    }

    switch ($rightname)
    {
      case 'getall':
          // only allowed websites will be responsed
            return true;
        break;
      case 'getbyid':
      case 'export':
        if ($this->isUserInAnyWebsiteGroup($identity, $check['websiteId'])) {
          return true;
        }
            break;
      case 'editcolorscheme':
        if ($this->checkWebsitePrivilegeForIdentity($identity, $check['websiteId'], 'colorscheme', 'all')) {
          return true;
        }
            break;
      case 'editresolutions':
        if ($this->checkWebsitePrivilegeForIdentity($identity, $check['websiteId'], 'templates', 'all')) {
          return true;
        }
            break;
      case 'edit':
      case 'create':
      case 'copy':
      case 'delete':
      case 'lock':
          // only superuser
            return false;
        break;

    }

    return false;
  }
}
