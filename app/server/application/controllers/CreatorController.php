<?php


use Cms\Controller\Action;
use Seitenbau\Registry as Registry;

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

    Registry::getLogger()->log(
      __CLASS__,
      __METHOD__,
      sprintf('Prepare action for website id "%s"', $validatedRequest->getWebsiteId()),
      \Zend_Log::NOTICE
    );

    $data = $this->getBusiness()->prepare(
        $validatedRequest->getCreatorName(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getPrepare(),
        $validatedRequest->getInfo()
    );
    $this->responseData->setData($data);
  }
}
