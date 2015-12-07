<?php
use Cms\Controller as Controller;
use Cms\Response\Heartbeat as Response;
use Seitenbau\Registry as Registry;

/**
 * Lock Controller
 *
 * @package      Application
 * @subpackage   Controller
 */

class HeartbeatController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Heartbeat');
    parent::init();
  }

  public function pollAction()
  {
    $validatedRequest = $this->getValidatedRequest('Heartbeat', 'Poll');

    // Alle geoeffneten Entitaeten pruefen
    $data       = array();
    $runId      = $validatedRequest->getRunId();
    $openItems  = $validatedRequest->getOpenItems();
    foreach ($openItems as $websiteId => $nextOpenItems) {
      $websiteData = $this->getBusiness()->checkOpenItems(
          $runId,
          $websiteId,
          $nextOpenItems
      );
      
      // Abgelaufene Locks aufnehmen
      if (isset($websiteData['expired']) && is_array($websiteData['expired'])
          && count($websiteData['expired']) > 0) {
        $data['expired'][$websiteId] = $websiteData['expired'];
      }

      // Nicht vorhandene Locks aufnehmen
      if (isset($websiteData['invalid']) && is_array($websiteData['invalid'])
          && count($websiteData['invalid']) > 0) {
        $data['invalid'][$websiteId] = $websiteData['invalid'];
      }
    }
    $this->responseData->setData(new Response\Poll($data));
  }
}
