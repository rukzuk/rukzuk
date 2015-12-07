<?php


namespace Render\APIs\APIv1;

use Render\APIs\APIv1\Page;
use Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage;

class Navigation
{

  /**
   * @var INavigationInfoStorage
   */
  private $navigationInfoStorage;

  /**
   * @param INavigationInfoStorage $navigationInfoStorage
   */
  public function __construct(INavigationInfoStorage $navigationInfoStorage)
  {
    $this->navigationInfoStorage = $navigationInfoStorage;
  }

  /**
   * Returns the id of the page that is currently rendered.
   *
   * @return string|null
   */
  public function getCurrentPageId()
  {
    return $this->getNavigationInfoStorage()->getCurrentPageId();
  }

  /**
   * Returns the page ids of the children for the given page id.
   * If given page id is null the page ids of the root level returned.
   *
   * @param string|null $pageId
   * @return array
   */
  public function getChildrenIds($pageId)
  {

    try {
      if (is_null($pageId)) {
        return $this->getNavigationInfoStorage()->getRootChildrenIds();
      } else {
        return $this->getNavigationInfoStorage()->getChildrenIds($pageId);
      }
    } catch (\Exception $_e) {
      // TODO: error handling
      return array();
    }
  }

  /**
   * Returns TRUE if the page specified by $pageId exists; FALSE otherwise.
   *
   * @param $pageId
   *
   * @return bool
   */
  public function pageExists($pageId)
  {
    return $this->getNavigationInfoStorage()->itemExists($pageId);
  }

  /**
   * Returns an \Render\APIs\APIv1\Page object
   *
   * @param $pageId
   *
   * @return Page
   */
  public function getPage($pageId)
  {
    return new Page($this->getNavigationInfoStorage(), $pageId);
  }

  /**
   * Returns the \Render\APIs\APIv1\Page object of the page that is currently rendered.
   *
   * @return Page
   */
  public function getCurrentPage()
  {
    return $this->getPage($this->getCurrentPageId());
  }

  /**
   * Returns an array of all parent ids for the given page id.
   *
   * @param $pageId
   *
   * @return array
   */
  public function getNavigatorIds($pageId)
  {
    try {
      $navigatorIds = $this->getNavigationInfoStorage()->getParentIds($pageId);
    } catch (\Exception $_e) {
      // TODO: error handling
      $navigatorIds = array();
    }
    $navigatorIds[] = $pageId;
    return $navigatorIds;
  }

  /**
   * @return INavigationInfoStorage
   */
  protected function getNavigationInfoStorage()
  {
    return $this->navigationInfoStorage;
  }
}
