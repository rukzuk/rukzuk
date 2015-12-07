<?php


namespace Render\APIs\APIv1;

use Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage;

/**
 * Holds the meta data of a page
 */
class Page
{
  /**
   * @var INavigationInfoStorage
   */
  private $navigationInfoStorage;

  /**
   * @var bool
   */
  private $isPageDataInitialized = false;

  /**
   * @var string
   */
  private $pageId;

  /**
   * @var string
   */
  private $pageType;

  /**
   * @var string
   */
  private $title = '';

  /**
   * @var string
   */
  private $description = '';

  /**
   * @var string
   */
  private $mediaid = '';

  /**
   * @var int
   */
  private $date = 0;

  /**
   * @var bool
   */
  private $showInNavigation = false;

  /**
   * @var string
   */
  private $navigationTitle = '';

  /**
   * @var array
   */
  private $parentIds = array();

  /**
   * @var array
   */
  private $childrenIds = array();

  /**
   * @param INavigationInfoStorage $navigationInfoStorage
   * @param string                 $pageId
   */
  public function __construct(INavigationInfoStorage $navigationInfoStorage, $pageId)
  {
    $this->navigationInfoStorage = $navigationInfoStorage;
    $this->pageId = $pageId;
  }

  /**
   * Returns the id of the page
   *
   * @return string
   */
  public function getPageId()
  {
    return $this->pageId;
  }

  /**
   * @return string
   */
  public function getPageType()
  {
    $this->initPageData();
    return $this->pageType;
  }

  /**
   * @return string
   */
  public function getMediaId()
  {
    $this->initPageData();
    return $this->mediaid;
  }

  /**
   * Returns the title of the page
   *
   * @return string
   */
  public function getTitle()
  {
    $this->initPageData();
    return $this->title;
  }

  /**
   * Returns the description of the page
   *
   * @return string
   */
  public function getDescription()
  {
    $this->initPageData();
    return $this->description;
  }

  /**
   * Returns the date of the page
   *
   * @return int
   */
  public function getDate()
  {
    $this->initPageData();
    return $this->date;
  }

  /**
   * Returns TRUE if the page is displayed in the navigation bar; FALSE otherwise.
   *
   * @return boolean
   */
  public function showInNavigation()
  {
    $this->initPageData();
    return $this->showInNavigation;
  }

  /**
   * Returns the title of the page that is shown in the navigation bar.
   * If no navigation title was set and $useTitleAsDefault is TRUE
   * the page title is returned instead of the empty navigation title.
   *
   * @param bool $useTitleAsDefault when TRUE the page title is used as default
   *
   * @return string
   */
  public function getNavigationTitle($useTitleAsDefault = true)
  {
    $this->initPageData();
    if (empty($this->navigationTitle) && $useTitleAsDefault) {
      return $this->getTitle();
    }
    return $this->navigationTitle;
  }

  /**
   * Returns the ids of the parent pages
   *
   * @return array
   */
  public function getParentIds()
  {
    $this->initPageData();
    return $this->parentIds;
  }

  /**
   * Returns the ids of the children pages
   *
   * @return array
   */
  public function getChildrenIds()
  {
    $this->initPageData();
    return $this->childrenIds;
  }

  /**
   * Returns the url of the page
   *
   * @param array $parameters
   *
   * @return string
   */
  public function getUrl(array $parameters = array())
  {
    try {
      $infoStorage = $this->getNavigationInfoStorage();
      return $infoStorage->getPageUrl($this->getPageId(), $parameters, false);
    } catch (\Exception $_e) {
      // TODO: error handling
      return '';
    }
  }

  /**
   * Returns the absolute url of the page
   *
   * @param array $parameters
   *
   * @return string
   */
  public function getAbsoluteUrl(array $parameters = array())
  {
    try {
      $infoStorage = $this->getNavigationInfoStorage();
      return $infoStorage->getPageUrl($this->getPageId(), $parameters, true);
    } catch (\Exception $_e) {
      // TODO: error handling
      return '';
    }
  }

  /**
   * Returns the global value for the given variable name.
   * If index is given, the value of this index/position is returned.
   *
   * @param string    $name
   * @param int|null  $index
   * @return mixed|null
   */
  public function getGlobal($name, $index = null)
  {
    try {
      $globals = $this->getNavigationInfoStorage()->getPageGlobals($this->getPageId());
    } catch (\Exception $_e) {
      // TODO: error handling
      return null;
    }

    // exists value for the given variable name
    if (!is_array($globals) || !isset($globals[$name]) || !is_array($globals[$name])) {
      return null;
    }

    // return all values for the given variable name
    if (is_null($index)) {
      return $globals[$name];
    }

    $index = (int)$index;
    if ($index < 0) {
      // adapt index (from the ending)
      $index = count($globals[$name])-abs($index);
    }

    if (isset($globals[$name][$index])) {
      return $globals[$name][$index];
    } else {
      return null;
    }
  }

  /**
   * Returns the page attributes.
   *
   * @return array
   */
  public function getPageAttributes()
  {
    try {
      $pageAttributes = $this->getNavigationInfoStorage()->getPageAttributes($this->getPageId());
      return is_array($pageAttributes) ? $pageAttributes : array();
    } catch (\Exception $doNothing) {
      return array();
    }
  }

  /**
   * @return INavigationInfoStorage
   */
  protected function getNavigationInfoStorage()
  {
    return $this->navigationInfoStorage;
  }

  protected function initPageData()
  {
    if ($this->isPageDataInitialized) {
      return;
    }

    $navigationInfoStorage = $this->getNavigationInfoStorage();
    try {
      $storageItem = $navigationInfoStorage->getItem($this->pageId);
      $this->pageType = $storageItem->getPageType();
      $this->title = $storageItem->getTitle();
        $this->mediaid = $storageItem->getMediaId();
      $this->description = $storageItem->getDescription();
      $this->date = $storageItem->getDate();
      $this->showInNavigation = $storageItem->showInNavigation();
      $this->navigationTitle = $storageItem->getNavigationTitle();
      $this->parentIds = $storageItem->getParentIds();
      $this->childrenIds = $storageItem->getChildrenIds();
    } catch (\Exception $_e) {
      // TODO: error handling
      return;
    }

    $this->isPageDataInitialized = true;
  }
}
