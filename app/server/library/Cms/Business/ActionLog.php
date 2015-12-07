<?php
namespace Cms\Business;

use Seitenbau\Registry;
use Cms\Exception as CmsException;

/**
 * Log
 *
 * @package      Cms
 * @subpackage   Business
 */
class ActionLog extends Base\Service
{
  const TEXT_DELIMITER = '|';
  
  /**
   * @param string  $websiteId
   * @param integer $timestamp
   */
  public function deleteLogEntriesBelowLifetimeBoundary($websiteId, $timestamp)
  {
    $this->getService()->deleteLogEntriesBelowLifetimeBoundary(
        $websiteId,
        $timestamp
    );
  }
  /**
   * @param  string $websiteId
   * @param  string $limit
   * @param  string $format
   * @return array
   */
  public function getLog($websiteId, $limit, $format)
  {
    $actionLogEntries = $this->getService()->getActionLogEntries($websiteId, $limit);

    if (is_null($format)) {
      return $actionLogEntries;
    }

    $transformerMethod = sprintf(
        'transformInto%s',
        ucfirst(strtolower($format))
    );
    
    return $this->$transformerMethod($actionLogEntries);

  }
  /**
   * @param  array $actionLogEntries
   * @return array
   */
  private function transformIntoJson($actionLogEntries)
  {
    $log = array();
    
    if (is_array($actionLogEntries) && count($actionLogEntries) > 0) {
      foreach ($actionLogEntries as $actionLogEntry) {
        $logColumns = array(
          'id' => $actionLogEntry->getId(),
          'name' => $actionLogEntry->getName(),
          'dateTime' => $this->convertTimestampToDatetime($actionLogEntry->getTimestamp()),
          'userlogin' => $actionLogEntry->getUserlogin(),
          'action' => $actionLogEntry->getAction(),
          'additionalinfo' => $actionLogEntry->getAdditionalinfo(),
        );
        $log[] = $logColumns;
      }
    }
    
    return $log;
  }
  /**
   * @param  array $actionLogEntries
   * @return array
   */
  private function transformIntoTxt($actionLogEntries)
  {
    $log = array();
    
    if (is_array($actionLogEntries) && count($actionLogEntries) > 0) {
      foreach ($actionLogEntries as $actionLogEntry) {
        $logColumns = array(
          $actionLogEntry->getId(),
          $actionLogEntry->getName(),
          $this->convertTimestampToDatetime($actionLogEntry->getTimestamp()),
          $actionLogEntry->getUserlogin(),
          $actionLogEntry->getAction()
        );
        $log[] = implode(self::TEXT_DELIMITER, $logColumns);
      }
    }
    
    return $log;
  }
  /**
   * @param  string $timestamp
   * @param  string $format
   * @return string
   */
  private function convertTimestampToDatetime($timestamp, $format = 'd.m.Y H:i:s')
  {
    return date($format, $timestamp);
  }

  /**
   * Pruefung, ob der angemeldete User die Rechte fuer die aufgerufene Aktion
   * besitzt
   *
   * @param array  $identity
   * @param string $rightname Name des Rechts, auf das geprueft wird
   * @param mixed  $check
   * @return boolean
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // Superuser darf alles
    if ($this->isSuperuser($identity)) {
      return true;
    }
    
    switch ($rightname) {
      case 'get':
        if ($this->checkWebsitePrivilegeForIdentity($identity, $check['websiteId'], 'readlog', 'all')) {
          return true;
        }
            break;
    }
    
    // Default: Keine Rechte
    return false;
  }
}
