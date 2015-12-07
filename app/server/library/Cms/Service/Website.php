<?php
namespace Cms\Service;

use Cms\Quota;
use Cms\Service\Base\Dao as DaoServiceBase;
use Cms\Exception as CmsException;
use Cms\Data;
use Cms\Dao\Base\SourceItem;
use Cms\Dao\Website\GlobalSetSource;
use Seitenbau\Cache\StaticCache;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Seitenbau\Log as SbLog;

/**
 * Stellt Service-Komponenten fuer Website zur Verfeugung
 *
 * @package      Cms
 * @subpackage   Service
 */

class Website extends DaoServiceBase
{
  const CACHE_PREFIX_REPO = 'repo::';
  const CACHE_PREFIX_USED_SET_ID = 'set::';

  /**
   * @var StaticCache
   */
  protected $cache;

  /**
   * @param string $id
   *
   * @return mixed
   */
  public function decreaseVersion($id)
  {
    return $this->execute('decreaseVersion', array($id));
  }

  /**
   * @param string $id
   *
   * @return mixed
   */
  public function increaseVersion($id)
  {
    return $this->execute('increaseVersion', array($id));
  }

  /**
   * @return \Cms\Data\Website[]
   */
  public function getAll()
  {
    $result = $this->execute('getAll');
    return $result;
  }

  /**
   * get one website object from database
   *
   * @param string $id
   *
   * @return \Cms\Data\Website
   */
  public function getById($id)
  {
    $result = $this->execute('getById', array($id));

    return $result;
  }

  /**
   * update one website in database
   *
   * @param string $id
   * @param array  $attributes
   *
   * @return mixed
   */
  public function update($id, array $attributes)
  {
    $this->checkWebhostingMaxCountQuota($attributes, $id);

    // only enabling publishing at update service
    if (isset($attributes['publishingenabled']) && $attributes['publishingenabled'] !== true) {
      unset($attributes['publishingenabled']);
    }

    return $this->execute('update', array($id, $attributes));
  }

  /**
   * @param $id
   *
   * @return \Cms\Data\Website
   */
  public function disablePublishing($id)
  {
    return $this->execute('update', array($id, array(
      'publishingenabled' => false,
      'version' => 0,
    )));
  }

  /**
   * creates a new website with the given attributes
   *
   * @param array $attributes
   * @param bool  $useIdFromAttributes
   *
   * @return  \Cms\Data\Website
   */
  public function create(array $attributes, $useIdFromAttributes = false)
  {
    $this->checkWebsiteMaxCountQuota();
    $this->checkWebhostingMaxCountQuota($attributes);

    // only enabling publishing at create service
    if (isset($attributes['publishingenabled']) && $attributes['publishingenabled'] !== true) {
      unset($attributes['publishingenabled']);
    }

    // add default publish configuration
    if (!array_key_exists('publish', $attributes) || empty($attributes['publish'])) {
      $attributes['publish'] = json_encode($this->getDefaultPublishData());
    }

    // add default set id if non is provided
    if (!array_key_exists('usedsetid', $attributes)) {
      $defaultSetId = $this->getDefaultSetId();
      // use default set if proper configured
      if (!empty($defaultSetId)) {
        $attributes['usedsetid'] = $defaultSetId;
      }
    }

    /** @var $website \Cms\Data\Website */
    $website = $this->execute('create', array($attributes, $useIdFromAttributes));
    $this->createWebsiteDirectories($website->getId());
    return $website;
  }

  /**
   * copy an existing website under a new name
   *
   * @param int    $id
   * @param string $newname
   *
   * @return mixed
   */
  public function copy($id, $newname)
  {
    $this->checkWebsiteMaxCountQuota();
    $attributes = array(
      'name' => $newname,
    );

    /** @var $website \Cms\Data\Website */
    $website = $this->execute('copy', array($id, $attributes, \Cms\Version::getMode()));
    $this->createWebsiteDirectories($website->getId());
    return $website;
  }

  /**
   * delete a website
   *
   * @param string $id
   *
   * @return mixed
   */
  public function deleteById($id)
  {
    $this->deleteWebsiteDirectories($id);
    return $this->execute('deleteById', array($id));
  }

  /**
   * @param string $id
   *
   * @return mixed
   */
  public function markForDeletion($id)
  {
    return $this->execute('markForDeletion', array($id));
  }

  public function getByMarkedForDeletion()
  {
    return $this->execute('getByMarkedForDeletion');
  }
  
