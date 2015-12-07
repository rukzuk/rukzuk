<?php


namespace Render\InfoStorage\NavigationInfoStorage;


use Cms\Data\Page as DataPage;
use Cms\Data\PageType as DataPageType;
use Cms\Render\InfoStorage\NavigationInfoStorage\ServiceBasedNavigationInfoStorage;
use Cms\Render\PageUrlHelper\CmsPageUrlHelper;
use Test\Render\InfoStorage\NavigationInfoStorage\AbstractNavigationInfoStorageTestCase;

class ServiceBasedNavigationInfoStorageTest extends AbstractNavigationInfoStorageTestCase
{
  /**
   * @var string
   */
  protected $renderPageServiceUrl = '/render/page/service/url';

  /**
   * @var \Cms\Data\PageType
   */
  protected $dummyPageTypeData = array();


  protected function setUp()
  {
    parent::setUp();
    $this->dummyPageTypeData = array();
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getPageGlobals_returnPageGlobalsForCurrentPage()
  {
    // ARRANGE
    $currentPageId = $this->currentPageId;
    $expectedPageGlobals = array(
      'foo' => array(
        array(
          'unitId' => 'MUNIT-00000001-0001-0001-0000-000000000001-MUNIT',
          'templateUnitId' => 'MUNIT-00000001-0001-0001-0001-000000000001-MUNIT',
          'moduleId' => 'MODUL-00000001-0001-0001-0001-000000000001-MODUL',
          'value' => 'bar',
          'isUnitValue' => true,
        )
      )
    );
    $pageInfo = array(
      'globalContent' => $expectedPageGlobals,
    );
    $pageInfoList = $this->getPageInfoList($this->getPageArray());
    /** @var $infoStorage \Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage */
    $infoStorage = $this->getServiceBasedNavigationInfoStorage($pageInfoList,
      $this->getNavigationArray(), $currentPageId, false, $pageInfo);

    // ACT
    $actualPageGlobals = $infoStorage->getPageGlobals($currentPageId);

    // ASSERT
    $this->assertEquals($expectedPageGlobals, $actualPageGlobals);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getPageAttributes_returnMergedPageAttributes()
  {
    // ARRANGE
    $pageId = 'PAGE-current0-page-0id0-0000-000000000000-PAGE';
    $this->setDummyPageTypeData('page', (object)array(
      'foo' => 'this_value_will_be_overwritten',
      'keyFromPageTypeFormValues' => 'theDefaultValueFromPageTypeFormValues',
    ));
    $expectedPageAttributes = array(
      'foo' => 'bar',
      'myArray' => array('foo', 'bar'),
      'myObject' => array(
        'foo' => 'bar',
      ),
      'keyFromPageTypeFormValues' => 'theDefaultValueFromPageTypeFormValues',
    );
    $infoStorage = $this->getNavigationInfoStorage($this->getPageArray(), $this->getNavigationArray());

    // ACT
    $actualPageAttributes = $infoStorage->getPageAttributes($pageId);

    // ASSERT
    $this->assertEquals($expectedPageAttributes, $actualPageAttributes);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getPageAttributes_returnDefaultPageAttributesForRenderingTemplate()
  {
    // ARRANGE
    $templateId = 'TPL-current0-temp-late-0id0-000000000000-TPL';
    $pageType = 'page';
    $expectedPageAttributes = array(
      'foo' => 'this_value_will_be_overwritten',
      'keyFromPageTypeFormValues' => 'theDefaultValueFromPageTypeFormValues',
    );
    $this->setDummyPageTypeData($pageType, (object)$expectedPageAttributes);
    $currentItemInfo = array(
      'pageType' => $pageType,
    );
    $infoStorage = $this->getNavigationInfoStorage($this->getPageArray(),
      $this->getNavigationArray(), $templateId, true, $currentItemInfo);

    // ACT
    $actualPageAttributes = $infoStorage->getPageAttributes($templateId);

    // ASSERT
    $this->assertEquals($expectedPageAttributes, $actualPageAttributes);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getItem_returnItemWithTemplateDataAtRenderingTemplate()
  {
    // ARRANGE
    $templateId = 'TPL-current0-temp-late-0id0-000000000000-TPL';
    $currentItemInfo = array(
      'id' => $templateId,
      'name' => 'this_is_the_current_item_name',
      'pageType' => 'page',
    );
    $expectedItem = new NavigationInfoStorageItem($templateId,
      $currentItemInfo['pageType'], $currentItemInfo['name'],
      '', 0, false, '', array(), array(), '');
    $infoStorage = $this->getNavigationInfoStorage($this->getPageArray(),
      $this->getNavigationArray(), $templateId, true, $currentItemInfo);

    // ACT
    $actualItem = $infoStorage->getItem($infoStorage->getCurrentPageId());

    // ASSERT
    $this->assertNavigationItemEquals($expectedItem, $actualItem);
  }

  /**
   * @return array
   */
  public function provider_test_getPageUrl_returnUrlForPage()
  {
    $data = array();

    // Existing page
    $pageId = 'PAGE-page0exi-sts0-0000-0000-000000000000-PAGE';
    $paramString = \json_encode(array(
      'pageid' => $pageId,
      'websiteid' => $this->websiteId,
    ));
    $data[] = array(
      $this->getPageArray(),
      $this->getNavigationArray(),
      $pageId,
      array(),
      $this->renderPageServiceUrl . urlencode($paramString)
    );

    // Existing page with parameters
    $pageId = 'PAGE-page0exi-sts0-0000-0000-000000000000-PAGE';
    $paramString = \json_encode(array(
      'pageid' => $pageId,
      'websiteid' => $this->websiteId,
    ));
    $data[] = array(
      $this->getPageArray(),
      $this->getNavigationArray(),
      $pageId,
      array('foo' => 'bar', 'bar' => 'f/oo'),
      $this->renderPageServiceUrl . urlencode($paramString) . '?foo=bar&bar=f%2Foo'
    );

    // Not existing page
    $pageId = 'PAGE-page0exi-sts0-0000-0000-000000000000-PAGE';
    $paramString = \json_encode(array(
      'pageid' => $pageId,
      'websiteid' => $this->websiteId,
    ));
    $data[] = array(
      $this->getPageArray(),
      $this->getNavigationArray(),
      $pageId,
      array(),
      $this->renderPageServiceUrl . urlencode($paramString)
    );

    return $data;
  }

  /**
   * @param array  $pageArray
   * @param array  $navigation
   * @param string $currentItemId
   * @param bool   $currentItemIsTemplate
   * @param array  $currentItemInfo
   *
   * @return INavigationInfoStorage
   */
  protected function getNavigationInfoStorage(array $pageArray, array $navigation,
                                              $currentItemId = null,
                                              $currentItemIsTemplate = false,
                                              array $currentItemInfo = null)
  {
    $pageInfoList = $this->getPageInfoList($pageArray);
    return $this->getServiceBasedNavigationInfoStorage($pageInfoList, $navigation,
      $currentItemId, $currentItemIsTemplate, $currentItemInfo);
  }

  /**
   * @param array      $pageInfoList
   * @param array      $navigation
   * @param string     $currentItemId
   * @param bool       $currentItemIsTemplate
   * @param array|null $currentItemInfo
   *
   * @return ServiceBasedNavigationInfoStorage
   */
  protected function getServiceBasedNavigationInfoStorage(array $pageInfoList, array $navigation,
                                                          $currentItemId = null,
                                                          $currentItemIsTemplate = false,
                                                          array $currentItemInfo = null)
  {
    $websiteId = $this->websiteId;

    // Create test website data object
    $websiteData = $this->createWebsiteDataObject($websiteId, $navigation);

    // Create website business mock
    $websiteBusinessStub = $this->getMockBuilder('\Cms\Business\Website')
      ->disableOriginalConstructor()->getMock();
    $websiteBusinessStub->expects($this->any())
      ->method('getById')
      // TODO: ->with($websiteId) removed, because there is a bug in phpunit 3.5.x if method isn't called
      ->will($this->returnValue($websiteData));

    // Create page business mock
    $pageBusinessStub = $this->getMockBuilder('\Cms\Business\Page')
      ->disableOriginalConstructor()->getMock();
    $pageBusinessStub->expects($this->any())
      ->method('getInfosByWebsiteId')
      // TODO: ->with($websiteId) removed, because there is a bug in phpunit 3.5.x if method isn't called
      ->will($this->returnValue($pageInfoList));
    $pageBusinessStub->expects($this->any())
      ->method('getById')
      ->will($this->returnCallback(array($this, 'pageBusinessStubGetByIdCallback')));

    // Create pageType business mock
    $pageTypeBusinessStub = $this->getMockBuilder('\Cms\Business\PageType')
      ->disableOriginalConstructor()->getMock();
    $pageTypeBusinessStub->expects($this->any())
      ->method('getById')
      // TODO: ->with($websiteId) removed, because there is a bug in phpunit 3.5.x if method isn't called
      ->will($this->returnCallback(array($this, 'pageTypeBusinessStubGetByIdCallback')));

    $pageUrlHelper = new CmsPageUrlHelper($websiteId, $currentItemId, true,
      '/render/page/service/url', '#pagecss', '#tpl', '#tplcss', '');

    return new ServiceBasedNavigationInfoStorage($websiteBusinessStub, $pageBusinessStub,
      $pageTypeBusinessStub, $websiteId, $pageUrlHelper,
      $currentItemId, $currentItemIsTemplate, $currentItemInfo);
  }

  public function pageBusinessStubGetByIdCallback($pageId, $websiteId)
  {
    if ($websiteId !== $this->websiteId) {
      throw new \Exception('No dummy page data found for website id ' . $websiteId);
    }
    return $this->getDataPageObject($pageId);
  }

  public function pageTypeBusinessStubGetByIdCallback($websiteId, $pageTypeId)
  {
    if ($websiteId !== $this->websiteId) {
      throw new \Exception('No dummy page type data found for website id ' . $websiteId);
    }
    if (!array_key_exists($pageTypeId, $this->dummyPageTypeData)) {
      throw new \Exception('No dummy page type data found for page type id ' . $pageTypeId);
    }
    return $this->dummyPageTypeData[$pageTypeId];
  }

  /**
   * @param string $websiteId
   * @param array  $navigation
   *
   * @return \Cms\Data\Website
   */
  protected function createWebsiteDataObject($websiteId, $navigation)
  {
    $websiteData = new \Cms\Data\Website();
    $websiteData->setId($websiteId);
    $websiteData->setNavigation(\json_encode($navigation));
    return $websiteData;
  }

  /**
   * @param string $pageId
   *
   * @throws \Exception
   * @return DataPage
   */
  protected function getDataPageObject($pageId)
  {
    $pageData = $this->getPageData($pageId);
    $page = new DataPage();
    $page->setWebsiteid($this->websiteId)
      ->setId($pageId)
      ->setName($pageData['name'])
      ->setDescription($pageData['description'])
      ->setNavigationtitle($pageData['navigationTitle'])
      ->setInnavigation(($pageData['inNavigation'] ? 1 : 0))
      ->setDate($pageData['date'])
      ->setGlobalContent(\json_encode($pageData['global']))
      ->setPageType($pageData['pageType'])
      ->setPageAttributes(\json_encode($pageData['pageAttributes']));
    return $page;
  }

  /**
   * @param array $pageArray
   *
   * @return array
   */
  protected function getPageInfoList(array $pageArray)
  {
    $pageInfoList = array();
    foreach ($pageArray as $pageId => $pageData) {
      $pageInfoList[$pageId] = array(
        'name' => $pageData['name'],
        'description' => $pageData['description'],
        'navigationTitle' => $pageData['navigationTitle'],
        'inNavigation' => $pageData['inNavigation'],
        'date' => $pageData['date'],
        'mediaId' => $pageData['mediaId'],
        'pageType' => $pageData['pageType'],
      );
    }
    return $pageInfoList;
  }

  /**
   * @param string $pageTypeId
   * @param mixed  $formValues
   */
  private function setDummyPageTypeData($pageTypeId, $formValues)
  {
    $pageTypeData = new DataPageType();
    $pageTypeData->setWebsiteId($this->websiteId);
    $pageTypeData->setId($pageTypeId);
    $pageTypeData->setFormValues($formValues);
    $this->dummyPageTypeData[$pageTypeId] = $pageTypeData;
  }
}
