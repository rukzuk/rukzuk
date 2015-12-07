<?php
use Cms\Controller as Controller;
use Cms\Business\Export as ExportService;
use Cms\Response\Export as Response;

/**
 * ExportController
 *
 * @package      Application
 * @subpackage   Controller
 */
class ExportController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Export');
    parent::init();
  }

  public function modulesAction()
  {
    $validatedRequest = $this->getValidatedRequest('Export', 'Module');
    $exportCdnUri = $this->getBusiness()->export(
        ExportService::EXPORT_MODE_MODULE,
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getIds(),
        $validatedRequest->getExportName()
    );
    
    $this->responseData->setData(new Response($exportCdnUri));
  }
  public function templatesnippetsAction()
  {
    $validatedRequest = $this->getValidatedRequest('Export', 'TemplateSnippets');
    $exportCdnUri = $this->getBusiness()->export(
        ExportService::EXPORT_MODE_TEMPLATESNIPPET,
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getIds(),
        $validatedRequest->getExportName()
    );

    $this->responseData->setData(new Response($exportCdnUri));
  }
  public function websiteAction()
  {
    $validatedRequest = $this->getValidatedRequest('Export', 'Website');

    /** @var $exportBusiness \Cms\Business\Export */
    $exportBusiness = $this->getBusiness();
    $exportResult = $exportBusiness->exportWebsite(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getExportName(),
        $validatedRequest->getComplete()
    );
    $exportCdnUri = $exportBusiness->buildExportCdnUri(
        $exportResult['file'],
        $exportResult['name']
    );

    $this->responseData->setData(new Response($exportCdnUri));
  }
}
