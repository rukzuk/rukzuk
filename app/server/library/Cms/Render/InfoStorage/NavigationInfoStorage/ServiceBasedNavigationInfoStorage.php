<?php


namespace Cms\Render\InfoStorage\NavigationInfoStorage;

use Cms\Business\Page as PageBusiness;
use Cms\Business\PageType as PageTypeBusiness;
use Cms\Business\Website;
use Render\InfoStorage\NavigationInfoStorage\AbstractNavigationInfoStorage;
use Render\InfoStorage\NavigationInfoStorage\NavigationInfoStorageItem;
use Render\PageUrlHelper\IPageUrlHelper;
use Seitenbau\Json;
use Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists;

class ServiceBasedNavigationInfoStorage extends AbstractNavigationInfoStorage
{
  /**
   * @var \Cms\Business\Website
   */
  private $websiteBusiness;

  /**
   * @var \Cms\Business\Page
   */
  private $pageBusiness;

  /**
   * @var \Cms\Business\PageType
   */
  private $pageTypeBusiness;

  /**
   * @var string
   */
  private $websiteId;

  /**
   * @var array|null
   */
  private $currentItemInfo;

  /**
   * @var bool
   */
  private $currentItemIsTemplate = false;

  /**
   * @var bool
   */
  private $isNavigationDataInitialized = false;

  /**
   * @var array of NavigationInfoStorageItem
   */
  private $navigationNodes = array();

  /**
   * @var array
   */
  private $rootChildrenIds = array();

  /**
   * @param Website          $websiteBusiness
   * @param PageBusiness     $pageBusiness
   * @param PageTypeBusiness $pageTypeBusiness
   * @param string           $websiteId
   * @param IPageUrlHelper   $pageUrlHelper
   * @param string|null      $currentItemId
   * @param bool             $currentItemIsTemplate
   * @param array|null       $currentItemInfo
   */
  public function __construct(
      Website $websiteBusiness,
      PageBusiness $pageBusiness,
      PageTypeBusiness $pageTypeBusiness,
      $websiteId,
      IPageUrlHelper $pageUrlHelper,
      $currentItemId = null,
      $currentItemIsTemplate = false,
      array $currentItemInfo = null
  ) {
    parent::__construct($currentItemId, $pageUrlHelper);
    $this->websiteBusiness = $websiteBusiness;
    $this->pageBusiness = $pageBusiness;
    $this->pageTypeBusiness = $pageTypeBusiness;
    $this->websiteId = $websiteId;
    $this->currentItemInfo = $currentItemInfo;
    $this->currentItemIsTemplate = $currentItemIsTemplate;
  }

  /**
   * @param $pageId
   *
   * @return NavigationInfoStorageItem
   *
   * @throws NavigationInfoStorageItemDoesNotExists
   */
  public function getItem($pageId)
  {
    if ($pageId == $this->getCurrentPageId()) {
      return $this->getCurrentItem();
    }
    return $this->getPageItem($pageId);
  }

  /**
   * @param string $pageId
   *
   * @return array
   *
   * @throws NavigationInfoStorageItemDoesNotExists
   */
  public function getPageGlobals($pageId)
  {
    if ($pageId == $this->getCurrentPageId()) {
      if (isset($this->currentItemInfo['globalContent'])) {
        return $this->currentItemInfo['globalContent'];
      }
      return array();
    }
    $this->validateItem($pageId);
    try {
      $page = $this->getPageDataById($pageId);
    } catch (\Exception $_e) {
      throw new NavigationInfoStorageItemDoesNotExists();
    }
    return \json_decode($page->getGlobalContent(), true);
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
    $item = $this->getItem($pageId);
    try {
      $page = $this->getPageDataById($item->getPageId());
      $pageAttributes = \json_decode($page->getPageAttributes(), true);
      if (!is_array($pageAttributes)) {
        $pageAttributes = array();
      }
    } catch (\Exception $doNothing) {
      $pageAttributes = array();
    }
    $defaultPageAttributes = $this->getDefaultPageAttributes($item->getPageType());
    return array_merge($defaultPageAttributes, $pageAttributes);
  }

  /**
   * @return string
   */
  protected function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @return \Cms\Business\Page
   */
  protected function getPageBusiness()
  {
    return $this->pageBusiness;
  }

  /**
   * @return \Cms\Business\PageType
   */
  protected function getPageTypeBusiness()
  {
    return $this->pageTypeBusiness;
  }

  /**
   * @return \Cms\Business\Website
   */
  protected function getWebsiteBusiness()
  {
    return $this->websiteBusiness;
  }

  /**
   * @return array
   */
  protected function getNavigationNodes()
  {
    $this->initNavigationData();
    return $this->navigationNodes;
  }

  /**
   * @return array
   */
  public function getRootChildrenIds()
  {
    $this->initNavigationData();
    return $this->rootChildrenIds;
  }

  protected function initNavigationData()
  {
    if ($this->isNavigationDataInitialized) {
      return;
    }

    $rawNavigation = $this->getRawNavigation();

    $pageInfoList = $this->getPageBusiness()->getInfosByWebsiteId($this->getWebsiteId(), false);
    $this->rootChildrenIds = $this->initNavigationSubNodes($rawNavigation, $pageInfoList, array());

    $this->isNavigationDataInitialized = true;
  }

