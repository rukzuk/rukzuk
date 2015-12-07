<?php


namespace Render\APIs\APIv1;


use Test\Render\AbstractAPITestCase;
use Render\InfoStorage\NavigationInfoStorage\NavigationInfoStorageItem;
use Render\InfoStorage\NavigationInfoStorage\Exceptions\NavigationInfoStorageItemDoesNotExists;

/**
 * @group rendering
 * @group small
 * @group dev
 */
class PageTest extends AbstractAPITestCase
{
  public function test_pageDataInitializedOnlyOnce()
  {
    // ARRANGE
    $pageId = 'PAGE-render00-test-api0-page-000000000001-PAGE';
    $pageItem = new NavigationInfoStorageItem($pageId, 'page_type_id', 'title', 'description',
      time() - 3600, true, 'navigationTitle', array('PARENT_ID_1', 'PARENT_ID_2'),
      array('CHILD_1', 'CHILD_2', 'CHILD_3'), 'MDB_ID_1'
    );
    $callCount = 0;
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getItem')
      ->with($this->equalTo($pageId))
      ->will($this->returnCallback(function ($pageId) use (&$pageItem, &$callCount) {
        $callCount++;
        return $pageItem;
      }));
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getPageUrl')
      ->with($this->equalTo($pageId))
      ->will($this->returnValue('/url/' . $pageId));
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getPageGlobals')
      ->with($this->equalTo($pageId))
      ->will($this->returnValue(array()));

    // ACT
    $page = new Page($infoStorageMock, $pageId);

    // ASSERT
    $this->assertInstanceOf('Render\APIs\APIv1\Page', $page);
    $this->assertEquals(0, $callCount, "Failed asserting that \$infoStorage->getItem() doesn't called at this time");
    $this->assertEquals($pageId, $page->getPageId());
    $this->assertEquals(0, $callCount, "Failed asserting that \$infoStorage->getItem() doesn't called at this time");
    $this->assertEquals('/url/' . $pageId, $page->getUrl());
    $this->assertEquals(0, $callCount, "Failed asserting that \$infoStorage->getItem() doesn't called at this time");
    $this->assertNull($page->getGlobal('teaserTitle'));
    $this->assertEquals(0, $callCount, "Failed asserting that \$infoStorage->getItem() doesn't called at this time");
    $this->assertEquals($pageItem->getTitle(), $page->getTitle());
    $this->assertEquals(1, $callCount, "Failed asserting that \$infoStorage->getItem() only called once at this time");
    $this->assertEquals($pageItem->getDescription(), $page->getDescription());
    $this->assertEquals(1, $callCount, "Failed asserting that \$infoStorage->getItem() only called once at this time");
    $this->assertEquals($pageItem->getDate(), $page->getDate());
    $this->assertEquals(1, $callCount, "Failed asserting that \$infoStorage->getItem() only called once at this time");
    $this->assertEquals($pageItem->showInNavigation(), $page->showInNavigation());
    $this->assertEquals(1, $callCount, "Failed asserting that \$infoStorage->getItem() only called once at this time");
    $this->assertEquals($pageItem->getNavigationTitle(), $page->getNavigationTitle());
    $this->assertEquals(1, $callCount, "Failed asserting that \$infoStorage->getItem() only called once at this time");
    $this->assertEquals($pageItem->getNavigationTitle(), $page->getNavigationTitle(false));
    $this->assertEquals(1, $callCount, "Failed asserting that \$infoStorage->getItem() only called once at this time");
    $this->assertEquals($pageItem->getParentIds(), $page->getParentIds());
    $this->assertEquals(1, $callCount, "Failed asserting that \$infoStorage->getItem() only called once at this time");
    $this->assertEquals($pageItem->getChildrenIds(), $page->getChildrenIds());
    $this->assertEquals(1, $callCount, "Failed asserting that \$infoStorage->getItem() only called once at this time");
    $this->assertEquals($pageItem->getMediaId(), $page->getMediaId());
    $this->assertEquals(1, $callCount, "Failed asserting that \$infoStorage->getItem() only called once at this time");
  }

  /**
   * @dataProvider provider_test_getXXXX_callInitPageData
   */
  public function test_getXXXX_callInitPageData($methodToCall, $methodArguments)
  {
    // ARRANGE
    $pageMock = $this->getMockBuilder('\Render\APIs\APIv1\Page')
      ->disableOriginalConstructor()
      ->setMethods(array('initPageData'))
      ->getMock();
    $pageMock->expects($this->once())->method('initPageData');

    // ACT
    $this->callMethod($pageMock, $methodToCall, $methodArguments);
  }

