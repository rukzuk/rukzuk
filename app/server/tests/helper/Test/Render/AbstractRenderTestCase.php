<?php


namespace Test\Render;


use \Test\Rukzuk\AbstractTestCase;
use Render\RenderContext;

class AbstractRenderTestCase extends AbstractTestCase
{
  /**
   * Reports an error identified by $message if the two array
   * $expected and $actual are not equal.
   *
   * @param mixed   $expected
   * @param mixed   $actual
   * @param string  $message
   */
  protected function assertArrayKeyOrderEquals($expected, $actual, $message = '')
  {
    $expectedKeys = (is_array($expected) ? array_keys($expected) : array());
    $actualKeys = (is_array($expected) ? array_keys($actual) : array());
    for($i=0; $i < count($expectedKeys); $i++) {
      if (!isset($actualKeys[$i])) {
        $this->fail((!empty($message)
          ? $message
          : sprintf('Failed asserting that index %d "%s" exists.',
              $i, $expectedKeys[$i])
        ));
      }
      if ($expectedKeys[$i] != $actualKeys[$i]) {
        $message =
        $this->fail((!empty($message)
          ? $message
          : sprintf('Failed asserting that index %d "%s" are equal to "%s".',
              $i, $expectedKeys[$i], $actualKeys[$i])
        ));
      }
    }
    if (count($expectedKeys) < count($actualKeys)) {
      $this->assertEquals($expectedKeys, $actualKeys,
        (!empty($message) ? $message : 'Failed asserting that keys equal'));
    }
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage
   */
  protected function createModuleInfoStorageMock()
  {
    return $this->getMockBuilder('\Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage')
      ->disableOriginalConstructor()->getMock();
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage
   */
  protected function createNavigationInfoStorageMock()
  {
    return $this->getMockBuilder('\Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage')
      ->disableOriginalConstructor()->getMock();
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Render\InfoStorage\ColorInfoStorage\IColorInfoStorage
   */
  protected function createColorInfoStorageMock()
  {
    return $this->getMockBuilder('\Render\InfoStorage\ColorInfoStorage\IColorInfoStorage')
      ->disableOriginalConstructor()->getMock();
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Render\MediaContext
   */
  protected function createMediaContextMock()
  {
    return $this->getMockBuilder('\Render\MediaContext')
      ->disableOriginalConstructor()->getMock();
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Render\InfoStorage\WebsiteInfoStorage\IWebsiteInfoStorage
   */
  protected function createWebsiteInfoStorageMock()
  {
    return $this->getMockBuilder('\Render\InfoStorage\WebsiteInfoStorage\IWebsiteInfoStorage')
      ->disableOriginalConstructor()->getMock();
  }

  /**
   * @param array $params
   *
   * @return RenderContext
   */
  protected function createRenderContext(array $params)
  {
    if (!array_key_exists('websiteInfoStorage', $params)) {
      $params['websiteInfoStorage'] = $this->createWebsiteInfoStorageMock();
    }
    if (!array_key_exists('moduleInfoStorage', $params)) {
      $params['moduleInfoStorage'] = $this->createModuleInfoStorageMock();
    }
    if (!array_key_exists('navigationInfoStorage', $params)) {
      $params['navigationInfoStorage'] = $this->createNavigationInfoStorageMock();
    }
    if (!array_key_exists('colorInfoStorage', $params)) {
      $params['colorInfoStorage'] = $this->createColorInfoStorageMock();
    }
    if (!array_key_exists('mediaContext', $params)) {
      $params['mediaContext'] = $this->createMediaContextMock();
    }
    if (!array_key_exists('interfaceLocaleCode', $params)) {
      $params['interfaceLocaleCode'] = 'en-US';
    }
    if (!array_key_exists('renderMode', $params)) {
      $params['renderMode'] = RenderContext::RENDER_MODE_PREVIEW;
    }
    if (!array_key_exists('renderType', $params)) {
      $params['renderType'] = RenderContext::RENDER_TYPE_TEMPLATE;
    }
    if (!array_key_exists('resolutions', $params)) {
      $params['resolutions'] = array();
    }
    if (!array_key_exists('jsApiUrl', $params)) {
      $params['jsApiUrl'] = null;
    }
    if (!array_key_exists('cache', $params)) {
      $params['cache'] = null;
    }

    return new RenderContext(
      $params['websiteInfoStorage'],
      $params['moduleInfoStorage'],
      $params['navigationInfoStorage'],
      $params['colorInfoStorage'],
      $params['mediaContext'],
      $params['interfaceLocaleCode'],
      $params['renderMode'],
      $params['renderType'],
      $params['resolutions'],
      $params['jsApiUrl'],
      $params['cache']
    );
  }
}