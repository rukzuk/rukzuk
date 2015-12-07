<?php


use Cms\Controller\Action;

class CreatorController extends Action
{
  public function init()
  {
    $this->initBusiness('Creator');
    parent::init();
  }

  public function prepareAction()
  {
    /** @var $validatedRequest \Cms\Request\Creator\Prepare */
    $validatedRequest = $this->getValidatedRequest('Creator', 'Prepare');
    $this->getBusiness()->checkUserRights('prepare', array(
      'websiteId' => $validatedRequest->getWebsiteId()
    ));
    $data = $this->getBusiness()->prepare(
        $validatedRequest->getCreatorName(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getPrepare(),
        $validatedRequest->getInfo()
    );
    $this->responseData->setData($data);
  }
}
