<?php


namespace Test\Render\InfoStorage\NavigationInfoStorage;


use Render\InfoStorage\NavigationInfoStorage\NavigationInfoStorageItem;
use Test\Render\InfoStorage\AbstractInfoStorageTestCase;

/**
 * Class NavigationInfoStorageTestCase
 *
 * @package Test\Render\InfoStorage
 */
abstract class AbstractNavigationInfoStorageTestCase extends AbstractInfoStorageTestCase
{
  /**
   * @var string
   */
  protected $websiteId = 'SITE-service0-base-d0na-v0st-orage0test00-SITE';

  /**
   * @var string
   */
  protected $currentPageId = 'PAGE-current0-page-0id0-0000-000000000000-PAGE';

  /**
   * @var array|null
   */
  protected $pageArray = null;

  /**
   * @var array|null
   */
  protected $navigationArray = null;

  /**
   * @param array       $pageArray
   * @param array       $navigation
   * @param string|null $currentPageId
   *
   * @return \Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage
   */
  abstract protected function getNavigationInfoStorage(array $pageArray, array $navigation,
                                                       $currentPageId = null);

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getCurrentPageId_returnPageId
   */
  public function test_getCurrentPageId_returnPageId($pageArray, $navigation,
                                                     $expectedPageId)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation,
      $expectedPageId);

    // ACT
    $actualPageId = $infoStorage->getCurrentPageId();

    // ASSERT
    $this->assertEquals($expectedPageId, $actualPageId);
  }

  /**
   * @return array
   */
  public function provider_test_getCurrentPageId_returnPageId()
  {
    return array(
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        $this->currentPageId
      ),
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        null
      ),
    );
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_itemExists
   */
  public function test_itemExists($pageArray, $navigation, $pageId,
                                  $expectedPageExists)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $actualExists = $infoStorage->itemExists($pageId);

    // ASSERT
    $this->assertInternalType('boolean', $actualExists);
    $this->assertEquals($expectedPageExists, $actualExists);
  }

  /**
   * @return array
   */
  public function provider_test_itemExists()
  {
    return array(
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        'PAGE-page0exi-sts0-0000-0000-000000000000-PAGE', true
      ),
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        'PAGE-page0not-0exi-sts0-0000-000000000000-PAGE', false
      ),
    );
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getParentIds_returnParentIds
   */
  public function test_getParentIds_returnParentIds($pageArray, $navigation,
                                                    $pageId, $expectedParentIds)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $actualParentIds = $infoStorage->getParentIds($pageId);

    // ASSERT
    $this->assertEquals($expectedParentIds, $actualParentIds);
  }

  /**
   * @return array
   */
  public function provider_test_getParentIds_returnParentIds()
  {
    return array(array(
      $this->getPageArray(), $this->getNavigationArray(),
      'PAGE-service0-base-d0na-v0st-orage0test04-PAGE',
      $this->getPageParentIds('PAGE-service0-base-d0na-v0st-orage0test04-PAGE'),
    ));
  }


  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_notExistingPages
   *
   * @expectedException \Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function test_getParentIds_throwExceptionForNotExistingPage($pageArray,
                                                                     $navigation,
                                                                     $pageId)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $infoStorage->getParentIds($pageId);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getChildrenIds_returnChildrenIds
   */
  public function test_getChildrenIds_returnChildrenIds($pageArray, $navigation,
                                                        $pageId, $expectedChildrenIds)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $actualChildrenIds = $infoStorage->getChildrenIds($pageId);

    // ASSERT
    $this->assertEquals($expectedChildrenIds, $actualChildrenIds);
  }

  /**
   * @return array
   */
  public function provider_test_getChildrenIds_returnChildrenIds()
  {
    return array(array(
      $this->getPageArray(), $this->getNavigationArray(),
      'PAGE-service0-base-d0na-v0st-orage0test01-PAGE',
      $this->getPageChildrenIds('PAGE-service0-base-d0na-v0st-orage0test01-PAGE'),
    ));
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_notExistingPages
   *
   * @expectedException \Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function test_getChildrenIds_throwExceptionForNotExistingPage($pageArray,
                                                                       $navigation,
                                                                       $pageId)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $infoStorage->getChildrenIds($pageId);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getPageUrl_returnUrlForPage
   */
  public function test_getPageUrl_returnUrlForPage($pageArray, $navigation,
                                                   $pageId, $parameters,
                                                   $expectedUrl)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $actualUrl = $infoStorage->getPageUrl($pageId, $parameters, false);

    // ASSERT
    $this->assertEquals($expectedUrl, $actualUrl);
  }

  /**
   * @return array
   */
  abstract public function provider_test_getPageUrl_returnUrlForPage();

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_notExistingPages
   *
   * @expectedException \Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function test_getPageUrl_throwExceptionForNotExistingPage($pageArray,
                                                                   $navigation,
                                                                   $pageId)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $infoStorage->getPageUrl($pageId, array(), false);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getPageGlobals_returnPageGlobals
   */
  public function test_getPageGlobals_returnPageGlobals($pageArray, $navigation,
                                                        $pageId, $expectedPageGlobals)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $actualPageGlobals = $infoStorage->getPageGlobals($pageId);

    // ASSERT
    $this->assertEquals($expectedPageGlobals, $actualPageGlobals);
  }

  /**
   * @return array
   */
  public function provider_test_getPageGlobals_returnPageGlobals()
  {
    return array(
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        $this->currentPageId,
        $this->getPageGlobals($this->currentPageId),
      ),
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        'PAGE-service0-base-d0na-v0st-orage0test01-PAGE',
        $this->getPageGlobals('PAGE-service0-base-d0na-v0st-orage0test01-PAGE'),
      )
    );
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_notExistingPages
   *
   * @expectedException \Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function test_getPageGlobals_throwExceptionForNotExistingPage($pageArray,
                                                                       $navigation,
                                                                       $pageId)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $infoStorage->getPageGlobals($pageId);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getPageAttributes_returnExpectedAttributes
   */
  public function test_getPageAttributes_returnExpectedAttributes($pageArray, $navigation,
                                                                  $pageId, $expectedPageAttributes)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $actualPageAttributes = $infoStorage->getPageAttributes($pageId);

    // ASSERT
    $this->assertEquals($expectedPageAttributes, $actualPageAttributes);
  }

  /**
   * @return array
   */
  public function provider_test_getPageAttributes_returnExpectedAttributes()
  {
    return array(
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        $this->currentPageId,
        $this->getPageAttributes($this->currentPageId),
      ),
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        'PAGE-service0-base-d0na-v0st-orage0test01-PAGE',
        $this->getPageAttributes('PAGE-service0-base-d0na-v0st-orage0test01-PAGE'),
      )
    );
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_notExistingPages
   *
   * @expectedException \Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function test_getPageAttributes_throwExceptionForNotExistingPage($pageArray,
                                                                          $navigation, $pageId)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $infoStorage->getPageAttributes($pageId);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getItem_returnItemObject
   */
  public function test_getItem_returnItemObject($pageArray, $navigation,
                                                $pageId, $expectedItem)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $actualItem = $infoStorage->getItem($pageId);

    // ASSERT
    $this->assertInstanceOf('\Render\InfoStorage\NavigationInfoStorage\NavigationInfoStorageItem', $actualItem);
    $this->assertNavigationItemEquals($expectedItem, $actualItem);
  }

  /**
   * @return array
   */
  public function provider_test_getItem_returnItemObject()
  {
    return array(
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        'PAGE-service0-base-d0na-v0st-orage0test01-PAGE',
        $this->getItemObject('PAGE-service0-base-d0na-v0st-orage0test01-PAGE'),
      ),
      array(
        $this->getPageArray(), $this->getNavigationArray(),
        'PAGE-page0000-not0-in00-navi-000000000000-PAGE',
        $this->getItemObject('PAGE-page0000-not0-in00-navi-000000000000-PAGE'),
      ),
    );
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_notExistingPages
   *
   * @expectedException \Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists
   */
  public function test_getItem_throwExceptionForNotExistingPage($pageArray,
                                                                $navigation,
                                                                $pageId)
  {
    // ARRANGE
    $infoStorage = $this->getNavigationInfoStorage($pageArray, $navigation);

    // ACT
    $infoStorage->getItem($pageId);
  }

  /**
   * @return array
   */
  public function provider_notExistingPages()
  {
    return array(array(
      $this->getPageArray(), $this->getNavigationArray(),
      'PAGE-page0not-0exi-sts0-0000-000000000000-PAGE',
    ));
  }

  /**
   * @param NavigationInfoStorageItem $expectedItem
   * @param NavigationInfoStorageItem $actualItem
   */
  protected function assertNavigationItemEquals(NavigationInfoStorageItem $expectedItem,
                                                NavigationInfoStorageItem $actualItem)
  {
    $this->assertSame($expectedItem->getPageId(), $actualItem->getPageId(),
      "Assertion for NavigationStorageItem::getPageId:");
    $this->assertSame($expectedItem->getPageType(), $actualItem->getPageType(),
      "Assertion for NavigationStorageItem::getPageType:");
    $this->assertSame($expectedItem->getTitle(), $actualItem->getTitle(),
      "Assertion for NavigationStorageItem::getTitle:");
    $this->assertSame($expectedItem->getDescription(), $actualItem->getDescription(),
      "Assertion for NavigationStorageItem::getDescription:");
    $this->assertSame($expectedItem->getDate(), $actualItem->getDate(),
      "Assertion for NavigationStorageItem::getDate:");
    $this->assertSame($expectedItem->showInNavigation(), $actualItem->showInNavigation(),
      "Assertion for NavigationStorageItem::showInNavigation:");
    $this->assertSame($expectedItem->getNavigationTitle(), $actualItem->getNavigationTitle(),
      "Assertion for NavigationStorageItem::getNavigationTitle:");
    $this->assertSame($expectedItem->getMediaId(), $actualItem->getMediaId(),
      "Assertion for NavigationStorageItem::getMediaId:");
    $this->assertEquals($expectedItem->getParentIds(), $actualItem->getParentIds(),
      "Assertion for NavigationStorageItem::getParentIds:");
    $this->assertEquals($expectedItem->getChildrenIds(), $actualItem->getChildrenIds(),
      "Assertion for NavigationStorageItem::getChildrenIds:");
  }

  /**
   * @param $pageId
   *
   * @throws \Exception
   * @return NavigationInfoStorageItem
   */
  protected function getItemObject($pageId)
  {
    $pageData = $this->getPageData($pageId);
    $parentIds = $this->getPageParentIds($pageId);
    $childrenIds = $this->getPageChildrenIds($pageId);
    return new NavigationInfoStorageItem($pageId,
      $pageData['pageType'], $pageData['name'],
      $pageData['description'], $pageData['date'],
      $pageData['inNavigation'], $pageData['navigationTitle'],
      $parentIds, $childrenIds, $pageData['mediaId']);
  }

  /**
   * @param string $pageId
   *
   * @throws \Exception
   * @return array
   */
  protected function getPageParentIds($pageId)
  {
    $parentIds = array();
    $pageData = $this->getPageData($pageId);
    $parentId = $pageData['parentId'];
    $secureCount = 0;
    while(!empty($parentId))
    {
      $parentIds[] = $parentId;
      $parentPageData = $this->getPageData($parentId);
      $parentId = $parentPageData['parentId'];
      if (++$secureCount > 10000) {
        throw new \Exception('Infinite loop detected at dummy page data!');
      }
    }
    return array_reverse($parentIds);
  }

  /**
   * @param $pageId
   *
   * @return array
   */
  protected function getPageChildrenIds($pageId)
  {
    $childrenIds = array();
    $pageArray = $this->getPageArray();
    foreach ($pageArray as $id => $data) {
      if ($data['parentId'] == $pageId) {
        $childrenIds[] = $id;
      }
    }
    return $childrenIds;
  }

  /**
   * @param $pageId
   *
   * @return string
   */
  protected function getPageGlobals($pageId)
  {
    $pageData = $this->getPageData($pageId);
    return $pageData['global'];
  }

  /**
   * @param $pageId
   *
   * @return mixed
   */
  protected function getPageAttributes($pageId)
  {
    $pageData = $this->getPageData($pageId);
    return $pageData['pageAttributes'];
  }

  /**
   * @param $pageId
   *
   * @throws \Exception
   * @return array
   */
  protected function getPageData($pageId)
  {
    $pageArray = $this->getPageArray();
    if (!isset($pageArray[$pageId])) {
      throw new \Exception('No dummy data found for page id '.$pageId);
    }
    return $pageArray[$pageId];
  }

  /**
   * @return array
   */
  protected function getNavigationArray()
  {
    if (is_null($this->navigationArray)) {
      $this->initNavigationArray();
    }
    return $this->navigationArray;
  }

  protected function initNavigationArray()
  {
    $pageArray = $this->getPageArray();
    $this->navigationArray = $this->createNavigationArray($pageArray);
  }

  protected function createNavigationArray(&$pageArray, $parentId=null)
  {
    $navigation = array();
    foreach ($pageArray as $pageId => $pageData) {
      if ($pageData['parentId'] == $parentId) {
        $navigation[] = array(
          'id' => $pageId,
          'name' => $pageData['name'],
          'children' => $this->createNavigationArray($pageArray, $pageId),
        );
      }
    }
    return $navigation;
  }

  /**
   * @return array
   */
  protected function getPageArray()
  {
    if (is_null($this->pageArray)) {
      $this->initPageArray();
    }
    return $this->pageArray;
  }

  protected function initPageArray()
  {
    $this->pageArray = array(
      'PAGE-home0pag-e000-0000-0000-000000000000-PAGE' => array(
        'name' => 'Home Page - name',
        'description' => 'Home Page - description',
        'navigationTitle' => 'Home Page - navigationTitle',
        'inNavigation' => true,
        'date' => mktime(1, 1, 1, 1, 1, 2014),
        'mediaId' => '',
        'parentId' => null,
        'global' => array(),
        'pageType' => 'home',
        'pageAttributes' => array(),
      ),
      'PAGE-current0-page-0id0-0000-000000000000-PAGE' => array(
        'name' => 'Current Page - name',
        'description' => 'Current Page - description',
        'navigationTitle' => 'Current Page - navigationTitle',
        'inNavigation' => true,
        'date' => mktime(2, 1, 1, 1, 1, 2014),
        'mediaId' => '',
        'parentId' => 'PAGE-home0pag-e000-0000-0000-000000000000-PAGE',
        'global' => array(
          'foo1' => array (
            array (
              'unitId' => 'MUNIT-00000000-0000-0000-0000-000000000001-MUNIT',
              'templateUnitId' => 'MUNIT-00000000-0000-0000-0001-000000000001-MUNIT',
              'moduleId' => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL',
              'value' => 'bar1',
              'isUnitValue' => false,
            ),
            array (
              'unitId' => 'MUNIT-00000000-0000-0000-0000-000000000002-MUNIT',
              'templateUnitId' => 'MUNIT-00000000-0000-0000-0001-000000000002-MUNIT',
              'moduleId' => 'MODUL-00000000-0000-0000-0000-000000000002-MODUL',
              'value' => 'bar2',
              'isUnitValue' => true,
            )
          )
        ),
        'pageType' => 'page',
        'pageAttributes' => array(
          'foo' => 'bar',
          'myArray' => array('foo', 'bar'),
          'myObject' => array(
            'foo' => 'bar',
          )
        ),
      ),
      'PAGE-page0exi-sts0-0000-0000-000000000000-PAGE' => array(
        'name' => 'Existing Page - name',
        'description' => 'Existing Page - description',
        'navigationTitle' => 'Existing Page - navigationTitle',
        'inNavigation' => 1,
        'date' => mktime(3, 1, 1, 1, 1, 2014),
        'mediaId' => '',
        'parentId' => 'PAGE-home0pag-e000-0000-0000-000000000000-PAGE',
        'global' => array(),
        'pageType' => 'page',
        'pageAttributes' => array(),
      ),
      'PAGE-page0000-not0-in00-navi-000000000000-PAGE' => array(
        'name' => 'Hidden Page - name',
        'description' => 'Hidden Page - description',
        'navigationTitle' => 'Hidden Page - navigationTitle',
        'inNavigation' => 0,
        'date' => mktime(4, 1, 1, 1, 1, 2014),
        'mediaId' => '',
        'parentId' => 'PAGE-home0pag-e000-0000-0000-000000000000-PAGE',
        'global' => array(),
        'pageType' => 'page',
        'pageAttributes' => array(),
      ),
    );

    $nextParentId = 'PAGE-home0pag-e000-0000-0000-000000000000-PAGE';
    for($i=1; $i<=4; $i++) {
      $nextPageId = 'PAGE-service0-base-d0na-v0st-orage0test0'.$i.'-PAGE';
      $this->pageArray[$nextPageId] = array(
        'name' => 'Page '.$i.' - name',
        'description' => 'Page '.$i.' - description',
        'navigationTitle' => 'Page '.$i.' - navigationTitle',
        'inNavigation' => 1,
        'date' => mktime($i, 1, 1, 1, 7, 2014),
        'mediaId' => '',
        'parentId' => $nextParentId,
        'global' => array(
          'foo'.$i => array (
            array (
              'unitId' => 'MUNIT-00000001-0000-0000-0000-00000000000'.$i.'-MUNIT',
              'templateUnitId' => 'MUNIT-00000001-0000-0000-0001-00000000000'.$i.'-MUNIT',
              'moduleId' => 'MODUL-00000001-0000-0000-0000-00000000000'.$i.'-MODUL',
              'value' => 'bar'.$i,
              'isUnitValue' => false,
            )
          )
        ),
        'pageType' => 'page',
        'pageAttributes' => array(
          'foo' => 'bar'.$i,
        ),
      );
      $nextParentId = $nextPageId;
    }
  }
}
 