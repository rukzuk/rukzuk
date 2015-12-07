<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Export Datenabfrage
 *
 * @package      Cms
 * @subpackage   Dao
 */
interface Export
{
  /**
   * @param string $websiteId
   * @param array  $templateIds
   */
  public function getTemplatesByWebsiteIdAndTemplateIds($websiteId, array $templateIds);
  /**
   * @param string $websiteId
   */
  public function getTemplatesByWebsiteId($websiteId);
  /**
   * @param string $websiteId
   */
  public function getMediaByWebsiteId($websiteId);
}
