<?php
namespace Seitenbau\Logger;

use Segment;

/**
 * Class SegmentIoStats
 * Send ActionLog entries to segment.io which calls various other APIs
 */
class SegmentIoStats
{

  /**
   * @var array
   */
  protected $events = array();

  /**
   * @var array
   */
  protected $properties = array();
  /**
   * @var string
   */
  protected $spaceHost = '';
  /**
   * @var array
   */
  protected $cfg = array();


  public function __construct($cfg, $webhost)
  {
    $this->cfg = $cfg;
    $weburl = parse_url($webhost);
    $this->spaceHost = preg_replace('/\.$/', '', $weburl['host']);

    // init Analytics lib
    \Segment::init($this->cfg['api_secret'], $this->cfg['api_options']);
  }

  /**
   * send summary
   */
  public function sendAll()
  {

    // send properties of a certain user
    foreach ($this->properties as $user => $prop) {
      if (\Segment::identify(array(
        'userId' => $user,
        'traits' => $prop,
        'context' => array('active' => false, 'ip' => 0)))) {
        // remove submitted property
        unset($this->properties[$user]);
      }
    }

    // track events (of a user)
    foreach ($this->events as $idx => $stat) {
      // ordinary action
      if (\Segment::track(array(
        'userId' => $stat['user'],
        'event' => $stat['action'],
        'properties' => $stat['additional_info'],
        'timestamp' => $stat['time']))) {
        // remove submitted event
        // oh yeah this works in php with foreach (do not try at home with a real language)
        unset($this->events[$idx]);
      }
    }

    return (count($this->events) + count($this->properties)) === 0;
  }

  /**
   * Space identifier (full hostname, eg. myspace.rukzuk.com)
   * @return string
   */
  public function getSpaceHost()
  {
    return $this->spaceHost;
  }

  /**
   * Helper to convert and add an ActionLog object
   * @param string userId - the user to which this should be logged
   * @param \Cms\Data\ActionLog $logEntry
   * @return bool - the log entry was added or not (this can be affected by config blacklists)
   */
  public function addEventByActionLogEntry($userId, $logEntry)
  {
    $action = $logEntry->getAction();

    if (preg_match($this->cfg['action_blacklist_regex'], $action)) {
      return false;
    }

    $readable_action = $this->humanReadableActionName($action);

    $time = $logEntry->getTimestamp();
    $additional_info = json_decode($logEntry->getAdditionalinfo(), true);
    // add the 'special' params again (see Seitenbau\Logger\Action::logAction())
    $additional_info['id'] = $logEntry->getId();
    $additional_info['websiteId'] = $logEntry->getWebsiteid();
    $additional_info['name'] = $logEntry->getName();

    $filtered_info = $this->filterInfo($additional_info, $this->cfg['info_white_list']);

    // static event properties
    if ($logEntry->getUserlogin() !== 'unknown-userlogin') {
      $filtered_info['userLogin'] = $logEntry->getUserlogin();
    }
    $filtered_info['space'] = $this->getSpaceHost();

    $this->addEvent($readable_action, $userId, $filtered_info, $time);

    return true;
  }

  /**
   * Helper function to filter info
   * @param $additional_info
   * @param $white_list
   * @return array
   */
  protected function filterInfo(array &$additional_info, array &$white_list)
  {
    $filtered_info = array();
    if (is_array($additional_info) && is_array($white_list)) {
      foreach ($white_list as $wl) {
        if (isset($additional_info[$wl])) {
          $filtered_info[$wl] = $additional_info[$wl];
        }
      }
    }
    return $filtered_info;
  }

  /**
   * Add an event
   * @param string $action
   * @param string $user (e-mail)
   * @param array $additional_info
   * @param int $time
   */
  public function addEvent($action, $user, $additional_info, $time = null)
  {

    if (is_null($time)) {
      $time = time();
    }

    $this->events[] = array('action' => $action, 'user' => $user, 'additional_info' => $additional_info, 'time' => $time);
  }

  /**
   * Add properties to user
   * @param string $user
   * @param array $properties ('key' => 'value')
   */
  public function addProperties($user, $properties)
  {
    if (!isset($this->properties[$user])) {
      $this->properties[$user] = array();
    }
    $this->properties[$user] = array_merge($this->properties[$user], $properties);
  }

  /**
   * Converts 'MY_FANCY_ACTION' to 'My Fancy'
   * @param string $action
   * @return string
   */
  protected function humanReadableActionName($action)
  {
    // action name mapping (remove tailing _ACTION)
    $action = preg_replace('/_ACTION$/', '', $action);

    // convert to human readable actions
    $action = str_replace('_', ' ', $action);
    $action = ucwords(strtolower($action));

    return $action;
  }
}
