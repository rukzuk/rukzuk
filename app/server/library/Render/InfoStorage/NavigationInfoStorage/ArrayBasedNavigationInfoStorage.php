<?php


namespace Render\InfoStorage\NavigationInfoStorage;

use Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists;
use Render\PageUrlHelper\IPageUrlHelper;

class ArrayBasedNavigationInfoStorage extends AbstractNavigationInfoStorage
{
  /**
   * @var array of NavigationInfoStorageItem
   */
  private $navigationNodes = array();

  /**
   * @var array
   */
  private $rootChildrenIds = array();

  /**
   * @var array
   */
  private $pageGlobals = array();

  /**
   * @var array
   */
  private $pageAttributes = array();

  /**
   * @param array       $navigation
   * @param null|string $currentPageId
   * @param IPageUrlHelper     $pageUrlHelper
   */
  public function __construct(array $navigation, $currentPageId = null, $pageUrlHelper)
  {
    parent::__construct($currentPageId, $pageUrlHelper);

    foreach ($navigation as &$pageInfo) {
      $this->rootChildrenIds[] = $pageInfo['id'];
    }

    $this->initNodes($navigation);
  }

  /**
   * returns the global values for given page id
   *
   * @param $pageId
   *
   * @return array
   *
   * @throws NavigationInfoStorageItemDoesNotExists
   */
  public function getPageGlobals($pageId)
  {
    if (!isset($this->pageGlobals[$pageId])) {
      throw new NavigationInfoStorageItemDoesNotExists();
    }
    return $this->pageGlobals[$pageId];
  }

  /**
   * returns the attributes of the given page id
   *
   * @param string $pageId
   *
   * @return array
   * @throws NavigationInfoStorageItemDoesNotExists
   */
  public function getPageAttributes($pageId)
  {
    if (!isset($this->pageAttributes[$pageId])) {
      throw new NavigationInfoStorageItemDoesNotExists();
    }
    return $this->pageAttributes[$pageId];
  }

  /**
   * @return string[]
   */
  public function getRootChildrenIds()
  {
    return $this->rootChildrenIds;
  }

  /**
   * @return NavigationInfoStorageItem[]
   */
  protected function getNavigationNodes()
  {
    return $this->navigationNodes;
  }

  /**
   * @param array $navItems
   * @param array $parentIds
   *
   * @return array  page ids from the sub nodes
   */
  protected function initNodes(array &$navItems, array $parentIds = array())
  {
    $currentNodeIds = array();
    foreach ($navItems as &$pageInfo) {
      $pageId = $pageInfo['id'];

      if (isset($pageInfo['children']) && is_array($pageInfo['children'])) {
        $parentIdsForChildren = $parentIds;
        $parentIdsForChildren[] = $pageId;
        $childrenIds = $this->initNodes($pageInfo['children'], $parentIdsForChildren);
      } else {
        $childrenIds = array();
      }
      $this->navigationNodes[$pageId] = $this->createNavigationInfoStorageItem($pageId, $pageInfo, $parentIds, $childrenIds);
      $this->pageGlobals[$pageId] = (isset($pageInfo['global']) ? $pageInfo['global'] : array());
      $this->pageAttributes[$pageId] = (isset($pageInfo['pageAttributes']) ? $pageInfo['pageAttributes'] : array());
      $currentNodeIds[] = $pageId;
    }
    return $currentNodeIds;
  }

  /**
   * @param string $pageId
   * @param array  $pageInfo
   * @param array  $parentIds
   * @param array  $childrenIds
   *
   * @return NavigationInfoStorageItem
   */
  private function createNavigationInfoStorageItem($pageId, array &$pageInfo, $parentIds, $childrenIds)
  {
    return new NavigationInfoStorageItem(
        $pageId,
        (isset($pageInfo['pageType']) && is_string($pageInfo['pageType']) ? $pageInfo['pageType'] : ''),
        (isset($pageInfo['name']) && is_string($pageInfo['name']) ? $pageInfo['name'] : ''),
        (isset($pageInfo['description']) && is_string($pageInfo['description']) ? $pageInfo['description'] : ''),
        (isset($pageInfo['date']) && is_int($pageInfo['date']) ? $pageInfo['date'] : 0),
        (isset($pageInfo['inNavigation']) ? (bool)$pageInfo['inNavigation'] : false),
        (isset($pageInfo['navigationTitle']) && is_string($pageInfo['navigationTitle']) ? $pageInfo['navigationTitle'] : ''),
        $parentIds,
        $childrenIds,
        (isset($pageInfo['mediaId']) && is_string($pageInfo['mediaId']) ? $pageInfo['mediaId'] : '')
    );
  }
}
