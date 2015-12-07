<?php
use Cms\Business\Template as TemplateBusiness;
use Cms\Controller;
use Cms\Response\Template as Response;
use Cms\Business\Lock as LockBusiness;
use Seitenbau\Registry as Registry;

/**
 * TemplateController
 *
 * @package      Application
 * @subpackage   Controller
 *
 * @method \Cms\Business\Template getBusiness
 */
class TemplateController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Template');
    parent::init();
  }

  public function getallAction()
  {
    $validatedRequest = $this->getValidatedRequest('Template', 'GetAll');

    $check = array(
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('getAll', $check);

    $templates = $this->getBusiness()->getAll(
        $validatedRequest->getWebsiteId()
    );
    $this->responseData->setData(new Response\GetAll($templates));
  }

  public function getbyidAction()
  {
    $validatedRequest = $this->getValidatedRequest('Template', 'GetById');

    $check = array(
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('getById', $check);

    $template = $this->getBusiness()->getById(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );
    $this->responseData->setData(new Response\GetById($template));
  }

  public function deleteAction()
  {
    $validatedRequest = $this->getValidatedRequest('Template', 'DeleteById');

    // Darf das Template geloescht werden?
    $check = array(
      'id'        => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('delete', $check);

    if (!$this->checkUserLock(
        $validatedRequest->getRunId(),
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        LockBusiness::LOCK_TYPE_TEMPLATE,
        false
    )) {
      return;
    }

    $this->getBusiness()->delete(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );

    Registry::getActionLogger()->logAction(TemplateBusiness::TEMPLATE_DELETE_ACTION, array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id'        => $validatedRequest->getId(),
    ));
  }

  public function createAction()
  {
    /** @var \Cms\Request\Template\Create $validatedRequest */
    $validatedRequest = $this->getValidatedRequest('Template', 'Create');

    // Darf ein neues Template erstellt werden?
    $check = array(
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('create', $check);

    $columnsValues = array(
      'name' => $validatedRequest->getName()
    );
    if ($validatedRequest->getContent() !== null) {
      $columnsValues['content'] = $validatedRequest->getContent();
    }
    if ($validatedRequest->getPageType() !== null) {
      $columnsValues['pageType'] = $validatedRequest->getPageType();
    }
    $template = $this->getBusiness()->create(
        $validatedRequest->getWebsiteId(),
        $columnsValues
    );

    Registry::getActionLogger()->logAction(TemplateBusiness::TEMPLATE_CREATE_ACTION, array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id'        => $template->getId(),
      'name'      => $template->getName(),
      'contentChecksum' => $template->getContentchecksum(),
      'pageType'  => $template->getPageType(),
    ));

    $this->responseData->setData(new Response\Create($template));
  }

  public function editAction()
  {
    $validatedRequest = $this->getValidatedRequest('Template', 'Edit');

    // Darf das Template bearbeitet werden?
    $check = array(
      'id'        => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('edit', $check);
    
    if (!$this->checkUserLock(
        $validatedRequest->getRunId(),
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        LockBusiness::LOCK_TYPE_TEMPLATE
    )) {
      return;
    }

    $columnsValues = array();
    if ($validatedRequest->getName() != null) {
      $columnsValues['name'] = $validatedRequest->getName();
    }
    if ($validatedRequest->getContent() !== null) {
      $columnsValues['content'] = $validatedRequest->getContent();
    }

    $template = $this->getBusiness()->update(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $columnsValues
    );

    Registry::getActionLogger()->logAction(TemplateBusiness::TEMPLATE_EDIT_ACTION, array(
      'websiteId'       => $validatedRequest->getWebsiteId(),
      'id'              => $template->getId(),
      'name'            => $template->getName(),
      'contentChecksum' => $template->getContentchecksum(),
    ));
  }

  public function editmetaAction()
  {
    $validatedRequest = $this->getValidatedRequest('Template', 'EditMeta');

    // Darf das Template bearbeitet werden?
    $check = array(
      'id'        => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('edit', $check);

    // Es darf kein Lock auf dem Modul vorhanden sein!
    $hasLock = $this->hasLock(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        LockBusiness::LOCK_TYPE_TEMPLATE
    );
    if (false !== $hasLock) {
      return false;
    }

    $attributes = array();
    if ($validatedRequest->getName() !== null) {
      $attributes['name'] = $validatedRequest->getName();
    }

    $template = $this->getBusiness()->update(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $attributes
    );

    Registry::getActionLogger()->logAction(TemplateBusiness::TEMPLATE_EDIT_META_ACTION, array(
      'websiteId'       => $validatedRequest->getWebsiteId(),
      'id'              => $template->getId(),
      'name'            => $template->getName(),
      'contentChecksum' => $template->getContentchecksum(),
    ));
  }

  public function lockAction()
  {
    $validatedRequest = $this->getValidatedRequest('Template', 'Lock');

    // Darf das Template bearbeitet werden?
    $check = array(
      'id'        => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('edit', $check);

    // Sperren der Page durchfuehren
    $lockData = $this->getBusiness('Lock')->lockItem(
        $validatedRequest->getRunId(),
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        LockBusiness::LOCK_TYPE_TEMPLATE,
        $validatedRequest->getOverride()
    );

    $this->pushDataToErrorController(true);
    $this->responseData->setData(new Response\Lock($lockData));
  }
}
