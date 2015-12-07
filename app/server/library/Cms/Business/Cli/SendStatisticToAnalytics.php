<?php

namespace Cms\Business\Cli;

use Seitenbau\Registry;
use \Cms\Data\PublisherStatus as PublisherStatus;
use Seitenbau\Logger\SegmentIoStats;
use Cms\Business\Cli as CliBusiness;

class SendStatisticToAnalytics
{

  /**
   * @var \Cms\Business\User
   */
  private $userBusiness;
  /**
   * @var \Cms\Business\ActionLog
   */
  private $actionLogBusiness;
  /**
   * @var \Cms\Business\Website
   */
  private $websiteBusiness;
  /**
   * @var \Cms\Business\Builder
   */
  private $builderBusiness;
  /**
   * @var \Cms\Business\Template
   */
  private $templateBusiness;

  /**
   * @param \Cms\Business\User      $userBusiness
   * @param \Cms\Business\ActionLog $actionLogBusiness
   * @param \Cms\Business\Website   $websiteBusiness
   * @param \Cms\Business\Builder   $builderBusiness
   * @param \Cms\Business\Template  $templateBusiness
   */
  public function __construct(
      $userBusiness,
      $actionLogBusiness,
      $websiteBusiness,
      $builderBusiness,
      $templateBusiness
  ) {
    $this->userBusiness = $userBusiness;
    $this->actionLogBusiness = $actionLogBusiness;
    $this->websiteBusiness = $websiteBusiness;
    $this->builderBusiness = $builderBusiness;
    $this->templateBusiness = $templateBusiness;
  }

  public function send()
  {
    // Config
    $config = Registry::getConfig();
    if (!$config->stats->segmentio->enabled) {
      return;
    }
    $stats_cfg = $config->stats->segmentio->toArray();
    $sio = new SegmentIoStats($stats_cfg, Registry::getBaseUrl());

    $tracking_id = $this->getTrackingId();
    if (empty($tracking_id)) {
      return; // end here
    }

    $propertiesReport = array();
    $propertiesReport['calculated_stats'] = $this->addCalculatedStats($sio, $tracking_id);

    // initial user data or new log entries
    if ($this->isFirstSubmit()) {
      // get all log entries
      $logEntries = $this->actionLogBusiness->getLogSinceLastAction(null);
      // remove legacy action entries
      $this->removeActionsWithLegacyName($logEntries);
    } else {
      // get new log entries (since last successful send operation)
      $logEntries = $this->actionLogBusiness->getLogSinceLastAction(CliBusiness::SPACE_SUBMITTED_LOG);
    }

    // add log entries to queue
    $this->addLogEntries($sio, $tracking_id, $logEntries);

    // send to segment.io and log the sending if successful
    if ($sio->sendAll()) {
      $this->logSuccessfulSent($propertiesReport);
    }
  }

  /**
   * @return string|null
   */
  protected function getTrackingId()
  {
    $cfg = Registry::getConfig();
    if (!isset($cfg->owner) || !isset($cfg->owner->trackingId)) {
      return null;
    }
    if (!is_string($cfg->owner->trackingId)) {
      return null;
    }
    return $cfg->owner->trackingId;
  }

  /**
   * @param \Seitenbau\Logger\SegmentIoStats $sio
   * @param                                  $tracking_id
   *
   * @return array
   * @throws \Exception
   */
  protected function addCalculatedStats($sio, $tracking_id)
  {
    $allUsers = $this->userBusiness->getAll();
    $allWebsites = $this->websiteBusiness->getAll();
    $numOfWebsites = count($allWebsites);
    list($numPublishData, $numWebsitesOnceSuccessfullyPublished, $publishedWebsitesUrl)
      = $this->collectPublishStats($allWebsites);
    $usedModuleIds = $this->getUsedModuleIds($allWebsites);

    $calculated_stats = array(
      'diskUsage' => round(DiskUsageHelper::getDiskUsage() / 1024, 2),
      'usedWebsites' => $numOfWebsites,
      'publishingEnabledWebsites' => $numPublishData,
      'publishedWebsites' => $numWebsitesOnceSuccessfullyPublished,
      'publishedWebsitesInternalUrl' => $publishedWebsitesUrl['internal'],
      'publishedWebsitesExternalUrl' => $publishedWebsitesUrl['external'],
      'totalUsers' => count($allUsers),
      'usedModuleIds' => $usedModuleIds,
    );

    $sio->addProperties($tracking_id, $calculated_stats);

    return $calculated_stats;
  }

