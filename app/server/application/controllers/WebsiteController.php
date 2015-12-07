<?php
use Cms\Business\Website as WebsiteBusiness;
use Cms\Controller as Controller;
use Seitenbau\Registry as Registry;
use Cms\Response\Website as Response;
use Cms\Data\Website as WebsiteData;
use Cms\Business\Lock as LockBusiness;
use \Cms\Access\Manager as AccessManager;

/**
 * Website Controller
 *
 * @package      Application
 * @subpackage   Controller
 *
 * @method \Cms\Business\Website getBusiness
 *
 * @SWG\Resource(
 *     apiVersion="0.1",
 *     swaggerVersion="2.0",
 *     resourcePath="/website",
 *     basePath="/cms/service")
 */
class WebsiteController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Website');
    parent::init();
  }

  /**
   * @SWG\Api(
   *   path="/website/getall",
   *   @SWG\Operation(
   *        method="GET, POST",
   *        summary="Returns all websites",
   *        notes="Returns all websites of the instance.",
   *        type="WebsiteGetAll",
   *        nickname="getAll")
   * ))
   */
  public function getallAction()
  {
    $this->getBusiness()->checkUserRights('getall');
    
    $result = $this->getBusiness()->getAll();

    $this->responseData->setData(new Response\GetAll($result));

    if (is_array($result) && count($result) > 0) {
      $websites = $this->responseData->getData()->getWebsites();

      foreach ($websites as $website) {
        $navigation = $this->getNavigationWithDataFromWebsite($website);

        $websitePrivileges = $this->getWebsitePrivileges($website);
        $website->setPrivileges($websitePrivileges);
        $website->setNavigation($navigation);
        $website->setPublishInfo($this->getWebsitePublishInfo($website->getId()));
      }
    }
  }

  public function getbyidAction()
  {
    $validatedRequest = $this->getValidatedRequest('Website', 'GetById');
    
    $this->getBusiness()->checkUserRights('getbyid', array(
      'websiteId' => $validatedRequest->getId()
    ));

    $website = $this->getBusiness()->getById($validatedRequest->getId());

    $this->responseData->setData(new Response\GetById(
        $website,
        $this->getWebsitePrivileges($website),
        $this->getNavigationWithDataFromWebsite($website),
        $this->getWebsitePublishInfo($website->getId())
    ));
  }

  public function editAction()
  {
    /** @var $validatedRequest \Cms\Request\Website\Edit */
    $validatedRequest = $this->getValidatedRequest('Website', 'Edit');
    
    $this->getBusiness()->checkUserRights('edit', array(
      'websiteId' => $validatedRequest->getId()
    ));

    $attribute = array(
      'name' => $validatedRequest->getName(),
      'publishingenabled' => $validatedRequest->getPublishingEnabled(),
      'publish' => $validatedRequest->getPublish(),
      'colorscheme' => $validatedRequest->getColorscheme(),
      'resolutions' => $validatedRequest->getResolutions(),
      'home' => $validatedRequest->getHome()
    );
    $website = $this->getBusiness()->update($validatedRequest->getId(), $attribute);

    Registry::getActionLogger()->logAction(WebsiteBusiness::WEBSITE_EDIT_ACTION, array(
      'websiteId' => $website->getId(),
      'id'        => $website->getId(),
      'name'      => $website->getName(),
    ));

    $this->responseData->setData(new Response\Edit(
        $website,
        $this->getWebsitePrivileges($website),
        $this->getNavigationWithDataFromWebsite($website),
        $this->getWebsitePublishInfo($website->getId())
    ));
  }

  public function editcolorschemeAction()
  {
    $validatedRequest = $this->getValidatedRequest('Website', 'EditColorscheme');

    $this->getBusiness()->checkUserRights('editcolorscheme', array(
      'websiteId' => $validatedRequest->getId()
    ));

    $attribute = array(
      'colorscheme' => $validatedRequest->getColorscheme(),
    );
    $website = $this->getBusiness()->update($validatedRequest->getId(), $attribute);

    Registry::getActionLogger()->logAction(WebsiteBusiness::WEBSITE_EDIT_COLORSCHEME_ACTION, array(
      'websiteId' => $website->getId(),
      'id'        => $website->getId(),
      'name'      => $website->getName(),
    ));

    $this->responseData->setData(new Response\EditColorscheme($website));
  }

  public function editresolutionsAction()
  {
    $validatedRequest = $this->getValidatedRequest('Website', 'EditResolutions');

    $this->getBusiness()->checkUserRights('editresolutions', array(
      'websiteId' => $validatedRequest->getId()
    ));

    $attribute = array(
      'resolutions' => $validatedRequest->getResolutions(),
    );
    $website = $this->getBusiness()->update($validatedRequest->getId(), $attribute);

    Registry::getActionLogger()->logAction(WebsiteBusiness::WEBSITE_EDIT_RESOLUTION_ACTION, array(
      'websiteId' => $website->getId(),
      'id'        => $website->getId(),
      'name'      => $website->getName(),
    ));

    $this->responseData->setData(new Response\EditResolutions($website));
  }

  public function createAction()
  {
    $validatedRequest = $this->getValidatedRequest('Website', 'Create');
    
    $this->getBusiness()->checkUserRights('create');

    $attribute = array(
      'name' => $validatedRequest->getName(),
      'publish' => $validatedRequest->getPublish(),
      'colorscheme' => $validatedRequest->getColorscheme(),
      'resolutions' => $validatedRequest->getResolutions(),
      'home' => $validatedRequest->getHome()
    );
    $website = $this->getBusiness()->create($attribute);

    Registry::getActionLogger()->logAction(WebsiteBusiness::WEBSITE_CREATE_ACTION, array(
      'websiteId' => $website->getId(),
      'id'        => $website->getId(),
      'name'      => $website->getName(),
    ));

    $this->responseData->setData(new Response\Create(
        $website,
        $this->getWebsitePrivileges($website),
        $this->getNavigationWithDataFromWebsite($website),
        $this->getWebsitePublishInfo($website->getId())
    ));
  }

  public function copyAction()
  {
    $validatedRequest = $this->getValidatedRequest('Website', 'Copy');
    
    $this->getBusiness()->checkUserRights('copy', array(
      'websiteId' => $validatedRequest->getId()
    ));

    $website = $this->getBusiness()->copy(
        $validatedRequest->getId(),
        $validatedRequest->getName()
    );

    Registry::getActionLogger()->logAction(WebsiteBusiness::WEBSITE_COPY_ACTION, array(
      'websiteId' => $website->getId(),
      'id'        => $website->getId(),
      'name'      => $website->getName(),
      'sourceId'  => $validatedRequest->getId(),
    ));

    $this->responseData->setData(new Response\Copy($website));
  }

  public function deleteAction()
  {
    $validatedRequest = $this->getValidatedRequest('Website', 'Delete');

    $this->getBusiness()->checkUserRights('delete', array(
      'websiteId' => $validatedRequest->getId()
    ));

    if (!$this->checkUserLock(
        $validatedRequest->getRunId(),
        null,
        $validatedRequest->getId(),
        LockBusiness::LOCK_TYPE_WEBSITE,
        false
    )) {
      return;
    }

    $this->getBusiness()->delete($validatedRequest->getId());

    Registry::getActionLogger()->logAction(WebsiteBusiness::WEBSITE_DELETE_ACTION, array(
      'websiteId' => $validatedRequest->getId(),
      'id'        => $validatedRequest->getId(),
    ));
  }

  /**
   * @SWG\Api(
   *   path="/website/disablepublishing",
   *   @SWG\Operation(
   *        method="GET, POST",
   *        summary="Disable publishing the website",
   *        notes="Disable publishing the website with the given id.",
   *        type="WebsiteDisablePublishing",
   *        nickname="disablePublishing")
   * ))
   */
  public function disablepublishingAction()
  {
    $validatedRequest = $this->getValidatedRequest('Website', 'DisablePublishing');

    $this->getBusiness()->checkUserRights('disablepublishing', array(
      'websiteId' => $validatedRequest->getId()
    ));

    $website = $this->getBusiness()->disablePublishing($validatedRequest->getId());

    Registry::getActionLogger()->logAction(WebsiteBusiness::WEBSITE_DISABLE_PUBLISHING_ACTION, array(
      'websiteId' => $validatedRequest->getId(),
      'id'        => $validatedRequest->getId(),
    ));

    $this->responseData->setData(new Response\DisablePublishing(
        $website,
        $this->getWebsitePrivileges($website),
        $this->getNavigationWithDataFromWebsite($website),
        $this->getWebsitePublishInfo($website->getId())
    ));
  }

  public function exportAction()
  {
    $validateRequest = $this->getValidatedRequest('Website', 'Export');

    $this->getBusiness()->checkUserRights('export', array(
      'websiteId' => $validateRequest->getId()
    ));

    $this->getBusiness()->exportById($validateRequest->getId());

    Registry::getActionLogger()->logAction(WebsiteBusiness::WEBSITE_EXPORT_ACTION, array(
      'websiteId' => $validateRequest->getId(),
      'id'        => $validateRequest->getId(),
    ));
  }


  /**
   * @SWG\Api(
   *   path="/website/updatecontent",
   *   @SWG\Operation(
   *        method="GET, POST",
   *        summary="Update all contents of the website",
   *        notes="Updates all contents of the website with the given id.",
   *        type="WebsiteUpdateContent",
   *        nickname="UpdateContent")
   * ))
   */
  public function updatecontentAction()
  {
    /** @var \Cms\Request\Website\UpdateContent $validateRequest */
    $validateRequest = $this->getValidatedRequest('Website', 'UpdateContent');
    $websiteId = $validateRequest->getProperty('websiteid');

    $this->getBusiness()->checkUserRights('updatecontent', array(
      'websiteId' => $websiteId,
    ));

    $this->getBusiness('Lock')->removeLocksByWebsiteId($websiteId);

    $this->getBusiness('ContentUpdater')->updateAllContentsOfWebsite($websiteId);

    Registry::getActionLogger()->logAction(WebsiteBusiness::WEBSITE_UPDATE_CONTENT_ACTION, array(
      'websiteId' => $websiteId,
    ));
  }

  public function lockAction()
  {
    $validatedRequest = $this->getValidatedRequest('Website', 'Lock');

    $this->getBusiness()->checkUserRights('lock', array(
      'websiteId' => $validatedRequest->getWebsiteId()
    ));

    // Sperren der Page durchfuehren
    $lockData = $this->getBusiness('Lock')->lockItem(
        $validatedRequest->getRunId(),
        null,
        $validatedRequest->getWebsiteId(),
        LockBusiness::LOCK_TYPE_WEBSITE,
        $validatedRequest->getOverride()
    );

    $this->pushDataToErrorController(true);
    $this->responseData->setData(new Response\Lock($lockData));
  }

  /**
   * Holt anhand der Website-ID Daten zu den in der Navigation verwendeten Pages
   *
   * @param \Orm\Entity\Website $website
   * @return string
   */
  protected function getNavigationWithDataFromWebsite($website)
  {
    $pageInfos = $this->getBusiness('Page')->getInfosByWebsiteId($website->getId());

    $navigation = \Zend_Json::decode($website->getNavigation());
    $arrayVerwalter = new Seitenbau\ArrayData();

    if (is_array($pageInfos) && count($pageInfos) > 0 && is_array($navigation)) {
      $arrayVerwalter->mergeData($navigation, $pageInfos);
    }

    return $navigation;
  }

  /**
   *
   * @param  Cms\Data\Website $website
   * @return array
   */
  protected function getWebsitePrivileges($website)
  {
    $websiteId = $website->getId();
    $accessManager = AccessManager::singleton();
    $allWebsitePrivileges = $accessManager->getWebsitePrivileges();
    if (is_null($websiteId) || !isset($allWebsitePrivileges[$websiteId])) {
      return $this->getBusiness('Group')->getDefaultNegativeWebsitePrivileges();
    }
    return $allWebsitePrivileges[$websiteId];
  }

  /**
   * @param $websiteId
   * @return array
   */
  protected function getWebsitePublishInfo($websiteId)
  {
    /**
     * @var \Cms\Service\Publisher $publisher
     */
    $publisher = $this->getBusiness('Publisher');
    return array(
      'internalDomain' => $publisher->getInternalLiveUrl($websiteId),
      'url' => $publisher->getLiveUrl($websiteId),
    );
  }
}
