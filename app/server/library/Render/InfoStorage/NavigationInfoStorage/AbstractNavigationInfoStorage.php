<?php


namespace Render\InfoStorage\NavigationInfoStorage;

use Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists;
use Render\PageUrlHelper\IPageUrlHelper;

abstract class AbstractNavigationInfoStorage implements INavigationInfoStorage
{
  /**
   * @var null|string
   */
  private $currentPageId;

  /**
   * @var IPageUrlHelper
   */
  private $pageUrlHelper;

  /**
   * @param null|string    $currentPageId
   * @param IPageUrlHelper $pageUrlHelper
   */
  public function __construct($currentPageId = null, IPageUrlHelper $pageUrlHelper)
  {
    $this->currentPageId = $currentPageId;
    $this->pageUrlHelper = $pageUrlHelper;
  }

  /**
   * @param string $pageId
   * @param array  $parameters
   * @param bool   $absoluteUrl
   *
   * @return string
   * @throws NavigationInfoStorageItemDoesNotExists
   */
  public function getPageUrl($pageId, array $parameters, $absoluteUrl)
  {
    $this->validateItem($pageId);
    return $this->pageUrlHelper->getPageUrl($pageId, $parameters, $absoluteUrl);
  }

  /**
   * URL of the Current Page (or Template)
   * @return string
   */
  public function getCurrentUrl()
  {
    return $this->pageUrlHelper->getCurrentUrl();
  }

  /**
   * URL of the Current Page (or Template)
   * @return string
   */
  public function getCurrentCssUrl()
  {
    return $this->pageUrlHelper->getCurrentCssUrl();
  }

  /**
   * @return string
   */
  public function getCurrentPageId()
  {
    return $this->currentPageId;
  }

  /**
   * @param $pageId
   *
   * @return bool
   */
  public function itemExists($pageId)
  {
    $navigationNodes = $this->getNavigationNodes();
    return (isset($navigationNodes[$pageId]));
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
    $this->validateItem($pageId);
    $navigationNodes = $this->getNavigationNodes();
    return $navigationNodes[$pageId];
  }

  /**
   * @param $pageId
   *
   * @return string[]
   *
   * @throws NavigationInfoStorageItemDoesNotExists
   */
  public function getParentIds($pageId)
  {
    return $this->getItem($pageId)->getParentIds();
  }

  /**
   * @param $pageId
   *
   * @return string[]
   *
   * @throws NavigationInfoStorageItemDoesNotExists
   */
  public function getChildrenIds($pageId)
  {
    $storageItem = $this->getItem($pageId);
    return $storageItem->getChildrenIds();
  }

  /**
   * @param $pageId
   *
   * @throws NavigationInfoStorageItemDoesNotExists
   */
  protected function validateItem($pageId)
  {
    if (!$this->itemExists($pageId)) {
      throw new NavigationInfoStorageItemDoesNotExists();
    }
  }

  /**
   * @return NavigationInfoStorageItem[]
   */
  abstract protected function getNavigationNodes();
}
