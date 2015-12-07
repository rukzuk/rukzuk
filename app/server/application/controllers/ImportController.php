<?php
use Cms\Controller as Controller;
use Cms\Business\Import as ImportBusiness;
use Cms\Exception as CmsException;
use Seitenbau\Registry as Registry;
use Cms\Response\Import as ImportResponse;
use Cms\Response\Import\Conflict as ImportConflictResponse;
use Cms\Service\Import\ConflictException;

/**
 * ImportController
 *
 * @package      Application
 * @subpackage   Controller
 *
 * @method \Cms\Business\Import getBusiness
 */

class ImportController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Import');
    parent::init();
  }

  public function overwriteAction()
  {
    $validatedRequest = $this->getValidatedRequest('Import', 'Overwrite');
    $importData = $this->getBusiness()->overwriteImport(
        $validatedRequest->getTemplates(),
        $validatedRequest->getModules(),
        $validatedRequest->getTemplateSnippets(),
        $validatedRequest->getMedia(),
        $validatedRequest->getImportId()
    );
    $this->responseData->setData(new ImportResponse($importData));

    // log overwrite
    $this->logImport(ImportBusiness::IMPORT_OVERWRITE, $importData, array(
      'importId' => $validatedRequest->getImportId()
    ));
  }
  
  public function cancelAction()
  {
    $validatedRequest = $this->getValidatedRequest('Import', 'Cancel');
    $this->getBusiness()->cancelImport(
        $validatedRequest->getImportId()
    );

    // log cancel
    Registry::getActionLogger()->logAction(ImportBusiness::IMPORT_CANCEL, array(
      'importId' => $validatedRequest->getImportId()
    ));
  }
  
  public function fileAction()
  {
    $validatedRequest = $this->getValidatedRequest('Import', 'File');
    $this->setContentTypeValue('text/plain');
    
    // WebsiteId uebergeben?
    $websiteId = $validatedRequest->getWebsiteId();
    if ($websiteId === Cms\Request\Import\File::DEFAULT_EMPTY_WEBSITE_ID) {
      $websiteId = null;
    }

    $uploadFilename = $validatedRequest->getUploadFilename();
    $allowedType = $validatedRequest->getAllowedType();
    $logInfo = array(
      'file' => $uploadFilename,
      'websiteId' => $websiteId,
      'type' => $allowedType,
    );


    try {
      $importData = $this->getBusiness()->importUploadFile(
          $uploadFilename,
          $websiteId,
          $allowedType
      );
      $this->responseData->setData(new ImportResponse($importData));
      $this->logImport(ImportBusiness::IMPORT_FROM_FILE, $importData, $logInfo);
    } catch (ConflictException $e) {
      $conflictData = $e->getData();
      $this->handleImportConflicts($conflictData);
      $this->logImport(ImportBusiness::IMPORT_FROM_FILE_CONFLICT, $conflictData, $logInfo);
    } catch (\Exception $e) {
      // Error-Response must have content type 'text/plain'
      $error = new Cms\Response\Error;
      $error->setCode($e->getCode());
      $error->setText($e->getMessage());
      $this->responseData->addError($error);
    }
  }
  
  public function urlAction()
  {
    $validatedRequest = $this->getValidatedRequest('Import', 'Url');
    
    // WebsiteId uebergeben?
    $websiteId = $validatedRequest->getWebsiteId();
    if ($websiteId === Cms\Request\Import\File::DEFAULT_EMPTY_WEBSITE_ID) {
      $websiteId = null;
    }

    $importUrl = $validatedRequest->getUploadFilename();
    $allowedType = $validatedRequest->getAllowedType();
    $logInfo = array(
      'url' => $importUrl,
      'websiteId' => $websiteId,
      'type' => $allowedType,
    );

    try {
      $importData = $this->getBusiness()->importUrl(
          $importUrl,
          $websiteId,
          $allowedType
      );
      $this->responseData->setData(new ImportResponse($importData));
      $this->logImport(ImportBusiness::IMPORT_FROM_URL, $importData, $logInfo);
    } catch (ConflictException $e) {
      $conflictData = $e->getData();
      $this->handleImportConflicts($conflictData);
      $this->logImport(ImportBusiness::IMPORT_FROM_URL_CONFLICT, $conflictData, $logInfo);
    }
  }

  public function localAction()
  {
    /** @var \Cms\Request\Import\LocalFiles $validatedRequest */
    $validatedRequest = $this->getValidatedRequest('Import', 'LocalFiles');

    $localId = $validatedRequest->getProperty('localid');
    if ($validatedRequest->hasProperty('websiteid')) {
      $websiteId = $validatedRequest->getProperty('websiteid');
    } else {
      $websiteId = null;
    }
    if ($validatedRequest->hasProperty('allowedtype')) {
      $allowedType = $validatedRequest->getProperty('allowedtype');
    } else {
      $allowedType = null;
    }
    if ($validatedRequest->hasProperty('websitename')) {
      $websiteName = $validatedRequest->getProperty('websitename');
    } else {
      $websiteName = null;
    }
    $logInfo = array(
      'localId' => $localId,
      'websiteId' => $websiteId,
      'type' => $allowedType,
    );

    try {
      $importData = $this->getBusiness()->importLocalFiles(
          $localId,
          $websiteId,
          $allowedType,
          $websiteName
      );
      $this->responseData->setData(new ImportResponse($importData));
      $this->logImport(ImportBusiness::IMPORT_FROM_LOCAL_FILE, $importData, $logInfo);
    } catch (ConflictException $e) {
      $conflictData = $e->getData();
      $this->handleImportConflicts($conflictData);
      $this->logImport(ImportBusiness::IMPORT_FROM_LOCAL_FILE_CONFLICT, $conflictData, $logInfo);
    }
  }

  /**
   * @param array $conflictData
   */
  protected function handleImportConflicts($conflictData)
  {
    $error = new Cms\Response\Error;
    $error->setCode(11);
    $error->setText(\Cms\Error::getMessageByCode(11));
    $this->responseData->addError($error);
    $this->responseData->setData(new ImportConflictResponse($conflictData));
  }
  
  protected function logImport($actionName, array $importData, $info)
  {
    if (isset($importData['importId'])) {
      $info['importId'] = $importData['importId'];
    }
    if (isset($importData['websiteId'])) {
      $info['websiteId'] = $importData['websiteId'];
    }
    if (isset($importData['conflict']) && is_array($importData['conflict'])) {
      $info['conflict'] = array();
      if (isset($importData['conflict']['modules'])) {
        $info['conflict']['modules'] = count($importData['conflict']['modules']);
      }
      if (isset($importData['conflict']['templates'])) {
        $info['conflict']['templates'] = count($importData['conflict']['templates']);
      }
      if (isset($importData['conflict']['templatesnippets'])) {
        $info['conflict']['templatesnippets'] = count($importData['conflict']['templatesnippets']);
      }
      if (isset($importData['conflict']['media'])) {
        $info['conflict']['media'] = count($importData['conflict']['media']);
      }
    }
    $info['imported'] = array();
    if (isset($importData['modules'])) {
      $info['imported']['modules'] = count($importData['modules']);
    }
    if (isset($importData['templates'])) {
      $info['imported']['templates'] = count($importData['templates']);
    }
    if (isset($importData['templatesnippets'])) {
      $info['imported']['templatesnippets'] = count($importData['templatesnippets']);
    }
    if (isset($importData['pages'])) {
      $info['imported']['pages'] = count($importData['pages']);
    }
    if (isset($importData['media'])) {
      $info['imported']['media'] = count($importData['media']);
    }
    if (isset($importData['usergroups'])) {
      $info['imported']['usergroups'] = count($importData['usergroups']);
    }
    if (isset($importData['website'])) {
      $info['imported']['website'] = count($importData['website']);
    }
    
    Registry::getActionLogger()->logAction($actionName, $info);
  }
}
