<?php

namespace Cms\Dao;

/**
 * Schnittstelle fuer ActionLog Datenabfragen
 *
 * @package      Cms
 * @subpackage   Dao
 */
interface ActionLog
{
  /**
   * @param  string   $websiteId
   * @param  integer  $limit
   * @return array
   */
  public function getByWebsiteId($websiteId, $limit);
  /**
   * @param  string  $websiteId
   * @param  integer $timestamp
   */
  public function deleteLogEntriesBelowLifetimeBoundary($websiteId, $timestamp);
}
