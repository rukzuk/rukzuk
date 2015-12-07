<?php
namespace Seitenbau;

use Seitenbau\ExternalRukzukService\Exception as SbExternalRukzukServiceException;
use Seitenbau\Http as SbHttp;

/**
 * class to handel calls to the external rukzuk services
 *
 * @package      Seitenbau
 */

class ExternalRukzukService
{
  private $serviceHosts               = null;
  private $maxServiceHosts            = 1;
  private $endpoints                  = null;
  private $defaultAcceptedStatusCodes = array(200);
  private $responseBody               = null;
  private $request                    = null;
  private $responseHeaders            = null;
  private $statusCode                 = null;
  private $replaceParams              = array();

  /**
   * @param array $config
   * @return ExternalRukzukService
   */
  public static function getInstance(array $config)
  {
    $className = __CLASS__;
    return new $className($config);
  }
  
  protected function __construct(array $config)
  {
    $this->checkRequiredOptions($config);
    $this->setOptions($config);
  }

  /**
   * @return string
   */
  public function getLastResponseBody()
  {
    return $this->responseBody;
  }

  /**
   * @return array
   */
  public function getLastRequest()
  {
    return $this->request;
  }

  /**
   * @return string
   */
  public function getLastStatusCode()
  {
    return $this->statusCode;
  }

  /**
   * @param  string     $action
   * @param  array      $serviceParams
   * @param  array      $replaceParams
   * @return string
   */
  public function callService($action, $serviceParams, $replaceParams = array())
  {
    $this->responseBody = null;
    $this->request = null;
    $this->responseHeaders = null;
    $this->statusCode = null;
    
    $serviceHosts = $this->getShuffledServiceHosts();
    $this->request = $this->getActionRequest($action);
    $this->setServiceParamsToRequest($this->request, $serviceParams);
    $this->replaceParamsInRequest($this->request, array_merge($this->replaceParams, $replaceParams));
    
    $statusCode = null;
    foreach ($serviceHosts as $nextHost) {
      $this->request['host'] = $nextHost;
      $statusCode = $this->callUrl();
      if (isset($statusCode) && in_array($statusCode, $this->request['acceptedStatusCodes'])) {
        break;
      }
    }
    return $statusCode;
  }

  /**
   * @return array
   */
  protected function getShuffledServiceHosts()
  {
    $allServiceHosts = array();
    if (is_array($this->serviceHosts)) {
      $allServiceHosts = $this->serviceHosts;
      shuffle($allServiceHosts);
      if (isset($this->maxServiceHosts)) {
        $maxHosts = intval($this->maxServiceHosts);
        if ($maxHosts > 0 && count($allServiceHosts) > $maxHosts) {
          $allServiceHosts = array_slice($allServiceHosts, 0, $maxHosts);
        }
      }
    }
    return $allServiceHosts;
  }

  /**
   * @param  string $action
   * @throws SbExternalRukzukServiceException
   * @return array
   */
  protected function getActionRequest($action)
  {
    if (!isset($this->endpoints[$action])) {
      throw new SbExternalRukzukServiceException('unknown action');
    }
    return $this->endpoints[$action];
  }

  /**
   * @param $request
   * @param $serviceParams
   * @return array
   */
  protected function setServiceParamsToRequest(&$request, $serviceParams)
  {
    if (!isset($request['params']) || !is_array($request['params'])) {
      $request['params'] = array();
    }
    if (is_array($serviceParams)) {
      $request['params'] = array_merge($request['params'], $serviceParams);
    }
  }
  
  /**
   * @params  array   $request
   * @params  array   $replaceParams
   */
  protected function replaceParamsInRequest(&$request, $replaceParams)
  {
    $search   = array_keys($replaceParams);
    $replace  = array_values($replaceParams);
    if (isset($request['url'])) {
      $replaceUrlEncoded = $replace;
      array_walk_recursive($replaceUrlEncoded, function (&$item, $key) {
        if (is_string($item)) {
          $item = urlencode($item);
        }
      });
      $this->doReplaceParams($request['url'], $search, $replaceUrlEncoded);
    }
    if (isset($request['params'])) {
      $this->doReplaceParams($request['params'], $search, $replace);
    }
  }
  
