<?php
namespace Seitenbau;

use Seitenbau\Http\HttpException as HttpException;

/**
 * class to handel http calls
 *
 * @package      Seitenbau
 */

class Http
{
  const METHOD_GET  = 'GET';
  const METHOD_POST = 'POST';
  
  const DEFAULT_TIMEOUT = 5;
  const DEFAULT_MAX_REDIRECTS = 1;

  /**
   * @var null|string
   */
  protected $lastError = null;

  /**
   * @param  string $host
   * @param  array  $request
   * @param  array  $responseHeaders
   * @param  string $responseBody
   * @param  string $method (METHOD_GET|METHOD_POST)
   *
   * @throws Http\HttpException
   * @return string
   */
  public function callUrl($host, $request, &$responseHeaders, &$responseBody, $method = self::METHOD_POST)
  {
    $this->lastError = null;

    // check request
    if (!isset($request['timeout']) || is_null($request['timeout'])) {
      $request['timeout'] = self::DEFAULT_TIMEOUT;
    }
    if (!isset($request['maxRedirects']) || is_null($request['maxRedirects'])) {
      $request['maxRedirects'] = self::DEFAULT_MAX_REDIRECTS;
    }
    $request['referer'] = $this->getReferer($request);
    
    if ($this->canCallOverCurl()) {
      return $this->callOverCurl($method, $host, $request, $responseHeaders, $responseBody);
    } elseif ($this->canCallOverStreamContext()) {
      return $this->callOverStreamContext($method, $host, $request, $responseHeaders, $responseBody);
    } else {
      throw new HttpException('error no http client installed');
    }
  }
  
  protected function canCallOverCurl()
  {
    if ($this->curlEnabled()
        && function_exists('curl_init') && function_exists('curl_setopt')
        && function_exists('curl_exec') && function_exists('curl_close')
    ) {
      return true;
    } else {
      return false;
    }
  }
  
  protected function curlEnabled()
  {
    return true;
  }

  protected function callOverCurl($method, $host, $request, &$responseHeaders, &$responseBody)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host.$request['url']);
    curl_setopt($ch, CURLOPT_TIMEOUT, $request['timeout']);
    curl_setopt($ch, CURLOPT_MAXREDIRS, $request['maxRedirects']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (isset($request['referer']) && !empty($request['referer'])) {
      curl_setopt($ch, CURLOPT_REFERER, $request['referer']);
    }
    
    if ($method == self::METHOD_GET) {
      curl_setopt($ch, CURLOPT_POST, false);
    } elseif ($method == self::METHOD_POST) {
      curl_setopt($ch, CURLOPT_POST, true);
    } else {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    
    if (isset($request['params']) && !empty($request['params']) > 0) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $request['params']);
    }

    if (isset($request['headers']) && is_array($request['headers'])) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $request['headers']);
    }
    
    $responseRawBody  = curl_exec($ch);

    $responseCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize       = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    if (curl_errno($ch)) {
      $this->lastError = curl_error($ch);
    }

    curl_close($ch);
    
    $responseHeaders  = explode("\n", mb_substr($responseRawBody, 0, $headerSize));
    $responseBody     = mb_substr($responseRawBody, $headerSize);

    return $responseCode;
  }
  
  protected function canCallOverStreamContext()
  {
    if ($this->streamContextEnabled()
        && function_exists('file_get_contents') && function_exists('stream_context_create')
    ) {
      return true;
    } else {
      return false;
    }
  }
  
  protected function streamContextEnabled()
  {
    return true;
  }
  
  protected function callOverStreamContext($method, $host, $request, &$responseHeaders, &$responseBody)
  {
    $http_scheme = (strpos($host, 'https') === 0 ? 'https' : 'http');

    $opts = array($http_scheme =>
      array(
        'method'        => 'POST',
        'timeout'       => $request['timeout'],
        'max_redirects' => $request['maxRedirects'],
        'header'        => "Accept-language: en\r\n".
                           "Content-type: application/x-www-form-urlencoded\r\n",
        'content'       => http_build_query($request['params']),
        'ignore_errors' => true
      ),
      'ssl' => array(
          'verify_peer'   => false
      )
    );
    if (isset($request['referer']) && !empty($request['referer'])) {
      $opts[$http_scheme]['header'] .= "Referer: ".$request['referer']."\r\n";
    }
    $context = stream_context_create($opts);
    $responseBody = @file_get_contents($host.$request['url'], false, $context);
    if ($responseBody === false) {
      $responseBody = '';
      $responseHeaders = array();
      return 0;
    }
    $responseHeaders = $http_response_header;
    $responseCode = 0;
    if (is_array($responseHeaders)) {
      foreach ($responseHeaders as $nextHeader) {
        if (preg_match('#HTTP/\d+\.\d+ (\d+)#', $nextHeader, $matches)) {
          $responseCode = $matches[1];
          break;
        }
      }
    }
    return $responseCode;
  }
  
  protected function getReferer($request)
  {
    if (isset($request['referer'])) {
      return $request['referer'];
    }

    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
  }

  /**
   * @return null|string
   */
  public function getLastError()
  {
    return $this->lastError;
  }
}
