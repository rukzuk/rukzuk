<?php


namespace Render\APIs\APIv1;


use Render\APIs\APIv1\Navigation;
use Render\InfoStorage\NavigationInfoStorage\ArrayBasedNavigationInfoStorage;
use Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists;
use Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage;
use Test\Render\AbstractAPITestCase;


class NavigationTest extends AbstractAPITestCase
{
  const CURRENT_PAGE_ID = 'PAGE-current0-page-0id0-0000-000000000000-PAGE';
  const EXISTING_PAGE_ID = 'PAGE-existing-0pag-e0id-0000-000000000000-PAGE';
  const NOT_EXISTING_PAGE_ID = 'PAGE-not0exis-ting-0pag-e0id-000000000000-PAGE';

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getCurrentPageId
   */
  public function test_getCurrentPageId($pageId)
  {
    // ARRANGE
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getCurrentPageId')
      ->will($this->returnValue($pageId));
    $navigation = $this->createNavigation($infoStorageMock);

    // ACT
    $actualPageId = $navigation->getCurrentPageId();

    // ASSERT
    $this->assertEquals($pageId, $actualPageId);
  }

  /**
   * @return array
   */
  public function provider_test_getCurrentPageId()
  {
    return array(
      array(self::CURRENT_PAGE_ID),
      array(null),
    );
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getChildrenIds()
  {
    // ARRANGE
    $pageId = self::EXISTING_PAGE_ID;
    $expectedChildrenIds = array(
      'PAGE-00000000-0000-0000-0000-000000000001-PAGE',
      'PAGE-00000000-0000-0000-0000-000000000002-PAGE',
      'PAGE-00000000-0000-0000-0000-000000000003-PAGE',
    );
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getChildrenIds')
      ->with($this->equalTo($pageId))
      ->will($this->returnValue($expectedChildrenIds));
    $navigation = $this->createNavigation($infoStorageMock);

    // ACT
    $actualChildrenIds = $navigation->getChildrenIds($pageId);

    // ASSERT
    $this->assertEquals($expectedChildrenIds, $actualChildrenIds);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getChildrenIds_returnsEmptyArrayIfExceptionOccurs()
  {
    // ARRANGE
    $pageId = self::NOT_EXISTING_PAGE_ID;
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getChildrenIds')
      ->with($this->equalTo($pageId))
      ->will($this->throwException(new NavigationInfoStorageItemDoesNotExists()));
    $navigation = $this->createNavigation($infoStorageMock);

    // ACT
    $actualChildrenIds = $navigation->getChildrenIds($pageId);

    // ASSERT
    $this->assertEquals(array(), $actualChildrenIds);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_pageExists_returnsTrueIfPageExists()
  {
    // ARRANGE
    $pageId = self::EXISTING_PAGE_ID;
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('itemExists')
      ->with($this->equalTo($pageId))
      ->will($this->returnValue(true));
    $navigation = $this->createNavigation($infoStorageMock);

    // ACT
    $actualPageExists = $navigation->pageExists($pageId);

    // ASSERT
    $this->assertInternalType('boolean', $actualPageExists);
    $this->assertTrue($actualPageExists);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_pageExists_returnsFalseIfPageNotExists()
  {
    // ARRANGE
    $pageId = self::NOT_EXISTING_PAGE_ID;
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('itemExists')
      ->with($this->equalTo($pageId))
      ->will($this->returnValue(false));
    $navigation = $this->createNavigation($infoStorageMock);

    // ACT
    $actualPageExists = $navigation->pageExists($pageId);

    // ASSERT
    $this->assertInternalType('boolean', $actualPageExists);
    $this->assertFalse($actualPageExists);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getNavigatorIds()
  {
    // ARRANGE
    $pageId = self::EXISTING_PAGE_ID;
    $parentIds = array(
      'PAGE-00000000-0000-0000-0000-000000000030-PAGE',
      'PAGE-00000000-0000-0000-0000-000000000020-PAGE',
      'PAGE-00000000-0000-0000-0000-000000000010-PAGE',
    );
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getParentIds')
      ->with($this->equalTo($pageId))
      ->will($this->returnValue($parentIds));
    $navigation = $this->createNavigation($infoStorageMock);
    $expectedNavigatorIds = $parentIds;
    $expectedNavigatorIds[] = $pageId;

    // ACT
    $actualNavigatorIds = $navigation->getNavigatorIds($pageId);

    // ASSERT
    $this->assertEquals($expectedNavigatorIds, $actualNavigatorIds);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getNavigatorIds_returnOnlyGivenPageIdIfExceptionOccurs()
  {
    // ARRANGE
    $pageId = self::NOT_EXISTING_PAGE_ID;
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getParentIds')
      ->with($this->equalTo($pageId))
      ->will($this->throwException(new NavigationInfoStorageItemDoesNotExists()));
    $navigation = $this->createNavigation($infoStorageMock);
    $expectedNavigatorIds = array($pageId);

    // ACT
    $actualNavigatorIds = $navigation->getNavigatorIds($pageId);

    // ASSERT
    $this->assertEquals($expectedNavigatorIds, $actualNavigatorIds);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getPage()
  {
    // ARRANGE
    $pageId = self::EXISTING_PAGE_ID;
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $navigation = $this->createNavigation($infoStorageMock);

    // ACT
    $page = $navigation->getPage($pageId);

    // ASSERT
    $this->assertInstanceOf('Render\APIs\APIv1\Page', $page);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getCurrentPage()
  {
    // ARRANGE
    $currentPageId = self::CURRENT_PAGE_ID;
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getCurrentPageId')
      ->will($this->returnValue($currentPageId));
    $navigation = $this->createNavigation($infoStorageMock);

    // ACT
    $actualPage = $navigation->getCurrentPage();

    // ASSERT
    $this->assertInstanceOf('Render\APIs\APIv1\Page', $actualPage);
    $this->assertSame($currentPageId, $actualPage->getPageId());
  }

  /**
   *
   * @param \Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage $navigationInfoStorage
   *
   * @return Navigation
   */
  protected function createNavigation(INavigationInfoStorage $navigationInfoStorage)
  {
    return new Navigation($navigationInfoStorage);
  }

  /**
   * @param string $currentPageId
   *
   * @return INavigationInfoStorage
   */
  protected function createNavigationInfoStorageMock($currentPageId=self::CURRENT_PAGE_ID)
  {
    return $this->getMockBuilder('\Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage')
      ->disableOriginalConstructor()->getMock();
  }
}