  /**
   * @return array
   */
  public function provider_test_getXXXX_callInitPageData()
  {
    $providedData = array();

    $ignoredMethods = array(
      '__construct', 'getPageId', 'getUrl', 'getAbsoluteUrl', 'getGlobal', 'getPageAttributes'
    );
    $methodArguments = array(
      'getNavigationTitle' => array(false),
    );

    $class = new \ReflectionClass('\Render\APIs\APIv1\Page');
    $publicMethods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($publicMethods as $method) {
      $methodName = $method->getName();
      if (in_array($methodName, $ignoredMethods)) {
        continue;
      }
      $providedData[] = array(
        $methodName,
        (isset($methodArguments[$methodName]) ? $methodArguments[$methodName] : array()),
      );
    }

    return $providedData;
  }


  public function test_initPageDataDoNotSetValuesIfExceptionOccurred()
  {
    // ARRANGE
    $pageId = 'PAGE-render00-test-api0-page-000000000001-PAGE';
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getItem')
      ->with($this->equalTo($pageId))
      ->will($this->throwException(new \Exception));
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getPageUrl')
      ->with($this->equalTo($pageId))
      ->will($this->throwException(new \Exception));
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getPageGlobals')
      ->with($this->equalTo($pageId))
      ->will($this->throwException(new \Exception));

    // ACT
    $page = new Page($infoStorageMock, $pageId);

    // ASSERT
    $this->assertInstanceOf('Render\APIs\APIv1\Page', $page);
    $this->assertEquals($pageId, $page->getPageId());
    $this->assertEquals('', $page->getUrl());
    $this->assertNull($page->getGlobal('teaserTitle'));
    $this->assertEquals('', $page->getTitle());
    $this->assertEquals('', $page->getDescription());
    $this->assertEquals(0, $page->getDate());
    $this->assertFalse($page->showInNavigation());
    $this->assertEquals('', $page->getNavigationTitle(true));
    $this->assertEquals('', $page->getNavigationTitle(false));
    $this->assertEquals(array(), $page->getParentIds());
    $this->assertEquals(array(), $page->getChildrenIds());
    $this->assertEquals('', $page->getMediaId());
  }

  public function test_getNavigationTitleReturnsTitleAsDefault()
  {
    // ARRANGE
    $pageId = 'PAGE-render00-test-api0-page-000000000001-PAGE';
    $pageItem = new NavigationInfoStorageItem($pageId, 'page_type_id', 'title', 'description',
      time() - 3600, true, '', array(), array(), ''
    );
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getItem')
      ->with($this->equalTo($pageId))
      ->will($this->returnValue($pageItem));

    // ACT
    $page = new Page($infoStorageMock, $pageId);

    // ASSERT
    $this->assertEquals($pageItem->getTitle(), $page->getNavigationTitle(true));
  }

  public function test_getNavigationTitleReturnsEmptyStringIfFlagSetToFalse()
  {
    // ARRANGE
    $pageId = 'PAGE-render00-test-api0-page-000000000001-PAGE';
    $pageItem = new NavigationInfoStorageItem($pageId, 'page_type_id', 'title', 'description',
      time() - 3600, true, '', array(), array(), ''
    );
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getItem')
      ->with($this->equalTo($pageId))
      ->will($this->returnValue($pageItem));

    // ACT
    $page = new Page($infoStorageMock, $pageId);

    // ASSERT
    $this->assertEquals($pageItem->getNavigationTitle(), $page->getNavigationTitle(false));
  }

