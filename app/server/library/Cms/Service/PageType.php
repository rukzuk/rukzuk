<?php


namespace Cms\Service;

use Cms\Dao\Base\SourceItem;
use Cms\Dao\PageType\Source as PageTypeSource;
use Cms\Exception;
use Cms\Service\Base\Dao as DaoServiceBase;
use Seitenbau\FileSystem as FS;
use Seitenbau\Registry;
use Seitenbau\Log as SbLog;
use Cms\Data\PageType as DataPageType;

/**
 * @package Cms\Service
 *
 * @method \Cms\Dao\PageType getDao
 */
class PageType extends DaoServiceBase
{
  const PAGE_TYPES_SUBDIRECTORY = 'pageTypes';

  /**
   * returns all page types of the given website
   *
   * @param   string $websiteId
   *
   * @return  \Cms\Data\PageType[]
   */
  public function getAll($websiteId)
  {
    $source = $this->getSource($websiteId);
    return $this->getDao()->getAll($source);
  }

  /**
   * returns the page type of the given website id and page type id
   *
   * @param   string $websiteId
   * @param   string       $id
   *
   * @return DataPageType
   */
  public function getById($websiteId, $id)
  {
    $source = $this->getSource($websiteId);
    return $this->getDao()->getById($source, $id);
  }

  /**
   * @return string|null
   */
  public function getDefaultPageTypeId()
  {
    $defaultSource = $this->getDefaultSource();
    if (is_null($defaultSource)) {
      return null;
    }
    return $defaultSource->getId();
  }

  /**
   * @param string $websiteId
   *
   * @return PageTypeSource
   */
  protected function getSource($websiteId)
  {
    $sources = array();

    $defaultSource = $this->getDefaultSource();
    if (!is_null($defaultSource)) {
      $sources[] = $defaultSource;
    }

    try {
      $packageService = $this->getPackageService();
      $packages = $packageService->getAll($websiteId);
      foreach ($packages as $package) {
        $sources = array_merge($sources, $package->getPageTypesSource());
      }
    } catch (Exception $logOnly) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
    }
    return new PageTypeSource($websiteId, $sources);
  }

  /**
   * @return \Cms\Service\Package
   */
  protected function getPackageService()
  {
    return $this->getService('Package');
  }

  /**
   * @return SourceItem
   */
  protected function getDefaultSource()
  {
    $config = Registry::getConfig();
    if (!isset($config->pageType->defaultPageType)) {
      return null;
    }

    $defaultPageTypeConfig = $config->pageType->defaultPageType;
    $defaultSource = new SourceItem(
        $defaultPageTypeConfig->id,
        $defaultPageTypeConfig->directory,
        $defaultPageTypeConfig->url,
        SourceItem::SOURCE_UNKNOWN,
        true,
        false
    );
    return $defaultSource;
  }
}
