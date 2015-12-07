<?php


namespace Render\InfoStorage\NavigationInfoStorage;

/**
 * This data transfer class represents a navigation item (page data) inside the renderer.
 *
 * @package Render\NavigationInfoStorage
 */
class NavigationInfoStorageItem
{
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
  private $title;
  /**
   * @var string
   */
  private $description;
  /**
   * @var string
   */
  private $mediaid;
  /**
   * @var int
   */
  private $date;
  /**
   * @var bool
   */
  private $showInNavigation;
  /**
   * @var string
   */
  private $navigationTitle;
  /**
   * @var array
   */
  private $parentIds;
  /**
   * @var array
   */
  private $childrenIds;

  /**
   * @param string $pageId
   * @param string $pageType
   * @param string $title
   * @param string $description
   * @param int    $date
   * @param bool   $showInNavigation
   * @param string $navigationTitle
   * @param array  $parentIds
   * @param array  $childrenIds
   * @param string  $mediaid
   */
  public function __construct(
      $pageId,
      $pageType,
      $title,
      $description,
      $date,
      $showInNavigation,
      $navigationTitle,
      array $parentIds,
      array $childrenIds,
      $mediaid
  ) {
    $this->pageId = $pageId;
    $this->pageType = $pageType;
    $this->title = $title;
      $this->mediaid = $mediaid;
    $this->description = $description;
    $this->date = $date;
    $this->showInNavigation = $showInNavigation;
    $this->navigationTitle = $navigationTitle;
    $this->parentIds = $parentIds;
    $this->childrenIds = $childrenIds;
  }

  /**
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
    return $this->pageType;
  }

  /**
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @return int
   */
  public function getDate()
  {
    return $this->date;
  }

  /**
   * @return boolean
   */
  public function showInNavigation()
  {
    return ($this->showInNavigation == true);
  }

  /**
   * @return string
   */
  public function getNavigationTitle()
  {
    return $this->navigationTitle;
  }

  /**
   * @return array
   */
  public function getParentIds()
  {
    return $this->parentIds;
  }

  /**
   * @return array
   */
  public function getChildrenIds()
  {
    return $this->childrenIds;
  }

  /**
   * @return string
   */
  public function getMediaId()
  {
    return $this->mediaid;
  }
}
