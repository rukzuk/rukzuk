<?php


namespace Render\RequestHelper;


use Test\Render\AbstractRenderTestCase;

class MagicQuotesActiveTestableSimpleHttpRequest extends SimpleHttpRequest
{
  private $phpunit_params = array();

  public function getRawParam($key)
  {
    if (isset($this->phpunit_params[$key])) {
      return $this->phpunit_params[$key];
    }
    return null;
  }

  protected function isMagicQuotesActive()
  {
    return true;
  }

  public function phpunit_setRawParams($params)
  {
    $this->phpunit_params = $params;
  }
}

class SimpleHttpRequestTest extends AbstractRenderTestCase
{
  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getParamWithMagicQuotesActiveReturnsNotEscapedString()
  {
    // ARRANGE
    $paramKey = 'test';
    $expectedValue = 'this is a "text" with \'quotes\'';
    $httpRequest = new MagicQuotesActiveTestableSimpleHttpRequest();
    $httpRequest->phpunit_setRawParams(array(
      $paramKey => $this->arrayAddSlashes($expectedValue))
    );

    // ACT
    $actualValue = $httpRequest->getParam($paramKey);

    // ASSERT
    $this->assertEquals($expectedValue, $actualValue);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getParamWithMagicQuotesActiveReturnsNotEscapedStringInArray()
  {
    // ARRANGE
    $paramKey = 'test';
    $expectedValue = array(
      'foo' => 'this is a "text" with \'quotes\'',
      'bar' => $this->arrayAddSlashes('text with \'double\' "quotes"'),
    );
    $httpRequest = new MagicQuotesActiveTestableSimpleHttpRequest();
    $httpRequest->phpunit_setRawParams(array(
        $paramKey => $this->arrayAddSlashes($expectedValue))
    );

    // ACT
    $actualValue = $httpRequest->getParam($paramKey);

    // ASSERT
    $this->assertEquals($expectedValue, $actualValue);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getParamWithMagicQuotesActiveReturnsDefaultAsIs()
  {
    // ARRANGE
    $paramKey = 'test';
    $expectedDefaultValue = $this->arrayAddSlashes(
      'this \'is\' a "text" with \\\'quotes\\\''
    );
    $httpRequest = new MagicQuotesActiveTestableSimpleHttpRequest();

    // ACT
    $actualValue = $httpRequest->getParam($paramKey, $expectedDefaultValue);

    // ASSERT
    $this->assertEquals($expectedDefaultValue, $actualValue);
  }

  /**
   * @param array|string
   *
   * @return array|string
   */
  protected function arrayAddSlashes($mix)
  {
    if(is_array($mix)) {
      $b = array();
      foreach($mix as $k => $v) {
        $b[$k] = $this->arrayAddSlashes($v);
      }
      return $b;
    } else {
      return addslashes($mix);
    }
  }
}
 