<?php
namespace Seitenbau\Logger;

use Seitenbau\Registry as Registry;
use \Cms\Access\Manager as AccessManager;

/**
 * Action Logger
 *
 * @package      Seitenbau
 * @subpackage   Logger
 */
class Action
{
  /**
   * @var Zend_Log
   */
  private $logger;
  /**
   * @var integer
   */
  private $level = 0;
  
  /**
   * @param \Zend_Log $logger
   */
  public function __construct(\Zend_Log $logger)
  {
    $this->logger = $logger;
  }
  
  /**
   * @param string  $websiteId
   * @param string  $id
   * @param string  $name
   * @param string  $additionalinfo
   * @param integer $timestamp
   * @param string  $userid
   * @param string  $action
   * @param integer $priority
   */
  protected function log(
      $websiteId,
      $id,
      $name,
      $additionalinfo,
      $timestamp,
      $userlogin,
      $action,
      $priority = \Zend_Log::INFO
  ) {
    if ((int) $priority > (int) $this->level) {
      return;
    }
    $this->logger->setEventItem('websiteid', $websiteId);
    $this->logger->setEventItem('id', $id);
    $this->logger->setEventItem('name', $name);
    $this->logger->setEventItem('additionalinfo', $additionalinfo);
    $this->logger->setEventItem('timestamp', $timestamp);
    $this->logger->setEventItem('userlogin', $userlogin);
    $this->logger->setEventItem('action', $action);
    $this->logger->log($action, $priority);
  }
  /**
   * Setzt das Logging-Level
   *
   * @param integer $level Logging Level
   */
  public function setLevel($level)
  {
    $this->level = $level;
  }
  /**
   * Liefert das Logging-Level zurueck
   *
   * @return integer
   */
  public function getLevel()
  {
    return $this->level;
  }
  /**
   * @param string $action
   * @param array  $params
   */
  public function logAction($action, $params)
  {
    $additionalinfo = $params;
    if (isset($params['websiteId'])) {
      $websiteId = $params['websiteId'];
      unset($additionalinfo['websiteId']);
    } else {
      $websiteId = 'unknown-websiteid';
    }
    if (isset($params['id'])) {
      $id = $params['id'];
      unset($additionalinfo['id']);
    } else {
      $id = 'unknown-id';
    }
    if (isset($params['name'])) {
      $name = $params['name'];
      unset($additionalinfo['name']);
    } else {
      $name = 'unknown-name';
    }
    
    
    if (count($additionalinfo) > 0) {
      $additionalinfoAsJson = \Zend_Json::encode($additionalinfo);
    } else {
      $additionalinfoAsJson = null;
    }
    
    $this->log(
        $websiteId,
        $id,
        $name,
        $additionalinfoAsJson,
        $this->getCurrentTimestamp(),
        $this->getUserloginFromSession(),
        $action
    );
  }
  /**
   * @return string
   */
  private function getUserloginFromSession()
  {
    $accessManager = AccessManager::singleton();
    $identity = $accessManager->getIdentityAsArray();
    return (isset($identity['email']) ? $identity['email'] : 'unknown-userlogin');
  }
  /**
   * @return integer
   */
  protected function getCurrentTimestamp()
  {
    return time();
  }
}
