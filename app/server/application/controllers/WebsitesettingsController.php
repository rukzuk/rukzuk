<?php


use Cms\Controller as Controller;
use Cms\ExceptionStack as CmsExceptionStack;
use Cms\Response\WebsiteSettings as Response;

/**
 * @package      Application/Controller
 *
 * @method \Cms\Business\WebsiteSettings getBusiness
 *
 * @SWG\Resource(
 *     apiVersion="0.1",
 *     swaggerVersion="2.0",
 *     resourcePath="/websitesettings",
 *     basePath="/cms/service")
 */
class WebsitesettingsController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('WebsiteSettings');
    parent::init();
  }

  /**
   * @SWG\Api(
   *   path="/websitesettings/getall",
   *   @SWG\Operation(
   *        method="GET, POST",
   *        summary="Return all website settings",
   *        notes="Return all website settings for the website with the given id.",
   *        type="WebsiteSettingsGetAll",
   *        nickname="getAll")
   * ))
   */
  public function getallAction()
  {
    /** @var $validatedRequest \Cms\Request\WebsiteSettings\GetAll */
    $validatedRequest = $this->getValidatedRequest('WebsiteSettings', 'GetAll');

    $this->getBusiness()->checkUserRights('getAll', array(
      'websiteId' => $validatedRequest->getWebsiteId()
    ));

    $allWebsiteSettings = $this->getBusiness()->getAll(
        $validatedRequest->getWebsiteId()
    );

    $this->responseData->setData(new Response\GetAll($allWebsiteSettings));
  }

  /**
   * @SWG\Api(
   *   path="/websitesettings/editmultiple",
   *   @SWG\Operation(
   *        method="GET, POST",
   *        summary="Updates website settings",
   *        notes="Update website settings section and return the new website settings.",
   *        type="WebsiteSettingsEdit",
   *        nickname="edit")
   * ))
   */
  public function editmultipleAction()
  {
    /** @var $validatedRequest \Cms\Request\WebsiteSettings\EditMultiple */
    $validatedRequest = $this->getValidatedRequest('WebsiteSettings', 'EditMultiple');

    $this->getBusiness()->checkUserRights('edit', array(
      'websiteId' => $validatedRequest->getWebsiteId(),
    ));

    $allNewWebsiteSettings = $validatedRequest->getAllWebsiteSettings();
    foreach ($allNewWebsiteSettings as $id => $websiteSettings) {
      try {
        $attributes = get_object_vars($websiteSettings);
        $this->getBusiness()->update($validatedRequest->getWebsiteId(), $id, $attributes);
      } catch (\Exception $e) {
        CmsExceptionStack::addException($e);
      }
    }

    $allWebsiteSettings = $this->getBusiness()->getAll($validatedRequest->getWebsiteId());
    $responseData = new Response\GetAll($allWebsiteSettings);

    if (CmsExceptionStack::hasErrors()) {
      CmsExceptionStack::throwErrors($responseData);
    }

    $this->responseData->setData($responseData);
  }
}
