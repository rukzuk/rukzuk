<?php

namespace Cms\Creator\Adapter\DynamicCreator;


class SiteStructurePageUrlHelperTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group rendering
   * @group creator
   * @group small
   * @group dev
   */
  public function test_getPageUrl()
  {
    // ARRANGE
    $currentPageId = 23;
    $pageUrlPrefix = 'http://url/prefix/';
    $simplePageUrlHelper = new SiteStructurePageUrlHelper($this->getSiteStructure(), $currentPageId, $pageUrlPrefix);

    // ACT
    $url = $simplePageUrlHelper->getPageUrl(342, array(), false);

    // ASSERT
    $this->assertStringStartsWith($pageUrlPrefix, $url);
    $this->assertStringEndsWith('#url_for_page342', $url);
  }

  /**
   * @test
   * @group rendering
   * @group creator
   * @group small
   * @group dev
   */
  public function test_getPageUrl_withParameters()
  {
    // ARRANGE
    $currentPageId = 23;
    $pageUrlPrefix = 'http://url/prefix/';
    $pageUrlEndPart = '#url_for_page342?getkey=get%2Fvalue';
    $simplePageUrlHelper = new SiteStructurePageUrlHelper($this->getSiteStructure(), $currentPageId, $pageUrlPrefix);

    // ACT
    $url = $simplePageUrlHelper->getPageUrl(342, array('getkey' => 'get/value'), false);

    // ASSERT
    $this->assertStringStartsWith($pageUrlPrefix, $url);
    $this->assertStringEndsWith($pageUrlEndPart, $url);
  }

  /**
   * @test
   * @group rendering
   * @group creator
   * @group small
   * @group dev
   */
  public function test_getCurrentPageUrl()
  {
    // ARRANGE
    $currentPageId = 23;
    $pageUrlPrefix = 'http://url/prefix/';
    $siteStructurePageUrlHelper = new SiteStructurePageUrlHelper($this->getSiteStructure(), $currentPageId, $pageUrlPrefix);

    // ACT
    $url = $siteStructurePageUrlHelper->getCurrentUrl();

    // ASSERT
    $this->assertStringStartsWith($pageUrlPrefix, $url);
    $this->assertStringEndsWith('#url_for_page23', $url);
  }

  /**
   * @test
   * @group rendering
   * @group creator
   * @group small
   * @group dev
   */
  public function test_getCurrentCssPageUrl()
  {
    // ARRANGE
    $currentPageId = 2;
    $pageUrlPrefix = 'http://url/prefix/';
    $siteStructurePageUrlHelper = new SiteStructurePageUrlHelper($this->getSiteStructure(), $currentPageId, $pageUrlPrefix);

    // ACT
    $url = $siteStructurePageUrlHelper->getCurrentCssUrl();

    // ASSERT
    $this->assertStringStartsWith($pageUrlPrefix, $url);
    $this->assertStringEndsWith('files/css/' . md5($currentPageId) . '.css', $url);
  }

  /**
   * @test
   * @group rendering
   * @group creator
   * @group small
   * @group dev
   * @expectedException \Render\PageUrlHelper\PageUrlNotAvailable
   */
  public function test_getPageUrlNotFoundException()
  {
    // ARRANGE
    $currentPageId = 2;
    $pageUrlPrefix = 'http://url/prefix/';
    $siteStructurePageUrlHelper = new SiteStructurePageUrlHelper($this->getSiteStructureEmpty(), $currentPageId, $pageUrlPrefix);

    // ACT
    $siteStructurePageUrlHelper->getPageUrl(5, array(), false);

    // ASSERT
    // see comment
  }

  /**
   * @return \Cms\Creator\Adapter\DynamicCreator\SiteStructure
   */
  protected function getSiteStructure()
  {

    $stub = $this->getMockBuilder('\Cms\Creator\Adapter\DynamicCreator\SiteStructure')
      ->disableOriginalConstructor()
      ->getMock();

    // Configure the stub.
    $stub->expects($this->any())
      ->method('getPageUrl')
      ->will($this->returnCallback(function ($pageId) {
        return '#url_for_page' . $pageId;
      }));

    return $stub;
  }

  /**
   * @return \Cms\Creator\Adapter\DynamicCreator\SiteStructure
   */
  protected function getSiteStructureEmpty()
  {

    $stub = $this->getMockBuilder('\Cms\Creator\Adapter\DynamicCreator\SiteStructure')
      ->disableOriginalConstructor()
      ->getMock();

    // Configure the stub.
    $stub->expects($this->any())
      ->method('getPageUrl')
      ->will($this->returnValue(null));

    return $stub;
  }

}
