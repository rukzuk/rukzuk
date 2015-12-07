<?php
namespace Seitenbau\Logger;

/**
 * Class GraphiteStats
 */
class GraphiteStats
{

  /**
   * @var array(array('value' => string, 'action' => string, 'time' => int))
   */
  private $stats_summary = array();
  /**
   * @var string
   */
  private $webhost = '';
  /**
   * @var array
   */
  private $cfg = array();


  public function __construct($graphite_cfg, $webhost)
  {
    $this->cfg = $graphite_cfg;
    $this->webhost = $webhost;
  }

  /**
   * @return string
   */
  private function buildBucketPrefix()
  {

    // current instance: com.rukzuk.user // net.rukzuk.dev.karl
    $webhost_url = parse_url($this->webhost);
    $space_bucket = implode('.', array_reverse(explode('.', $webhost_url['host'])));

    // build whole prefix for this space
    return $this->cfg['prefix'].'.'.$space_bucket.'.'.$this->cfg['bucket'].'.';
  }

  /**
   * send summary to graphite
   * @return bool - status of operation (TRUE = success, FALSE = failure)
   */
  public function sendAll()
  {

    $bucket_prefix = $this->buildBucketPrefix();
    $address = gethostbyname($this->cfg['host']);

    $cmdBuf = '';
    foreach ($this->stats_summary as $stat) {
      // action name mapping
      $action = preg_replace('/[\. ]/', '', $stat['action']);
      $action = preg_replace('/_ACTION$/', '', $action);

      // HACK to prevent old event names to appear in graphite
      if (!preg_match('/_/', $action)) {
        continue;
      }

      $cmdBuf .= $bucket_prefix.$action.' '.$stat['value'].' '.$stat['time']."\n";
    }

    // var_dump($cmdBuf);

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    $result = socket_connect($socket, $address, $this->cfg['port']);
    if (!$result) {
      return false;
    }

    $write_result = socket_write($socket, $cmdBuf, strlen($cmdBuf));

    if (!$write_result) {
      return false;
    }

    return true;
  }

  /**
   * Helper to convert and add a ActionLog object
   * @param \Cms\Data\ActionLog $logEntry
   */
  public function addMetricByActionLogEntry($logEntry)
  {
    $action = $logEntry->getAction();
    $time = $logEntry->getTimestamp();
    $additional_info = json_decode($logEntry->getAdditionalinfo(), true);

    // default value is a counter
    $value = 1;

    // special values defined in the additionalinfo field
    if (isset($additional_info['metric_value'])) {
      if (isset($additional_info[$additional_info['metric_value']])) {
        $value = $additional_info[$additional_info['metric_value']];
      }
    }

    $this->addMetric($action, $value, $time);
  }

  /**
   * Add a simple metric value
   * @param string $action
   * @param int $value
   * @param int $time
   */
  public function addMetric($action, $value, $time = null)
  {

    if (is_null($time)) {
      $time = time();
    }

    $this->stats_summary[] = array('action' => $action, 'value' => $value, 'time' => $time);
  }
}
