<?php
namespace Test\Seitenbau\Screenshot\Type;

/**
 * External rukzuk screenshot implementierung test class
 *
 * @package      Seitenbau
 * @subpackage   Screenshot
 */

class ExternalrukzukserviceMock extends \Seitenbau\Screenshot\Type\Externalrukzukservice
{
  static protected $testResponseBodys = null;
  static protected $testResponseCodes = null;
  static protected $testToken = null;

  /**
   */
  static public function setTestResponseBodys($testResponseBodys)
  {
    if (!is_array($testResponseBodys)) {
      $testResponseBodys = array($testResponseBodys);
    }
    self::$testResponseBodys = $testResponseBodys;
  }

  /**
   */
  static public function setTestResponseCodes($testResponseCodes)
  {
    if (!is_array($testResponseCodes)) {
      $testResponseCodes = array($testResponseCodes);
    }
    self::$testResponseCodes = $testResponseCodes;
  }

  /**
   */
  static public function setTestToken($testToken)
  {
    self::$testToken = $testToken;
  }
  
  /**
   */
  static public function clearTestData()
  {
    self::$testResponseBodys = null;
    self::$testResponseCodes = null;
  }

  
  /**
   * @return string 
   */
  protected function getToken()
  {
    if (isset(self::$testToken)) {
      return self::$testToken;
    }
    return parent::getToken();
  }

  /**
   */
  protected function callUrl($host, $request, &$responseBody)
  {
    $responseCode = null;
    if (isset(self::$testResponseCodes)) {
      if (is_array(self::$testResponseCodes) && count(self::$testResponseCodes) > 0) {
        $responseCode = array_shift(self::$testResponseCodes);
      }
    }

    if (isset(self::$testResponseBody)
      && is_array(self::$testResponseBodys) && count(self::$testResponseBodys) > 0)
    {
      $responseBody = array_shift(self::$testResponseBodys);
    } else {
      $responseBody = json_encode(array(
        'responseCode'  => $responseCode,
        'host'          => $host,
        'request'       => $request,
      ));
    }
    
    return $responseCode;
  }
}