  /**
   * @param array $rawItems
   * @param array $pageInfoList
   * @param array $parentIds
   *
   * @return array  page ids from the sub nodes
   */
  protected function initNavigationSubNodes(array &$rawItems, array &$pageInfoList, array $parentIds = array())
  {
    $currentNodeIds = array();
    foreach ($rawItems as &$rawPageInfo) {
      $pageId = $rawPageInfo['id'];
      if (isset($rawPageInfo['children']) && is_array($rawPageInfo['children'])) {
        $parentIdsForChildren = $parentIds;
        $parentIdsForChildren[] = $pageId;
        $childrenIds = $this->initNavigationSubNodes($rawPageInfo['children'], $pageInfoList, $parentIdsForChildren);
      } else {
        $childrenIds = array();
      }
      $this->navigationNodes[$pageId] = $this->getNavigationInfoStorageItem($pageId, $pageInfoList, $parentIds, $childrenIds);
      $currentNodeIds[] = $pageId;
    }
    return $currentNodeIds;
  }

  /**
   * @param string $pageId
   * @param array  $pageInfoList
   * @param array  $parentIds
   * @param array  $childrenIds
   *
   * @return NavigationInfoStorageItem
   */
  private function getNavigationInfoStorageItem(
      $pageId,
      array &$pageInfoList,
      array $parentIds,
      array $childrenIds
  ) {

    if (isset($pageInfoList[$pageId]) && is_array($pageInfoList[$pageId])) {
      $pageInfo = $pageInfoList[$pageId];
    } else {
      $pageInfo = array();
    }

    // merge current page info
    if ($pageId == $this->getCurrentPageId() && is_array($this->currentItemInfo)) {
      $pageInfo = array_replace($pageInfo, $this->currentItemInfo);
    }

    return $this->createNavigationInfoStorageItem($pageId, $pageInfo, $parentIds, $childrenIds);
  }

  /**
   * @param string $pageId
   * @param array  $pageInfo
   * @param array  $parentIds
   * @param array  $childrenIds
   *
   * @return NavigationInfoStorageItem
   */
  protected function createNavigationInfoStorageItem(
      $pageId,
      array $pageInfo,
      array $parentIds,
      array $childrenIds
  ) {
    return new NavigationInfoStorageItem(
        $pageId,
        (isset($pageInfo['pageType']) && is_string($pageInfo['pageType']) ? $pageInfo['pageType'] : ''),
        (isset($pageInfo['name']) && is_string($pageInfo['name']) ? $pageInfo['name'] : ''),
        (isset($pageInfo['description']) && is_string($pageInfo['description']) ? $pageInfo['description'] : ''),
        (isset($pageInfo['date']) && intval($pageInfo['date']) ? intval($pageInfo['date']) : 0),
        (isset($pageInfo['inNavigation']) ? (bool)$pageInfo['inNavigation'] : false),
        (isset($pageInfo['navigationTitle']) && is_string($pageInfo['navigationTitle']) ? $pageInfo['navigationTitle'] : ''),
        $parentIds,
        $childrenIds,
        (isset($pageInfo['mediaId']) && is_string($pageInfo['mediaId']) ? $pageInfo['mediaId'] : '')
    );
  }

  /**
   * @return array
   */
  private function getRawNavigation()
  {
    $website = $this->getWebsiteBusiness()->getById($this->getWebsiteId());
    $rawNavigation = \json_decode($website->getNavigation(), true);
    if (!is_array($rawNavigation)) {
      return array();
    }
    return $rawNavigation;
  }

  /**
   * @param string $pageId
   *
   * @return \Cms\Data\Page
   */
  protected function getPageDataById($pageId)
  {
    return $this->getPageBusiness()->getById($pageId, $this->getWebsiteId());
  }

  /**
   * @param string $pageTypeId
   *
   * @return array
   */
  protected function getDefaultPageAttributes($pageTypeId)
  {
    try {
      $pageTypeData = $this->getPageTypeBusiness()->getById($this->getWebsiteId(), $pageTypeId);
    } catch (\Exception $doNothing) {
      return array();
    }
    $defaultPageAttributes = $this->objectToArray($pageTypeData->getFormValues());
    if (!is_array($defaultPageAttributes)) {
      return array();
    }
    return $defaultPageAttributes;
  }

  /**
   * @param string $pageId
   *
   * @return NavigationInfoStorageItem
   */
  protected function getPageItem($pageId)
  {
    $this->validateItem($pageId);
    $navigationNodes = $this->getNavigationNodes();
    return $navigationNodes[$pageId];
  }

  /**
   * @return NavigationInfoStorageItem
   */
  protected function getCurrentItem()
  {
    if ($this->currentItemIsTemplate) {
      return $this->getCurrentTemplateItem();
    } else {
      return $this->getCurrentPageItem();
    }
  }

  /**
   * @return NavigationInfoStorageItem
   */
  protected function getCurrentPageItem()
  {
    $pageId = $this->getCurrentPageId();
    return $this->getPageItem($pageId);
  }

  /**
   * @return NavigationInfoStorageItem
   */
  protected function getCurrentTemplateItem()
  {
    return $this->createNavigationInfoStorageItem(
        $this->getCurrentPageId(),
        $this->currentItemInfo,
        array(),
        array()
    );
  }

  /**
   * @param object $object
   *
   * @return array
   */
  protected function objectToArray($object)
  {
    return json_decode(json_encode($object), true);
  }
}