  /**
   * @params  array   $data
   * @params  array   $replaceParams
   */
  protected function doReplaceParams(&$data, $search, $replace)
  {
    if (is_array($data)) {
      foreach ($data as $dataKey => &$dataValue) {
        $this->doReplaceParams($dataValue, $search, $replace);
      }
    } elseif (is_string($data)) {
      $data = str_replace($search, $replace, $data);
    }
  }
  
  /**
   * @return string
   */
  protected function callUrl()
  {
    $this->responseBody = $this->responseHeaders = null;
    $http = $this->getHttpClient();
    $this->statusCode = $http->callUrl(
        $this->request['host'],
        $this->request,
        $this->responseHeaders,
        $this->responseBody,
        (isset($this->request['method']) ? $this->request['method'] : $http::METHOD_POST)
    );
    if (!in_array($this->statusCode, $this->request['acceptedStatusCodes'])) {
      $logger = \Seitenbau\Registry::getLogger();
      if ($logger instanceof \Seitenbau\Logger) {
        $output = preg_replace('#^.*<\s*body.*?>\s*#s', '', $this->responseBody);
        $output = preg_replace('#\s*<\s*/\s*body\s*>.*$#s', '', $output);
        if (mb_strlen($output) > 1024) {
          $output = mb_substr($output, 0, 1024)."\n...";
        }
        $logId = $logger->createLogId();
        $logger->log(__METHOD__, __LINE__, "Url '".$this->request['host'].$this->request['url']."' (Status: ".$this->statusCode.")", \Seitenbau\Log::WARN, $logId);
        $logger->log(__METHOD__, __LINE__, "Body:\n".trim($output), \Seitenbau\Log::INFO, $logId);
      }
      return false;
    }
    return $this->statusCode;
  }


  /**
   * @param array $config
   * @throws SbExternalRukzukServiceException
   */
  protected function checkRequiredOptions(array $config)
  {
    if (!isset($config['hosts']) && !is_array($config['hosts'])) {
      throw new SbExternalRukzukServiceException('Configuration must have keys for "hosts" that defined the available rukzuk service hosts');
    }
    if (!isset($config['endpoint']) && !is_array($config['endpoint'])) {
      throw new SbExternalRukzukServiceException('Configuration must have key "endpoint" that defined the url for the service enpoints');
    }
    foreach ($config['endpoint'] as $endpointName => $endpoint) {
      if (!isset($endpoint['url']) && is_string($endpoint['url'])) {
        throw new SbExternalRukzukServiceException(sprintf(
            'Configuration must have key "endpoint.%s.url" that defined the url for the status-service enpoints',
            $endpointName
        ));
      }
    }
  }

  /**
   * @param array $config
   */
  protected function setOptions(array $config)
  {
    $this->serviceHosts = $config['hosts'];
    $this->endpoints    = $config['endpoint'];
    foreach ($this->endpoints as &$request) {
      if (!isset($request['acceptedStatusCodes']) || !is_array($request['acceptedStatusCodes'])) {
        $request['acceptedStatusCodes'] = $this->defaultAcceptedStatusCodes;
      }
    }
    if (isset($config['maxHosts'])) {
      $this->maxServiceHosts  = (int)$config['maxHosts'];
    }
    $this->replaceParams = array();
    if (isset($config['replace']) && is_array($config['replace'])) {
      foreach ($config['replace'] as $search => $replace) {
        $this->replaceParams['{{'.$search.'}}'] = $replace;
      }
    }
  }

  /**
   * @return \Seitenbau\Http
   */
  protected function getHttpClient()
  {
    return new \Seitenbau\Http();
  }
}
