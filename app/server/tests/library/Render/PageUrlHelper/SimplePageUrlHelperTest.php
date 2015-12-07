<?php

namespace Render\PageUrlHelper;


class SimplePageUrlHelperTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @dataProvider provider_test_getPageUrl
   */
  public function test_getPageUrl($pageUrls, $currentPageId, $currentCssUrl, $pageUrlPrefix,
                                  $serverVars, $pageId, $parameters, $absoluteUrl, $expectedUrl)
  {
    // ARRANGE
    $simplePageUrlHelper = $this->getMockBuilder('\Render\PageUrlHelper\SimplePageUrlHelper')
      ->setConstructorArgs(array($pageUrls, $currentPageId, $currentCssUrl, $pageUrlPrefix))
      ->setMethods(array('getServerVar'))
      ->getMock();
    $simplePageUrlHelper->expects($this->any())
      ->method('getServerVar')
      ->will($this->returnCallback(function ($name, $default) use ($serverVars) {
        return isset($serverVars[$name]) ? $serverVars[$name] : $default;
      }));

    // ACT
    $url = $simplePageUrlHelper->getPageUrl($pageId, $parameters, $absoluteUrl);

    // ASSERT
    $this->assertContains($pageUrlPrefix, $url);
    $this->assertSame($expectedUrl, $url);
  }

  /**
   * @return array
   */
  public function provider_test_getPageUrl()
  {
    $urlPrefix = '/url/prefix/';
    return array(
      array(
        array(23 => '#currentUrl', 1 => '#test_id1'),
        23,
        '#testCssUrl',
        $urlPrefix,
        array(),
        1,
        array(),
        false,
        $urlPrefix . '#test_id1',
      ),
      array(
        array(23 => '#currentUrl', 1 => '#test_id1'),
        23,
        '#testCssUrl',
        $urlPrefix,
        array(),
        1,
        array(),
        true,
        'http://localhost' . $urlPrefix . '#test_id1',
      ),
      array(
        array(23 => '#currentUrl', 1 => '#test_id1'),
        23,
        '#testCssUrl',
        $urlPrefix,
        array('HTTPS' => 'on', 'HTTP_HOST' => 'myhost.name'),
        1,
        array(),
        true,
        'https://myhost.name' . $urlPrefix . '#test_id1',
      ),
      array(
        array(23 => '#currentUrl', 1 => '#test_id1'),
        23,
        '#testCssUrl',
        $urlPrefix,
        array(),
        1,
        array('foo' => 'bar', 'bar' => 'f/oo'),
        false,
        $urlPrefix . '#test_id1?foo=bar&bar=f%2Foo',
      ),
      array(
        array(23 => '#currentUrl', 1 => '#test_id1'),
        23,
        '#testCssUrl',
        $urlPrefix,
        array(),
        1,
        array('foo' => 'bar', 'bar' => 'f/oo'),
        true,
        'http://localhost' . $urlPrefix . '#test_id1?foo=bar&bar=f%2Foo',
      ),
      array(
        array(23 => '#currentUrl', 1 => '#test_id1'),
        23,
        '#testCssUrl',
        $urlPrefix,
        array('HTTPS' => 'on', 'HTTP_HOST' => 'myhost.name'),
        1,
        array('foo' => 'bar', 'bar' => 'f/oo'),
        true,
        'https://myhost.name' . $urlPrefix . '#test_id1?foo=bar&bar=f%2Foo',
      ),
    );
  }


  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getCurrentPageUrl()
  {
    // ARRANGE
    $pageUrls = array(23 => '#currentUrl', 1 => '#test_id1');
    $currentPageId = 23;
    $currentCssUrl = '#testCssUrl';
    $pageUrlPrefix = 'http://url/prefix/';
    $simplePageUrlHelper = new SimplePageUrlHelper($pageUrls, $currentPageId, $currentCssUrl, $pageUrlPrefix);

    // ACT
    $url = $simplePageUrlHelper->getCurrentUrl();

    // ASSERT
    $this->assertStringStartsWith($pageUrlPrefix, $url);
    $this->assertStringEndsWith($pageUrls[$currentPageId], $url);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getCurrentCssPageUrl()
  {
    // ARRANGE
    $pageUrls = array(23 => '#currentUrl', 1 => '#test_id1');
    $currentPageId = 23;
    $currentCssUrl = '#testCssUrl';
    $pageUrlPrefix = 'http://url/prefix/';
    $simplePageUrlHelper = new SimplePageUrlHelper($pageUrls, $currentPageId, $currentCssUrl, $pageUrlPrefix);

    // ACT
    $url = $simplePageUrlHelper->getCurrentCssUrl();

    // ASSERT
    $this->assertStringStartsNotWith($pageUrlPrefix, $url);
    $this->assertEquals($currentCssUrl, $url);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @expectedException \Render\PageUrlHelper\PageUrlNotAvailable
   */
  public function test_getPageUrlNotFoundException()
  {
    // ARRANGE
    $pageUrls = array(23 => '#currentUrl', 1 => '#test_id1');
    $currentPageId = 23;
    $currentCssUrl = '#testCssUrl';
    $pageUrlPrefix = 'http://url/prefix/';
    $simplePageUrlHelper = new SimplePageUrlHelper($pageUrls, $currentPageId, $currentCssUrl, $pageUrlPrefix);

    // ACT
    $simplePageUrlHelper->getPageUrl(5, array(), false);

    // ASSERT
    // see comment
  }

}
