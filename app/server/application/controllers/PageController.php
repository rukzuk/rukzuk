<?php
use Cms\Controller as Controller;
use Cms\Response\Page as Response;
use Cms\Business\Page as PageBusiness;
use Cms\Business\Lock as LockBusiness;
use Seitenbau\Registry as Registry;

/**
 * Page Controller
 *
 * @package      Application
 * @subpackage   Controller
 *
 * @method \Cms\Business\Page getBusiness
 */
class PageController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Page');
    parent::init();
  }

  public function getbyidAction()
  {
    $validatedRequest = $this->getValidatedRequest('Page', 'GetById');

    $page = $this->getBusiness()->getById(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );

    $this->responseData->setData(new Response\GetById($page));
  }

  public function copyAction()
  {
    $validatedRequest = $this->getValidatedRequest('Page', 'Copy');

    $check = array(
      'id' => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('createChildren', $check);

    $page = $this->getBusiness()->copy(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getName()
    );

    $navigation = $this->getNavigationWithDataByWebsiteId(
        $validatedRequest->getWebsiteId()
    );

    Registry::getActionLogger()->logAction(PageBusiness::PAGE_COPY_ACTION, array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id' => $page->getId(),
      'name' => $page->getName(),
      'fromPageId' => $validatedRequest->getId(),
    ));

    $this->responseData->setData(new Response\Copy($page));
    $this->responseData->getData()->setNavigation($navigation);
  }

  public function deleteAction()
  {
    $validatedRequest = $this->getValidatedRequest('Page', 'Delete');

    $check = array(
      'id' => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('delete', $check);

    if (!$this->checkUserLock(
        $validatedRequest->getRunId(),
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        LockBusiness::LOCK_TYPE_PAGE,
        false
    )
    ) {
      return;
    }

    $this->getBusiness()->delete(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );

    $navigation = $this->getNavigationWithDataByWebsiteId(
        $validatedRequest->getWebsiteId()
    );

    Registry::getActionLogger()->logAction(PageBusiness::PAGE_DELETE_ACTION, array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id' => $validatedRequest->getId()
    ));

    $responseData = new Response\Delete();
    $responseData->setNavigation($navigation);
    $this->responseData->setData($responseData);
  }

  /**
   * @SWG\Api(
   *  path="/page/edit",
   *  @SWG\Operation(
   *    method="POST",
   *    summary="Edit the page content",
   *    nickname="edit",
   *    type="Response/Base",
   *    @SWG\Parameter(name="params", type="Request/Page/Edit",
   *      required=true, paramType="query")
   * )))
   */
  public function editAction()
  {
    /** @var \Cms\Request\Page\Edit $validatedRequest */
    $validatedRequest = $this->getValidatedRequest('Page', 'Edit');

    $check = array(
      'id' => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('edit', $check);

    if (!$this->checkUserLock(
        $validatedRequest->getRunId(),
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        LockBusiness::LOCK_TYPE_PAGE
    )
    ) {
      return;
    }

    $attributes = array();
    if ($validatedRequest->getContent() !== null) {
      $attributes['content'] = $validatedRequest->getContent();
    }

    $page = $this->getBusiness()->update(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $attributes
    );

    Registry::getActionLogger()->logAction(PageBusiness::PAGE_EDIT_ACTION, array(
      'websiteId' => $page->getWebsiteid(),
      'id' => $page->getId(),
      'name' => $page->getName(),
    ));
  }

  /**
   * @SWG\Api(
   *  path="/page/editMeta",
   *  @SWG\Operation(
   *    method="POST",
   *    summary="Edit the page meta",
   *    nickname="editMeta",
   *    type="Response/Base",
   *    @SWG\Parameter(name="params", type="Request/Page/EditMeta",
   *      required=true, paramType="query")
   * )))
   */
  public function editmetaAction()
  {
    /** @var \Cms\Request\Page\EditMeta $validatedRequest */
    $validatedRequest = $this->getValidatedRequest('Page', 'EditMeta');

    $id = $validatedRequest->getProperty('id');
    $websiteId = $validatedRequest->getProperty('websiteid');

    $this->getBusiness()->checkUserRights('edit', array(
      'id' => $id,
      'websiteId' => $websiteId,
    ));

    $attributes = array();
    if ($validatedRequest->hasProperty('name')) {
      $attributes['name'] = $validatedRequest->getProperty('name');
    }
    if ($validatedRequest->hasProperty('description')) {
      $attributes['description'] = $validatedRequest->getProperty('description');
    }
    if ($validatedRequest->hasProperty('innavigation')) {
      $attributes['innavigation'] = $validatedRequest->getProperty('innavigation');
    }
    if ($validatedRequest->hasProperty('navigationtitle')) {
      $attributes['navigationtitle'] = $validatedRequest->getProperty('navigationtitle');
    }
    if ($validatedRequest->hasProperty('date')) {
      $attributes['date'] = $validatedRequest->getProperty('date');
    }
    if ($validatedRequest->hasProperty('mediaid')) {
      $attributes['mediaid'] = $validatedRequest->getProperty('mediaid');
    }
    if ($validatedRequest->hasProperty('pageattributes')) {
      $attributes['pageAttributes'] = $validatedRequest->getProperty('pageattributes');
    }

    $page = $this->getBusiness()->update(
        $id,
        $websiteId,
        $attributes
    );

    Registry::getActionLogger()->logAction(PageBusiness::PAGE_EDIT_META_ACTION, array(
      'id' => $id,
      'websiteId' => $websiteId,
      'name' => $page->getName(),
    ));
  }

  /**
   * Neue Page in einer Website anlegen
   */
  public function createAction()
  {
    /** @var \Cms\Request\Page\Create $validatedRequest */
    $validatedRequest = $this->getValidatedRequest('Page', 'Create');

    $check = array(
      'id' => $validatedRequest->getParentId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('createChildren', $check);

    $properties = array(
      'parentid' => $validatedRequest->getParentId(),
      'insertbeforeid' => $validatedRequest->getInsertBeforeId(),
      'websiteid' => $validatedRequest->getWebsiteId(),
      'templateid' => $validatedRequest->getTemplateId(),
      'name' => $validatedRequest->getName(),
      'description' => $validatedRequest->getDescription(),
      'mediaid' => $validatedRequest->getMediaId(),
      'date' => $validatedRequest->getDate(),
      'innavigation' => $validatedRequest->getInNavigation(),
      'navigationtitle' => $validatedRequest->getNavigationTitle(),
      'content' => $validatedRequest->getContent(),
      'pageType' => $validatedRequest->getPageType(),
      'pageAttributes' => $validatedRequest->getPageAttributes(),
    );

    $page = $this->getBusiness()->create(
        $properties,
        $validatedRequest->getWebsiteId()
    );

    $navigation = $this->getNavigationWithDataByWebsiteId(
        $validatedRequest->getWebsiteId()
    );
    $result['navigation'] = $navigation;
    $result['id'] = $page->getId();


    Registry::getActionLogger()->logAction(PageBusiness::PAGE_CREATE_ACTION, array(
      'websiteId' => $page->getWebsiteid(),
      'id' => $page->getId(),
      'name' => $page->getName(),
      'parentId' => $validatedRequest->getParentId(),
      'beforeId' => $validatedRequest->getInsertBeforeId(),
    ));

    $this->responseData->setData(new Response\Create($result));
  }

  /**
   * Verschiebt eine Page innerhalb der Website-Navigation
   */
  public function moveAction()
  {
    $validatedRequest = $this->getValidatedRequest('Page', 'Move');

    // darf page geloescht werden?
    $check = array(
      'id' => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('createChildren', $check);

    // darf page an neuer stelle eingebunden werden?
    $check = array(
      'id' => $validatedRequest->getParentId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('createChildren', $check);

    $navigation = $this->getBusiness()->movePageInNavigation(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getParentId(),
        $validatedRequest->getInsertBeforeId()
    );

    Registry::getActionLogger()->logAction(PageBusiness::PAGE_MOVE_ACTION, array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id' => $validatedRequest->getId(),
      'parentId' => $validatedRequest->getParentId(),
      'beforeId' => $validatedRequest->getInsertBeforeId(),
    ));

    $this->responseData->setData(new Response\Move($navigation));
  }

  public function getallpagetypesAction()
  {
    /** @var \Cms\Request\Page\GetAllPageTypes $validatedRequest */
    $validatedRequest = $this->getValidatedRequest('Page', 'GetAllPageTypes');

    $this->getBusiness()->checkUserRights('GetAllPageTypes', array(
      'websiteId' => $validatedRequest->getWebsiteId()
    ));

    $allPageTypes = $this->getBusiness()->getAllPageTypes($validatedRequest->getWebsiteId());

    $this->responseData->setData(new Response\GetAllPageTypes($allPageTypes));
  }

  public function lockAction()
  {
    $validatedRequest = $this->getValidatedRequest('Page', 'Lock');

    // Darf die Page bearbeitet werden?
    $check = array(
      'id' => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('edit', $check);

    // Sperren der Page durchfuehren
    $lockData = $this->getBusiness('Lock')->lockItem(
        $validatedRequest->getRunId(),
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        LockBusiness::LOCK_TYPE_PAGE,
        $validatedRequest->getOverride()
    );

    $this->pushDataToErrorController(true);
    $this->responseData->setData(new Response\Lock($lockData));
  }

  /**
   * Holt anhand der Website-ID Daten zu den in der Navigation verwendeten Pages
   *
   * @param string $websiteId
   *
   * @return string
   */
  protected function getNavigationWithDataByWebsiteId($websiteId)
  {
    $website = $this->getBusiness('Website')->getById($websiteId);

    $pageInfos = $this->getBusiness('Page')->getInfosByWebsiteId($website->getId());

    $navigation = \Zend_Json::decode($website->getNavigation());
    $arrayVerwalter = new Seitenbau\ArrayData();

    if (is_array($pageInfos) && count($pageInfos) > 0 && is_array($navigation)) {
      $arrayVerwalter->mergeData($navigation, $pageInfos);
    }

    return $navigation;
  }
}
