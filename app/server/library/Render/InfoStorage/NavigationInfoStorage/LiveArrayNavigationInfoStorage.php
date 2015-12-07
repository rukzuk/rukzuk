<?php

namespace Render\InfoStorage\NavigationInfoStorage;

use Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists;
use Render\PageUrlHelper\IPageUrlHelper;

/**
 * Class LiveArrayNavigationInfoStorage
 * @package Render\InfoStorage\NavigationInfoStorage
 */
class LiveArrayNavigationInfoStorage extends ArrayBasedNavigationInfoStorage
{
  /**
   * @var array
   */
  private $pageGlobalsCache = array();
  /**
   * @var array
   */
  private $pageAttributesCache = array();
  /**
   * @var string
   */
  private $basePageDataDirectory;

  /**
   * @param string         $basePageDataDirectory
   * @param array          $navigation
   * @param null|string    $currentPageId
   * @param IPageUrlHelper $pageUrlHelper
   */
  public function __construct(
      $basePageDataDirectory,
      array $navigation,
      $currentPageId = null,
      $pageUrlHelper
  ) {
    $this->basePageDataDirectory = $basePageDataDirectory;
    parent::__construct($navigation, $currentPageId, $pageUrlHelper);
  }

  /**
   * Optimized Version for Live Rendering (lazy globals loading)
   *
   * @param $pageId
   * @return array
   * @throws NavigationInfoStorageItemDoesNotExists
   *
   * @overrides
   */
  public function getPageGlobals($pageId)
  {
    if (!isset($this->pageGlobalsCache[$pageId])) {
      $pageGlobalFilePath = $this->basePageDataDirectory . DIRECTORY_SEPARATOR .
        $pageId . DIRECTORY_SEPARATOR . 'global.php';
      if (file_exists($pageGlobalFilePath)) {
        /** @noinspection PhpIncludeInspection */
        $this->pageGlobalsCache[$pageId] = @include($pageGlobalFilePath);
      }
    }

    if (!isset($this->pageGlobalsCache[$pageId])) {
      throw new NavigationInfoStorageItemDoesNotExists();
    }

    return $this->pageGlobalsCache[$pageId];
  }

  /**
   * Optimized Version for Live Rendering (lazy loading)
   *
   * @param $pageId
   * @return array
   * @throws NavigationInfoStorageItemDoesNotExists
   *
   * @overrides
   */
  public function getPageAttributes($pageId)
  {
    if (!isset($this->pageAttributesCache[$pageId])) {
      $pageAttributesFilePath = $this->basePageDataDirectory . DIRECTORY_SEPARATOR .
        $pageId . DIRECTORY_SEPARATOR . 'attributes.php';
      if (file_exists($pageAttributesFilePath)) {
        /** @noinspection PhpIncludeInspection */
        $this->pageAttributesCache[$pageId] = @include($pageAttributesFilePath);
      }
    }

    if (!isset($this->pageAttributesCache[$pageId])) {
      throw new NavigationInfoStorageItemDoesNotExists();
    }

    return $this->pageAttributesCache[$pageId];
  }
}
