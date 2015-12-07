<?php
use Cms\Controller as Controller;
use Cms\Response\Log as Response;
use Seitenbau\Registry as Registry;

/**
 * LogController
 *
 * @package      Application
 * @subpackage   Controller
 */
class LogController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('ActionLog');
    parent::init();
  }
  
  public function getAction()
  {
    $validatedRequest = $this->getValidatedRequest('Log', 'Get');

    $check = array(
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('get', $check);
    
    $log = $this->getBusiness()->getLog(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getLimit(),
        $validatedRequest->getFormat()
    );
    
    $logEntryLifetime = Registry::getConfig()->action->logging->db->lifetime;
    
    if ($logEntryLifetime > 0) {
      if ($logEntryLifetime > 1) {
        $timeExpression = sprintf('-%d days', $logEntryLifetime);
        } else {
        $timeExpression = sprintf('-%d day', $logEntryLifetime);
        }
      
        $lifetimeBoundary = strtotime($timeExpression);
      
        $this->getBusiness()->deleteLogEntriesBelowLifetimeBoundary(
            $validatedRequest->getWebsiteId(),
            $lifetimeBoundary
        );
    }
    
    $this->responseData->setData($log);
  }
}
