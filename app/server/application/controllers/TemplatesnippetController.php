<?php
use Cms\Business\TemplateSnippet as TemplateSnippetBusiness;
use Cms\Controller;
use Cms\Response\TemplateSnippet as Response;
use Cms\Business\Lock as LockBusiness;
use Seitenbau\Registry as Registry;

/**
 * TemplatesnippetController
 *
 * @package      Application
 * @subpackage   Controller
 */
class TemplatesnippetController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('TemplateSnippet');
    parent::init();
  }

  public function getallAction()
  {
    $validatedRequest = $this->getValidatedRequest('TemplateSnippet', 'GetAll');

    $check = array(
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('getAll', $check);

    $templateSnippets = $this->getBusiness()->getAll(
        $validatedRequest->getWebsiteId()
    );
    $this->responseData->setData(new Response\GetAll($templateSnippets));
  }

  public function getbyidAction()
  {
    $validatedRequest = $this->getValidatedRequest('TemplateSnippet', 'GetById');

    $check = array(
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('getById', $check);

    $templateSnippets = $this->getBusiness()->getById(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getId()
    );
    $this->responseData->setData(new Response\GetById($templateSnippets));
  }

  public function deleteAction()
  {
    $validatedRequest = $this->getValidatedRequest('TemplateSnippet', 'Delete');

    // Darf das TemplateSnippet geloescht werden?
    $check = array(
      'id'        => $validatedRequest->getIds(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('delete', $check);

    foreach ($validatedRequest->getIds() as $id) {
      Registry::getActionLogger()->logAction(TemplateSnippetBusiness::TEMPLATESNIPPET_DELETE_ACTION, array(
        'websiteId' => $validatedRequest->getWebsiteId(),
        'id'        => $id,
      ));
    }

    $this->getBusiness()->delete(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getIds()
    );
  }

  public function createAction()
  {
    $validatedRequest = $this->getValidatedRequest('TemplateSnippet', 'Create');

    // Darf ein neues TemplateSnippet erstellt werden?
    $check = array(
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('create', $check);

    $columnsValues = array(
      'websiteid'   => $validatedRequest->getWebsiteId(),
      'name'        => $validatedRequest->getName(),
      'description' => $validatedRequest->getDescription(),
      'category'    => $validatedRequest->getCategory(),
      'content'     => $validatedRequest->getContent(),
    );

    $templateSnippet = $this->getBusiness()->create(
        $validatedRequest->getWebsiteId(),
        $columnsValues
    );

    Registry::getActionLogger()->logAction(TemplateSnippetBusiness::TEMPLATESNIPPET_CREATE_ACTION, array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id'        => $templateSnippet->getId(),
      'name'      => $templateSnippet->getName(),
    ));

    $this->responseData->setData(new Response\Create($templateSnippet));
  }

  public function editAction()
  {
    $validatedRequest = $this->getValidatedRequest('TemplateSnippet', 'Edit');

    // Darf das TemplateSnippet bearbeitet werden?
    $check = array(
      'id'        => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('edit', $check);

    $columnsValues = array(
      'websiteid'   => $validatedRequest->getWebsiteId(),
      'name'        => $validatedRequest->getName(),
      'description' => $validatedRequest->getDescription(),
      'category'    => $validatedRequest->getCategory(),
      'content'     => $validatedRequest->getContent(),
    );

    $templateSnippet = $this->getBusiness()->update(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getId(),
        $columnsValues
    );

    Registry::getActionLogger()->logAction(TemplateSnippetBusiness::TEMPLATESNIPPET_EDIT_ACTION, array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id'        => $templateSnippet->getId(),
      'name'      => $templateSnippet->getName(),
    ));
  }
}
