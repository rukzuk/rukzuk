<?php
use Cms\Controller as Controller;
use Cms\Response\Album as Response;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as Log;

/**
 * AlbumController
 *
 * @package      Application
 * @subpackage   Controller
 */
class AlbumController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Album');
    parent::init();
  }

  public function createAction()
  {
    $validatedRequest = $this->getValidatedRequest('Album', 'Create');

    $this->getBusiness()->checkUserRights('create', $validatedRequest->getWebsiteId());

    $createValues = array(
      'name' => $validatedRequest->getName(),
    );
    $album = $this->getBusiness()->create(
        $validatedRequest->getWebsiteId(),
        $createValues
    );
    $this->responseData->setData(new Response\Create($album));
  }

  public function editAction()
  {
    $validatedRequest = $this->getValidatedRequest('Album', 'Edit');

    $this->getBusiness()->checkUserRights('edit', $validatedRequest->getWebsiteId());

    $updateValues = array(
      'name' => $validatedRequest->getName(),
    );
    $album = $this->getBusiness()->edit(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $updateValues
    );
  }

  public function getallAction()
  {
    $validatedRequest = $this->getValidatedRequest('Album', 'GetAll');

    $this->getBusiness()->checkUserRights('getAll', $validatedRequest->getWebsiteId());

    $all = $this->getBusiness()->getAllByWebsiteId(
        $validatedRequest->getWebsiteId()
    );
    $this->responseData->setData(new Response\GetAll($all));
  }

  public function deleteAction()
  {
    $validatedRequest = $this->getValidatedRequest('Album', 'Delete');

    $this->getBusiness()->checkUserRights('delete', $validatedRequest->getWebsiteId());

    $notDeletableMediaIds = $this->getBusiness()->delete(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );

    $this->responseData->setData(new Response\Delete($notDeletableMediaIds));
  }
}