  public function test_getGlobalReturnsExpectedValueForGivenNameAndIndex()
  {
    // ARRANGE
    $pageId = 'PAGE-render00-test-api0-page-000000000001-PAGE';
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getPageGlobals')
      ->with($this->equalTo($pageId))
      ->will($this->returnValue(array('teaserTitle' => array('index0', 'index1', 'index2'))));
    // ACT
    $page = new Page($infoStorageMock, $pageId);

    // ASSERT
    $this->assertEquals(array('index0', 'index1', 'index2'), $page->getGlobal('teaserTitle'));
    $this->assertEquals('index0', $page->getGlobal('teaserTitle', 0));
    $this->assertEquals('index1', $page->getGlobal('teaserTitle', 1));
    $this->assertEquals('index2', $page->getGlobal('teaserTitle', 2));
    $this->assertNull($page->getGlobal('teaserTitle', 3));
    $this->assertEquals('index2', $page->getGlobal('teaserTitle', -1));
    $this->assertEquals('index1', $page->getGlobal('teaserTitle', -2));
    $this->assertEquals('index0', $page->getGlobal('teaserTitle', -3));
    $this->assertNull($page->getGlobal('teaserTitle', -4));
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @dataProvider provider_test_getUrl
   */
  public function test_getUrl($pageId, $parameters, $expectedUrl)
  {
    // ARRANGE
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getPageUrl')
      ->will($this->returnCallback(function ($pageId, array $parameters, $absoluteUrl) {
        if (is_null($pageId)) {
          throw new NavigationInfoStorageItemDoesNotExists();
        }
        return json_encode(array(
          'pageId' => $pageId,
          'parameters' => $parameters,
          'absoluteUrl' => $absoluteUrl,
        ));
      }));
    $page = new Page($infoStorageMock, $pageId);

    // ACT
    $actualUrl = call_user_func_array(array($page, 'getUrl'), $parameters);

    // ASSERT
    $this->assertInstanceOf('Render\APIs\APIv1\Page', $page);
    $this->assertSame($expectedUrl, $actualUrl);
  }

  /**
   * @return array
   */
  public function provider_test_getUrl()
  {
    return array(
      array(
        null,
        array(),
        '',
      ),
      array(
        'THE-PAGE-ID',
        array(),
        json_encode(array(
          'pageId' => 'THE-PAGE-ID',
          'parameters' => array(),
          'absoluteUrl' => false,
        )),
      ),
      array(
        'THE-PAGE-ID',
        array(
          array()
        ),
        json_encode(array(
          'pageId' => 'THE-PAGE-ID',
          'parameters' => array(),
          'absoluteUrl' => false,
        )),
      ),
      array(
        'THE-PAGE-ID',
        array(
          array('foo' => 'bar', 'bar' => 'f/oo'),
        ),
        json_encode(array(
          'pageId' => 'THE-PAGE-ID',
          'parameters' => array('foo' => 'bar', 'bar' => 'f/oo'),
          'absoluteUrl' => false,
        )),
      ),
      array(
        null,
        array(
          array('foo' => 'bar', 'bar' => 'f/oo'),
        ),
        '',
      ),
    );
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @dataProvider provider_test_getAbsoluteUrl
   */
  public function test_getAbsoluteUrl($pageId, $parameters, $expectedUrl)
  {
    // ARRANGE
    $infoStorageMock = $this->createNavigationInfoStorageMock();
    $infoStorageMock->expects($this->atLeastOnce())
      ->method('getPageUrl')
      ->will($this->returnCallback(function ($pageId, array $parameters, $absoluteUrl) {
        if (is_null($pageId)) {
          throw new NavigationInfoStorageItemDoesNotExists();
        }
        return json_encode(array(
          'pageId' => $pageId,
          'parameters' => $parameters,
          'absoluteUrl' => $absoluteUrl,
        ));
      }));
    $page = new Page($infoStorageMock, $pageId);

    // ACT
    $actualUrl = call_user_func_array(array($page, 'getAbsoluteUrl'), $parameters);

    // ASSERT
    $this->assertInstanceOf('Render\APIs\APIv1\Page', $page);
    $this->assertSame($expectedUrl, $actualUrl);
  }

  /**
   * @return array
   */
  public function provider_test_getAbsoluteUrl()
  {
    return array(
      array(
        null,
        array(),
        '',
      ),
      array(
        'THE-PAGE-ID',
        array(),
        json_encode(array(
          'pageId' => 'THE-PAGE-ID',
          'parameters' => array(),
          'absoluteUrl' => true,
        )),
      ),
      array(
        'THE-PAGE-ID',
        array(array()),
        json_encode(array(
          'pageId' => 'THE-PAGE-ID',
          'parameters' => array(),
          'absoluteUrl' => true,
        )),
      ),
      array(
        'THE-PAGE-ID',
        array(
          array('foo' => 'bar', 'bar' => 'f/oo'),
        ),
        json_encode(array(
          'pageId' => 'THE-PAGE-ID',
          'parameters' => array('foo' => 'bar', 'bar' => 'f/oo'),
          'absoluteUrl' => true,
        )),
      ),
      array(
        null,
        array(
          array('foo' => 'bar', 'bar' => 'f/oo'),
          true
        ),
        '',
      ),
    );
  }

  /**
   * @return INavigationInfoStorage
   */
  protected function createNavigationInfoStorageMock()
  {
    return $this->getMockBuilder('\Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage')
      ->disableOriginalConstructor()->getMock();
  }
}