  public function getByCreationMode($creationMode)
  {
    return $this->execute('getByCreationMode', array($creationMode));
  }
  
  /**
   * @param  string $id
   * @return boolean
   */
  public function existsWebsiteAlready($id)
  {
    return $this->execute('existsWebsite', array($id));
  }

  public function movePageInNavigation($websiteId, $pageId, $parentId, $beforeId)
  {
    $website = $this->getById($websiteId);
    $navigation = \Zend_Json::decode($website->getNavigation());

    $data = new \Seitenbau\ArrayData();
    $result = $data->move($navigation, $pageId, $parentId, $beforeId);
    if ($result == false) {
      throw new CmsException(752, __METHOD__, __LINE__, 'could not generate new navigation');
    }
    $result = \Zend_Json::encode($result);

    $attributes = array('navigation' => $result);
    $this->update($websiteId, $attributes);

    $result = array('id' => $pageId, 'navigation' => $result);
    return $result;
  }

  public function addPageToNavigation(
      Data\Page $page,
      $websiteId,
      $parentId,
      $insertBeforeId
  ) {
    $dataPage = array(
      'id'  => $page->getId()
    );

    $website = $this->getById($websiteId);
    $navigation = \Zend_Json::decode($website->getNavigation());

    $data = new \Seitenbau\ArrayData();
    $result = $data->insert($navigation, $dataPage, $parentId, $insertBeforeId);
    if ($result == false) {
      throw new CmsException(752, __METHOD__, __LINE__);
    }
    $result = \Zend_Json::encode($result);

    $attributes = array('navigation' => $result);
    $this->update($websiteId, $attributes);

    $result = array('id' => $page->getId(), 'navigation' => $result);
    return $result;
  }

  /**
   * Gibt die Website-IDs zurueck, welche unter einer angegeben Parent-ID liegen
   * Die Pages werden als flaches Array zurueckgegeben
   *
   * @param string $websiteId ID der Website
   * @param string $parentId  ID von der Unter-Pages zurueckgegeben werdnen
   *
   * @return array
   */
  public function getSubPagesFromNavigation($websiteId, $parentId)
  {
    $pages = array();

    $website = $this->getById($websiteId);
    $navigation = \Zend_Json::decode($website->getNavigation());

    if (!is_array($navigation)) {
      return $pages;
    }

    $subNavigation = null;
    $this->getSubNavigation($navigation, $parentId, $subNavigation);
    if (is_array($subNavigation)) {
      $data = new \Seitenbau\ArrayData();
      $data->setValuesAsArray($pages, $subNavigation);
    }
    
    return $pages;
  }

  /**
   * Gibt die Navigation ab einer angegebenen Tiefe zurueck
   *
   * @param array  $navigation
   * @param string $parentId Wert, von dem die Subnavigation zurueckgegeben wird
   * @param        $subNavigation
   *
   * @return array|null
   */
  protected function getSubNavigation($navigation, $parentId, &$subNavigation)
  {
    foreach ($navigation as $entry) {
      if ($entry['id'] == $parentId) {
        if (isset($entry['children']) && is_array($entry['children'])) {
          $subNavigation = $entry['children'];
        }
        return true;
      } elseif (isset($entry['children']) && is_array($entry['children'])) {
        $found = $this->getSubNavigation($entry['children'], $parentId, $subNavigation);
        if ($found) {
          return $found;
        }
      }
    }
    return false;
  }

  /**
   * @throws /Cms/Exception
   */
  public function checkWebsiteMaxCountQuota()
  {
    $count = $this->execute('getCount');
    $quotas = new Quota();
    $websiteQuota = $quotas->getWebsiteQuota();
    if ($count >= $websiteQuota->getMaxCount()) {
      throw new CmsException(2300, __METHOD__, __LINE__);
    }
  }

  /**
   * @param array   $attributes
   * @param string  $websiteId
   * @throws /Cms/Exception
   */
  protected function checkWebhostingMaxCountQuota(array $attributes, $websiteId = null)
  {
    if (!array_key_exists('publishingenabled', $attributes)) {
      return;
    }
    if (!$attributes['publishingenabled']) {
      return;
    }

    $quotas = new Quota();
    $webhostingQuota = $quotas->getWebhostingQuota();
    if ($this->getNewPublishingEnabledCount($websiteId) > $webhostingQuota->getMaxCount()) {
      throw new CmsException(2303, __METHOD__, __LINE__);
    }
  }

