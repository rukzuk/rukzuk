<?php
use Cms\Controller as Controller;
use Cms\Response\Lock as Response;
use Cms\Business\Lock as LockBusiness;
use Cms\Exception as CmsException;
use Seitenbau\Registry as Registry;

/**
 * Lock Controller
 *
 * @package      Application
 * @subpackage   Controller
 */

class LockController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Lock');
    parent::init();
  }

  public function getallAction()
  {
    $validatedRequest = $this->getValidatedRequest('Lock', 'GetAll');
    $locks = $this->getBusiness()->getAll(
        $validatedRequest->getRunId(),
        $validatedRequest->getWebsiteId()
    );
    $this->responseData->setData(new Response\GetAll($locks));
  }

  public function lockAction()
  {
    $validatedRequest = $this->getValidatedRequest('Lock', 'Lock');

    // Auf eine andere Controller/Action weiterleiten (Grund: Rechtepruefung)
    switch($validatedRequest->getType())
    {
      // Page-Lock durchfueren
      case LockBusiness::LOCK_TYPE_PAGE:
            return $this->_forward('lock', 'page');
        break;
 
      // Template-Lock durchfueren
      case LockBusiness::LOCK_TYPE_TEMPLATE:
            return $this->_forward('lock', 'template');
        break;

      // Module-Lock durchfueren
      case LockBusiness::LOCK_TYPE_MODULE:
            return $this->_forward('lock', 'modul');
        break;

      // Webstie-Lock durchfueren
      case LockBusiness::LOCK_TYPE_WEBSITE:
            return $this->_forward('lock', 'website');
        break;

      // Fehler -> Typ nicht vorhanden
      default:
          // Type existiert nicht
            throw new CmsException(1501, __METHOD__, __LINE__);
        break;
    }
  }

  public function unlockAction()
  {
    $validatedRequest = $this->getValidatedRequest('Lock', 'Unlock');

    // Alle Unlocks durchfuehren
    $runId = $validatedRequest->getRunId();
    $items = $validatedRequest->getItems();
    foreach ($items as $nextUnlock) {
      if (is_object($nextUnlock)) {
        $nextUnlock = get_object_vars($nextUnlock);
      }

      $lockData = $this->getBusiness()->unlock(
          $validatedRequest->getRunId(),
          (isset($nextUnlock['id'])         ? $nextUnlock['id']         : null),
          (isset($nextUnlock['websiteId'])  ? $nextUnlock['websiteId']  : null),
          (isset($nextUnlock['type'])       ? $nextUnlock['type']       : null)
      );
    }
  }
}
