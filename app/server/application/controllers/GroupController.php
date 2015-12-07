<?php
use Cms\Controller as Controller;
use Cms\Response\Group as Response;

/**
 * GroupController
 *
 * @package      Application
 * @subpackage   Controller
 */
class GroupController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Group');
    parent::init();
  }

  public function copyAction()
  {
    $this->getBusiness()->checkUserRights('copy');

    $validatedRequest = $this->getValidatedRequest('Group', 'Copy');

    $replicateId = $this->getBusiness()->copy(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getName()
    );
    $this->responseData->setData(new Response\Copy($replicateId));
  }

  public function createAction()
  {
    $this->getBusiness()->checkUserRights('create');

    $validatedRequest = $this->getValidatedRequest('Group', 'Create');

    $createValues = array(
      'name' => $validatedRequest->getName(),
      'rights' => $validatedRequest->getRights(),
    );

    $group = $this->getBusiness()->create(
        $validatedRequest->getWebsiteId(),
        $createValues
    );

    $this->responseData->setData(new Response\Create($group));
  }

  public function setpagerightsAction()
  {
    $this->getBusiness()->checkUserRights('setPageRights');

    $validatedRequest = $this->getValidatedRequest('Group', 'SetPageRights');

    $pageRights = array(
      'allrights' => $validatedRequest->getAllRights(),
      'rights' => $validatedRequest->getRights(),
    );

    $group = $this->getBusiness()->setPageRights(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $pageRights
    );

    $this->responseData->setData(new \Cms\Response\Group($group));
  }

  public function editAction()
  {
    $this->getBusiness()->checkUserRights('edit');

    $validatedRequest = $this->getValidatedRequest('Group', 'Edit');

    $editValues = array(
      'name' => $validatedRequest->getName(),
      'rights' => $validatedRequest->getRights(),
    );

    $group = $this->getBusiness()->edit(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $editValues
    );

    $this->responseData->setData(new \Cms\Response\Group($group));
  }

  public function getallAction()
  {
    $this->getBusiness()->checkUserRights('getAll');

    $validatedRequest = $this->getValidatedRequest('Group', 'GetAll');

    $all = $this->getBusiness()->getAllByWebsiteId(
        $validatedRequest->getWebsiteId()
    );

    $this->responseData->setData(new Response\GetAll($all));
  }

  public function getbyidAction()
  {
    $this->getBusiness()->checkUserRights('getById');

    $validatedRequest = $this->getValidatedRequest('Group', 'GetById');

    $group = $this->getBusiness()->getByIdAndWebsiteId(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );

    $this->responseData->setData(new Response\GetById($group));
  }

  public function deleteAction()
  {
    $this->getBusiness()->checkUserRights('delete');

    $validatedRequest = $this->getValidatedRequest('Group', 'Delete');

    $this->getBusiness()->delete(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );
  }

  public function addusersAction()
  {
    $this->getBusiness()->checkUserRights('addUsers');

    $validatedRequest = $this->getValidatedRequest('Group', 'AddUsers');

    $this->getBusiness()->addUsers(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getUserIds()
    );
  }

  public function removeusersAction()
  {
    $this->getBusiness()->checkUserRights('removeUsers');

    $validatedRequest = $this->getValidatedRequest('Group', 'RemoveUsers');

    $this->getBusiness()->removeUsers(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getUserIds()
    );
  }

  public function getpagerightsAction()
  {
    $this->getBusiness()->checkUserRights('getPageRights');

    $validatedRequest = $this->getValidatedRequest('Group', 'GetPageRights');

    $pageRightsAndNavigation = $this->getBusiness()->getPageRightsAndNavigation(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );

    $groupsAllRightsValue = $this->getBusiness()->getAllRightsValue(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );

    $this->responseData->setData(
        new Response\GetPageRights($pageRightsAndNavigation, $groupsAllRightsValue)
    );
  }
}