  /**
   * @param \Cms\Data\Website[] $allWebsites
   *
   * @return array
   */
  protected function collectPublishStats($allWebsites)
  {
    $numPublishData = 0;
    $numWebsitesOnceSuccessfullyPublished = 0;
    $publishedWebsitesUrl = array('internal' => array(), 'external' => array());
    foreach ($allWebsites as $ws) {
      if (!$ws->getPublishingEnabled()) {
        continue;
      }
      // count
      $numPublishData++;

      // check build status of websites
      $isPublished = false;
      $allWebsiteBuilds = $this->builderBusiness->getWebsiteBuilds($ws->getId());
      foreach ($allWebsiteBuilds as $build) {
        if ($build->getLastPublished()->getStatus() == PublisherStatus::STATUS_FINISHED) {
          $isPublished = true;
          break;
        }
      }

      if ($isPublished) {
        $numWebsitesOnceSuccessfullyPublished++;

        // add urls
        $publishData = json_decode($ws->getPublish(), true);
        if (is_array($publishData) && isset($publishData['type'])) {
          if ($publishData['type'] === 'internal') {
            $internalUrl = $this->getPublishedInternalUrl($publishData);
            if (!empty($internalUrl)) {
              $publishedWebsitesUrl['internal'][] = $internalUrl;
            }
          } else {
            $externalUrl = $this->getPublishedExternalUrl($publishData);
            if (!empty($externalUrl)) {
              $publishedWebsitesUrl['external'][] = $externalUrl;
            }
          }
        }
      }
    }
    return array($numPublishData, $numWebsitesOnceSuccessfullyPublished, $publishedWebsitesUrl);
  }

  /**
   * @param array $publishData
   * @return string|null
   */
  protected function getPublishedInternalUrl($publishData)
  {
    if (isset($publishData['cname']) && !empty($publishData['cname'])) {
      return "http://".$publishData['cname'];
    }
    return null;
  }

  /**
   * @param $publishData
   * @return string|null
   */
  protected function getPublishedExternalUrl($publishData)
  {
    if (isset($publishData['url']) && !empty($publishData['url'])) {
      return $publishData['url'];
    }
    if (isset($publishData['host']) && !empty($publishData['host'])) {
      return $publishData['host'];
    }
    return null;
  }

  /**
   * @param \Cms\Data\Website[] $websites
   *
   * @return array
   */
  protected function getUsedModuleIds($websites)
  {
    $usedModuleIds = array();
    $templateBusiness = $this->getTemplateBusiness();
    foreach ($websites as $ws) {
      $templateIds = $this->getTemplateIdsByWebsiteId($templateBusiness, $ws->getId());
      foreach ($templateIds as $templateId) {
        try {
          $moduleIds = $templateBusiness->getUsedModuleIds($ws->getId(), $templateId);
          $usedModuleIds = array_unique(array_merge($usedModuleIds, $moduleIds));
        } catch (\Exception $doNothing) {
        }
      }
    }
    sort($usedModuleIds);
    return $usedModuleIds;
  }

  /**
   * @param \Cms\Business\Template $templateBusiness
   * @param string $websiteId
   *
   * @return array
   */
  protected function getTemplateIdsByWebsiteId($templateBusiness, $websiteId)
  {
    try {
      return $templateBusiness->getIdsByWebsiteId($websiteId);
    } catch (\Exception $doNothing) {
    }
    return array();
  }

  /**
   * @return bool
   * @throws \Exception
   */
  protected function isFirstSubmit()
  {
    try {
      $this->actionLogBusiness->getLogSinceLastAction(CliBusiness::SPACE_SUBMITTED_LOG);
      return false;
    } catch (\Exception $e) {
      // action SPACE_SUBMITTED_LOG not found: this is the first submit of stats
      if ($e->getCode() == 1203) {
        return true;
      } else {
        throw $e;
      }
    }
  }

  /**
   * @param array $submitted_additional_info
   */
  protected function logSuccessfulSent($submitted_additional_info)
  {
    Registry::getActionLogger()->logAction(CliBusiness::SPACE_SUBMITTED_LOG, $submitted_additional_info);
  }

  /**
   * @param \Seitenbau\Logger\SegmentIoStats $sio
   * @param string                           $tracking_id
   * @param \Cms\Data\ActionLog[]            $logEntries
   */
  protected function addLogEntries($sio, $tracking_id, $logEntries)
  {
    foreach ($logEntries as $logEntry) {
      $sio->addEventByActionLogEntry($tracking_id, $logEntry);
    }
  }

  /**
   * Removes actions without a _ (underscore) in the name from the given array
   *
   * @param \Cms\Data\ActionLog[] &$logEntries
   */
  protected function removeActionsWithLegacyName(array &$logEntries)
  {
    foreach ($logEntries as $logIdx => $logEntry) {
      if (!preg_match('/_/', $logEntry->getAction())) {
        unset($logEntries[$logIdx]);
      }
    }
  }

  /**
   * @return \Cms\Business\Template
   */
  protected function getTemplateBusiness()
  {
    return $this->templateBusiness;
  }
}
