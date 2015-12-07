<?php


namespace Render\InfoStorage\NavigationInfoStorage;


use Render\PageUrlHelper\SimplePageUrlHelper;
use Test\Render\InfoStorage\NavigationInfoStorage\AbstractNavigationInfoStorageTestCase;

class ArrayBasedNavigationInfoStorageTest extends AbstractNavigationInfoStorageTestCase
{
  /**
   * @param array       $pageArray
   * @param array       $navigation
   * @param string|null $currentPageId
   *
   * @return \Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage
   */
  protected function getNavigationInfoStorage(array $pageArray, array $navigation,
                                              $currentPageId = null)
  {
    $fullNavigation = $this->createNavigationWithSubNodes($navigation, $pageArray);
    $pageUrls = $this->createAllPageUrls($navigation, $pageArray);
    $urlHelper = new SimplePageUrlHelper($pageUrls, $currentPageId, '#cssurl');
    return new ArrayBasedNavigationInfoStorage($fullNavigation, $currentPageId, $urlHelper);
  }

  /**
   * @return array
   */
  public function provider_test_getPageUrl_returnUrlForPage()
  {
    return array(
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        'PAGE-page0exi-sts0-0000-0000-000000000000-PAGE',
        array(),
        '/Home Page - name/Existing Page - name/',
      ),
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        'PAGE-page0exi-sts0-0000-0000-000000000000-PAGE',
        array('foo' => 'bar', 'bar' => 'f/oo'),
        '/Home Page - name/Existing Page - name/?foo=bar&bar=f%2Foo',
      )
    );
  }

  /**
   * @param array $navItems
   * @param array $pageArray
   * @param string $parentUrl
   * @param array $pageUrls
   * @return array
   */
  private function createAllPageUrls(array $navItems, array &$pageArray, $parentUrl = '/', &$pageUrls = array())
  {

    foreach ($navItems as &$navItemData) {
      $pageId = $navItemData['id'];
      $pageInfo = $pageArray[$pageId];
      $url = $parentUrl.$pageInfo['name'].'/';
      $pageUrls[$pageId] = $url;
      // children
      $children = (is_array($navItemData['children']) ? $navItemData['children'] : array());
      $this->createAllPageUrls($children, $pageArray, $url, $pageUrls);
    }

    return $pageUrls;
  }

  /**
   * @param array $navItems
   * @param array $pageArray
   * @internal param string $parentUrl
   *
   * @return array
   */
  private function createNavigationWithSubNodes(array $navItems, array &$pageArray)
  {
    $fullNavigation = array();
    foreach ($navItems as &$navItemData) {
      $pageId = $navItemData['id'];
      $pageInfo = $pageArray[$pageId];
      $children = (is_array($navItemData['children']) ? $navItemData['children'] : array());
      $fullNavigation[] = array(
        'id'  => $pageId,
        'name' => $pageInfo['name'],
        'description' => $pageInfo['description'],
        'navigationTitle' => $pageInfo['navigationTitle'],
        'inNavigation' => $pageInfo['inNavigation'],
        'date' => $pageInfo['date'],
        'global' => $pageInfo['global'],
        'pageType' => $pageInfo['pageType'],
        'pageAttributes' => $pageInfo['pageAttributes'],
        'children' => $this->createNavigationWithSubNodes($children, $pageArray),
      );
    }
    return $fullNavigation;
  }
}
 