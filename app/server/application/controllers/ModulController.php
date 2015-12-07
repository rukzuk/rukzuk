<?php
use Cms\Business\Modul as ModulBusiness;
use Cms\Controller as Controller;
use Cms\Response\Modul as Response;
use Cms\Business\Lock as LockBusiness;
use Seitenbau\Registry as Registry;

/**
 * ModulController
 *
 * @package      Application
 * @subpackage   Controller
 *
 * @method \Cms\Business\Modul getBusiness
 *
 * @SWG\Resource(
 *     apiVersion="0.1",
 *     resourcePath="/module",
 *     basePath="/cms/service")
*/
class ModulController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Modul');
    parent::init();
  }

  /**
   * @SWG\Api(
   *  path="/modul/getall",
   *  @SWG\Operation(
   *    method="POST",
   *    summary="Returns modules by website ID",
   *    notes="Returns all modules given by website ID.",
   *    nickname="getAll",
   *    type="ResponseData/Module/GetAll",
   *    @SWG\Parameter(name="params", type="Request/Module/GetAll",
   *      required=true, paramType="query")
   * )))
   * @SWG\Model(
   *  id="ResponseData/Module/GetAll",
   *  @SWG\Property(name="data", type="Response/Module/GetAll", required=true)
   * )
   */
  public function getallAction()
  {
    /** @var $validatedRequest \Cms\Request\Modul\GetAll */
    $validatedRequest = $this->getValidatedRequest('Modul', 'GetAll');

    $check = array(
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('getAll', $check);
    
    $moduls = $this->getBusiness()->getAll(
        $validatedRequest->getWebsiteId()
    );
    $this->responseData->setData(new Response\GetAll($moduls));
  }

  public function lockAction()
  {
    /** @var $validatedRequest \Cms\Request\Modul\Lock */
    $validatedRequest = $this->getValidatedRequest('Modul', 'Lock');

    // Darf das Modul bearbeitet werden?
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
        LockBusiness::LOCK_TYPE_MODULE,
        $validatedRequest->getOverride()
    );

    $this->pushDataToErrorController(true);
    $this->responseData->setData(new Response\Lock($lockData));
  }
}
