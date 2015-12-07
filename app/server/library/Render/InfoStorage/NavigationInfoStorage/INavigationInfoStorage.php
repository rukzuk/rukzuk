<?php


namespace Render\InfoStorage\NavigationInfoStorage;

interface INavigationInfoStorage
{
  /**
   * Returns the id of the current page.
   *
   * NOTE: If this is a template this will NOT return the template id!
   *       Use {@link #getCurrentUrl} if you need to know the template URL.
   *
   * @return string
   */
  public function getCurrentPageId();

  /**
   * returns the list of parent ids for the given page id
   *
   * @param $pageId
   *
   * @return string[]
   *
   * @throws Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function getParentIds($pageId);

  /**
   * returns the list of children ids for the given page id
   *
   * @param $pageId
   *
   * @return string[]
   *
   * @throws Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function getChildrenIds($pageId);

  /**
   * checks if the item of the given page id exists
   *
   * @param $pageId
   *
   * @return bool
   */
  public function itemExists($pageId);

  /**
   * returns storage item of the given page id
   *
   * @param $pageId
   *
   * @return NavigationInfoStorageItem
   *
   * @throws Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function getItem($pageId);

  /**
   * @param string $pageId
   * @param array  $parameters
   * @param bool   $absoluteUrl
   *
   * @return string
   */
  public function getPageUrl($pageId, array $parameters, $absoluteUrl);

  /**
   * returns the global values for given page id
   *
   * @param $pageId
   *
   * @return array
   *
   * @throws Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function getPageGlobals($pageId);

  /**
   * URL of the current Page (or Template)
   * @return string
   */
  public function getCurrentUrl();

  /**
   * URL of the CSS file for the current Page (or Template)
   * @return string
   */
  public function getCurrentCssUrl();

  /**
   * Root Navigation Items
   * @return string[]
   */
  public function getRootChildrenIds();

  /**
   * returns the attributes of the given page id
   *
   * @param string $pageId
   *
   * @return array
   */
  public function getPageAttributes($pageId);
}
