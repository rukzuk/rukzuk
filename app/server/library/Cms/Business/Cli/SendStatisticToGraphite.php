<?php

namespace Cms\Business\Cli;

use Seitenbau\Registry;
use Seitenbau\Logger\GraphiteStats;

class SendStatisticToGraphite
{

  public function __construct($actionLogBusiness, $websiteBusiness)
  {
    $this->actionLogBusniess = $actionLogBusiness;
    $this->websiteBusiness = $websiteBusiness;
  }

  public function send()
  {

    // Config
    $config = Registry::getConfig();
    if (!$config->stats->graphite->enabled) {
      return;
    }
    $graphite_cfg = $config->stats->graphite->toArray();

    $graphiteStats = new GraphiteStats($graphite_cfg, Registry::getBaseUrl());

    $actionLog = $this->actionLogBusiness;
    $allWebsites = $this->websiteBusiness->getAll();

    // add some non ActionLog related metrics (gauges)
    $graphiteStats->addMetric('WEBSITE_COUNT', count($allWebsites));

    // website specific data
    foreach ($allWebsites as $website) {
      $log = $actionLog->getLog($website->getId(), null, null);
      foreach ($log as $logEntry) {
        $graphiteStats->addMetricByActionLogEntry($logEntry);
      }
    }

    // non-website specific data
    $globalLog = $actionLog->getLog('unknown-websiteid', null, null);

    foreach ($globalLog as $logEntry) {
      $graphiteStats->addMetricByActionLogEntry($logEntry);
    }

    $result = $graphiteStats->sendAll();

    // reflect result in data of "response"
    if (!$result) {
      throw new \Exception('Failed to send graphiteStats');
    }

  }
}
