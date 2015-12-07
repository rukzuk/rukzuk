<?php
namespace Cms\Service;

use Cms\Exception as CmsException;
use Cms\Service\Base\Dao as DaoServiceBase;

/**
 * Log
 *
 * @package      Cms
 * @subpackage   Service
 */
class ActionLog extends DaoServiceBase
{
  /**
   * @param  string  $websiteId
   * @param  integer $limit
   * @return array[] \Cms\Data\ActionLog
   */
  public function getActionLogEntries($websiteId, $limit)
  {
    return $this->execute('getByWebsiteId', array($websiteId, $limit));
  }


  /**
   * Get log with some filter magic
   * @param string $startAfterAction - fetch only log entries newer then the last entry of this given action
   * @param int $limit - max results
   * @return mixed
   * @throws \Cms\Exception
   */
  public function getLogSinceLastAction($startAfterAction, $limit = null)
  {
    return $this->execute('getLogSinceLastAction', array($startAfterAction, $limit));
  }


  /**
   * @param  string  $websiteId
   * @param  integer $timestamp
   */
  public function deleteLogEntriesBelowLifetimeBoundary($websiteId, $timestamp)
  {
    $this->execute('deleteLogEntriesBelowLifetimeBoundary', array($websiteId, $timestamp));
  }
}
