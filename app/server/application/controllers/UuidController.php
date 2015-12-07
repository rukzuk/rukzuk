<?php
use Cms\Controller as Controller;
use Cms\Response\Uuid as Response;

/**
 * UuidController
 *
 * @package      Application
 * @subpackage   Controller
 */

class UuidController extends Controller\Action
{
  const DATA_MODUL = 'Modul';
  const DATA_TEMPLATE = 'Template';
  const DATA_UNIT = 'Unit';
  const DATA_PAGE = 'Page';
  const DATA_SITE = 'Site';

  public function init()
  {
    parent::init();
    $this->initBusiness('Uuid');
  }
  
  public function getmoduleidsAction()
  {
    $validatedRequest = $this->getValidatedRequest('Uuid', 'GetUuids');
    $uuids = $this->getBusiness()->getUuids(
        self::DATA_MODUL,
        $validatedRequest->getCount()
    );
    $this->responseData->setData(new Response\GetUuids($uuids));
  }

  public function getpageidsAction()
  {
    $validatedRequest = $this->getValidatedRequest('Uuid', 'GetUuids');
    $uuids = $this->getBusiness()->getUuids(
        self::DATA_PAGE,
        $validatedRequest->getCount()
    );
    $this->responseData->setData(new Response\GetUuids($uuids));
  }

  public function getsiteidsAction()
  {
    $validatedRequest = $this->getValidatedRequest('Uuid', 'GetUuids');
    $uuids = $this->getBusiness()->getUuids(
        self::DATA_SITE,
        $validatedRequest->getCount()
    );
    $this->responseData->setData(new Response\GetUuids($uuids));
  }

  public function gettemplateidsAction()
  {
    $validatedRequest = $this->getValidatedRequest('Uuid', 'GetUuids');
    $uuids = $this->getBusiness()->getUuids(
        self::DATA_TEMPLATE,
        $validatedRequest->getCount()
    );
    $this->responseData->setData(new Response\GetUuids($uuids));
  }

  public function getunitidsAction()
  {
    $validatedRequest = $this->getValidatedRequest('Uuid', 'GetUuids');
    $uuids = $this->getBusiness()->getUuids(
        self::DATA_UNIT,
        $validatedRequest->getCount()
    );
    $this->responseData->setData(new Response\GetUuids($uuids));
  }
}