  /**
   * Get used set id of a website
   *
   * @param $websiteId
   *
   * @return  GlobalSetSource - null if this website does not use a global set
   */
  public function getUsedSetSource($websiteId)
  {
    if (!Registry::getConfig()->item->sets->enabled) {
      return new GlobalSetSource($websiteId, array());
    }

    $cacheKey = self::CACHE_PREFIX_USED_SET_ID . $websiteId;
    $itemSetSource = $this->getCache()->getValue($cacheKey, null);
    if (isset($itemSetSource)) {
      return $itemSetSource;
    }

    /** @var \Cms\Data\Website $website */
    $website = $this->getById($websiteId);
    $useSetId = $website->getUsedSetId();
    $sources = array();
    if (!empty($useSetId)) {
      $sources[] = $this->getSourceItemForSetId($website->getUsedSetId());
    }
    $usedSetSource = new GlobalSetSource($websiteId, $sources);
    $this->getCache()->setValue($cacheKey, $usedSetSource);
    return $usedSetSource;
  }

  /**
   * @param string $setId
   *
   * @return SourceItem
   */
  protected function getSourceItemForSetId($setId)
  {
    $config = Registry::getConfig()->item->sets;
    return new SourceItem(
        $setId,
        FS::joinPath($config->directory, $setId),
        $config->url . '/'. $setId,
        SourceItem::SOURCE_REPOSITORY,
        true,
        false
    );
  }

  /**
   * @return string|null
   */
  protected function getDefaultSetId()
  {
    $config = Registry::getConfig()->item->sets;
    if (!$config->enabled) {
      return null;
    }
    $defaultSetId = $config->default_set_id;
    if (empty($defaultSetId)) {
      return null;
    }
    return $defaultSetId;
  }

  /**
   * @param string $id
   */
  private function deleteWebsiteDirectories($id)
  {
    $itemDataDirectory = realpath(FS::joinPath(Registry::getConfig()->item->data->directory, $id));
    if (!empty($itemDataDirectory) && is_dir($itemDataDirectory)) {
      try {
        FS::rmdir($itemDataDirectory);
      } catch (\Exception $logOnly) {
        Registry::getLogger()->log(__METHOD__, __LINE__, $logOnly->getMessage(), SbLog::ERR);
      }
    }
  }

  /**
   * @param string $id
   */
  private function createWebsiteDirectories($id)
  {
    $baseDataDirectory = realpath(Registry::getConfig()->item->data->directory);
    if (empty($baseDataDirectory)) {
      Registry::getLogger()->log(__METHOD__, __LINE__, 'failed to calculate base data directory: '.$baseDataDirectory, SbLog::ERR);
      return;
    }
    $itemDataDirectory = FS::joinPath($baseDataDirectory, $id);
    if (empty($itemDataDirectory) || is_dir($itemDataDirectory)) {
      return;
    }
    try {
      FS::createDirIfNotExists($itemDataDirectory, true);
    } catch (\Exception $logOnly) {
      Registry::getLogger()->log(__METHOD__, __LINE__, $logOnly->getMessage(), SbLog::ERR);
    }
  }

  /**
   * @return array
   */
  protected function getDefaultPublishData()
  {
    /** @var $publisherService \Cms\Service\Publisher */
    $publisherService = $this->getService('Publisher');
    return $publisherService->getDefaultPublishData();
  }

  /**
   * @return string[]
   */
  private function getWebsiteIdsWithPublishingEnabled()
  {
    $websiteIds = array();
    $websites = $this->getAll();
    foreach ($websites as $website) {
      if ($website->getPublishingEnabled()) {
        $websiteIds[] = $website->getId();
      }
    }
    return array_unique($websiteIds);
  }

  /**
   * @param null|string $websiteId
   *
   * @return int
   */
  private function getNewPublishingEnabledCount($websiteId)
  {
    $websiteIdsWithPublishingEnabled = $this->getWebsiteIdsWithPublishingEnabled();
    $publishingEnabledCount = count($websiteIdsWithPublishingEnabled);

    if (!is_null($websiteId)) {
      // new website
      $publishingEnabledCount++;
      return $publishingEnabledCount;
    } elseif (!in_array($websiteId, $websiteIdsWithPublishingEnabled)) {
      // update website and enable publishing
      $publishingEnabledCount++;
      return $publishingEnabledCount;
    }
    return $publishingEnabledCount;
  }

  /**
   * @return StaticCache
   */
  protected function getCache()
  {
    if (!isset($this->cache)) {
      $this->cache = new StaticCache(__CLASS__);
    }
    return $this->cache;
  }
}
