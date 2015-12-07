<?php
namespace Cms\Dao\Export;

use Cms\Dao\Export as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Cms\Dao\Factory as DaoFactory;

/**
 * Doctrine Dao für den Export
 *
 * @package      Cms
 * @subpackage   Dao
 */
class Doctrine extends DoctrineBase implements Dao
{
  /**
   * @param  string $websiteId
   * @return array  Orm\Entity\Media
   */
  public function getMediaByWebsiteId($websiteId)
  {
    $mediaDao = DaoFactory::get('Media');
    return $mediaDao->getByWebsiteIdAndFilter($websiteId);
  }
  /**
   * @param  string $websiteId
   * @return array  Orm\Entity\Template
   */
  public function getTemplatesByWebsiteId($websiteId)
  {
    $templateDao = DaoFactory::get('Template');
    return $templateDao->getAll($websiteId);
  }
  /**
   * @param string $websiteId
   * @param array  $templateIds
   * @return array  Orm\Entity\Template
   */
  public function getTemplatesByWebsiteIdAndTemplateIds(
      $websiteId,
      array $templateIds
  ) {
    $templateDao = DaoFactory::get('Template');
    $templates = array();
    foreach ($templateIds as $templateId) {
      $templates[] = $templateDao->getById($templateId, $websiteId);
    }
    return $templates;
  }
}
