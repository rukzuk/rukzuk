<?php


namespace Test\Rukzuk\Mock\Seitenbau;


use Seitenbau\Http;

class HttpMock extends Http
{
  protected $mock_methodCalls = array();
  protected $mock_methodReturns = array();

  public function callUrl($host, $request, &$responseHeaders, &$responseBody, $method=self::METHOD_POST)
  {
    $this->mock_methodCalls[] = array(__FUNCTION__, $host, $request, $responseHeaders, $responseBody, $method);

    $mockReturn = $this->mock_getNextMethodReturn(__FUNCTION__);

    if (isset($mockReturn['data']['responseHeaders'])) {
      $responseHeaders = $mockReturn['data']['responseHeaders'];
    }
    if (isset($mockReturn['data']['responseBody'])) {
      $responseBody = $mockReturn['data']['responseBody'];
    }
    return $mockReturn['returnValue'];
  }

  /**
   * @return array
   */
  public function mock_getMethodCalls()
  {
    return $this->mock_methodCalls;
  }

  /**
   * @param string $method
   * @param mixed  $returnValue
   * @param array  $data
   */
  public function mock_addMethodReturn($method, $returnValue, array $data = array())
  {
    if (!isset($this->mock_methodReturns[$method])) {
      $this->mock_methodReturns[$method] = array();
    }
    $this->mock_methodReturns[$method][] = array(
      'returnValue' => $returnValue,
      'data' => $data,
    );
  }

  /**
   * @param string $methodName
   *
   * @return string
   */
  protected function mock_getNextMethodReturn($methodName)
  {
    if (isset($this->mock_methodReturns[$methodName]) && is_array($this->mock_methodReturns[$methodName])) {
      $mockReturn = array_shift($this->mock_methodReturns[$methodName]);
    } else {
      $mockReturn = array(
        'returnValue' => null,
        'data' => array(),
      );
    }
    return $mockReturn;
  }
} 