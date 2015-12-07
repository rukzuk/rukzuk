<?php
namespace Seitenbau\Screenshot\Type;

use Seitenbau\Screenshot\Base as ScreenshotBase;
use Seitenbau\Screenshot as SBScreenshot;

/**
 * External rukzuk screenshot implementierung
 *
 * Service status code:
 *   200: image returned
 *   202: accepted task
 *   400: error at params
 *   404: shooterId not exist
 *   405: post/get error
 *   423: shoot in progess
 *
 * @package      Seitenbau
 * @subpackage   Screenshot
 */

class Externalrukzukservice extends ScreenshotBase
{
  const CONFIG_SELECTION    = 'externalrukzukservice';
  
  const ACTION_STATUS       = 'status';
  const ACTION_TRIGGER      = 'trigger';
  
  const STATUS_SUCCESS      = 'SUCCESS';
  const STATUS_IN_PROGRESS  = 'PROGRESS';
  const STATUS_NOT_FOUND    = 'NOT_FOUND';
  const STATUS_ERROR        = 'ERROR';

  /**
   * @var array
   */
  private $options = array();
  
  /**
   * @var integer
   */
  private $callTimeout = 5;

  /**
   * @var integer
   */
  private $callMaxRedirects = 5;

  /**
   * @var integer
   */
  private $acceptedCodes = array(200, 202, 404, 423);

  /**
   * create screenshot
   *
   * @param string $shootId
   * @param \Cms\Business\Screenshot\Url $screenshotUrl
   * @param string $destinationFile
   * @return boolean
   */
  protected function shootImplementation($shootId, $screenshotUrl, $destinationFile)
  {
    $statusResult = $this->getShootStatusImplementation($shootId, $destinationFile);
    switch ($statusResult) {
      case self::STATUS_SUCCESS:
          // screenshot successfully created
            return true;
        break;
      
      case self::STATUS_NOT_FOUND:
          $triggerResult = $this->triggerShootImplementation(
              $shootId,
              (string)$screenshotUrl,
              $destinationFile
          );
          if (self::STATUS_SUCCESS === $triggerResult) {
            // screenshot successfully created
            return true;
          }
            break;
    }
    
    // screenshot not created
    return false;
  }

  /**
   * get screenshot task status
   *
   * @param string $shootId
   * @param string $destinationFile
   * @return boolean
   */
  protected function getShootStatusImplementation($shootId, $destinationFile)
  {
    $params = $this->options['status'];
    $params['id']     = $shootId;
    $params['token']  = $this->getToken();

    $responseBody = null;
    $responseCode = $this->callService(self::ACTION_STATUS, $params, $responseBody);
    
    switch ($responseCode) {
      case 200:
          $this->writeImage($destinationFile, $responseBody);
            return self::STATUS_SUCCESS;
        break;
      
      case 423:
            return self::STATUS_IN_PROGRESS;
        break;
      
      case 404:
            return self::STATUS_NOT_FOUND;
        break;
    }
    
    $this->setLastError($responseBody);
    return self::STATUS_ERROR;
  }

  /**
   * create screenshot task
   *
   * @param string $shootId
   * @param string $url
   * @param string $destinationFile
   * @return boolean
   */
  protected function triggerShootImplementation($shootId, $url, $destinationFile)
  {
    $params = $this->options['trigger'];
    $params['id']     = $shootId;
    $params['url']    = $url;
    $params['token']  = $this->getToken();
    
    $responseBody = null;
    $responseCode = $this->callService(self::ACTION_TRIGGER, $params, $responseBody);

    switch ($responseCode) {
      case 200:
          $this->writeImage($destinationFile, $responseBody);
            return self::STATUS_SUCCESS;
        break;
      
      case 202:
      case 423:
            return self::STATUS_IN_PROGRESS;
        break;
    }

    $this->setLastError($responseBody);
    return self::STATUS_ERROR;
  }
  
  /**
   * @return string
   */
  protected function getToken()
  {
    return 'eyJkYXRhIjogImV5SnBibk4wWVc1alpTSTZJQ0lxSWl3Z0ltTnlaV0YwWldRaU9pQXhNell4TkRVM09EVXhmUT09XG4iLCAic2lnbiI6ICJKQnVtVlR2Z09kSnZPT1V4TGdEYmVFTnpWMkNIa0VIbWt4MkRNNENoZGRLYndPd2FvVThmWTBCb1B6U1l6b0RpaUVHdEh6SUg5WHFJLzQ1NjhtWGllanNTekJPWFgvY29ZU1dscGVrUDRNNGxVWnlBNTVqY0NFMXMzb0hUMWM2QjJqK0txb2pOVFB0REliQWp5Y3pXNTd5S3hMb2o0RkVhbDBuMHMyMWM4dVRFTnpKMWloakF0VXZDYkIrSXlTUUpDTGdJWnhDWGlGcEY2eGl6MWprUGtzT2F5ZS9ZeWZ4VDlsVkRBVWlyZmtvY1V1MzN2djQxNDRnaTZJOEZNYzRCbFRtdVZ0U1R3MTZGWWk5UkpJMTFxMWp0ZGx3UHhHSmcvZWUvcVRlMVFBblk4ZlZhRFM0ZTJrYTE0ZThYdTMxUGI1djNwcEtsdm9XK1ZUd3BRajhUZ2c9PSJ9';
  }
  
  /**
   * @param  string $action
   * @param  array  $serviceParams
   * @param  stringref  $responseBody
   * @return string
   */
  protected function callService($action, $serviceParams, &$responseBody)
  {
    $serviceHosts = $this->getShuffledServiceHosts();
    $request = $this->getActionUrl($action);
    $request['params'] = $serviceParams;
    
    $statusCode = null;
    foreach ($serviceHosts as $nextHost) {
      $statusCode = $this->callUrl($nextHost, $request, $responseBody);
      if (isset($statusCode) && in_array($statusCode, $this->acceptedCodes)) {
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
    if (isset($this->config[self::CONFIG_SELECTION]['hosts'])) {
      $allServiceHosts = $this->config[self::CONFIG_SELECTION]['hosts'];
      shuffle($allServiceHosts);
      if (isset($this->config[self::CONFIG_SELECTION]['maxHosts'])) {
        $maxHosts = intval($this->config[self::CONFIG_SELECTION]['maxHosts']);
        if ($maxHosts > 0 && count($allServiceHosts) > $maxHosts) {
          $allServiceHosts = array_slice($allServiceHosts, 0, $maxHosts);
        }
      }
    }
    return $allServiceHosts;
  }
  
  /**
   * @param  string $action
   * @param  array  $serviceParams
   * @return array
   */
  protected function getActionUrl($action)
  {
    switch($action) {
      case self::ACTION_STATUS:
            return $this->config[self::CONFIG_SELECTION]['endpoint']['status'];
        break;
      case self::ACTION_TRIGGER:
            return $this->config[self::CONFIG_SELECTION]['endpoint']['trigger'];
        break;
    }

    $this->setLastError('unknown action');
    throw new \Exception('unknown action');
  }

  /**
   * @param  string     $host
   * @param  array      $request
   * @param  stringref  $responseBody
   * @return string
   */
  protected function callUrl($host, $request, &$responseBody)
  {
    $responseBody = $responseHeaders = null;
    $http = $this->getHttpClient();
    $responseCode = $http->callUrl($host, $request, $responseHeaders, $responseBody, $http::METHOD_POST);
    
    // check for content-type on success
    if ($responseCode == 200) {
      $responseCode = -1;
      if (isset($responseHeaders) && is_array($responseHeaders)) {
        foreach ($responseHeaders as $header) {
          if (preg_match('#Content-Type: image/.+#', $header)) {
            $responseCode = 200;
            break;
          }
        }
      }
    }

    if (isset($responseCode) && !in_array($responseCode, $this->acceptedCodes)) {
      $logger = \Seitenbau\Registry::getLogger();
      if ($logger instanceof \Seitenbau\Logger) {
        $output = preg_replace('#^.*<\s*body.*?>\s*#s', '', $responseBody);
        $output = preg_replace('#\s*<\s*/\s*body\s*>.*$#s', '', $output);
        if (mb_strlen($output) > 1024) {
          $output = mb_substr($output, 0, 1024)."\n...";
        }
        $logId = $logger->createLogId();
        $logger->log(
            __METHOD__,
            __LINE__,
            sprintf(
                "Url '%s%s' (Status: %s; Error: %s)",
                $host,
                $request['url'],
                $responseCode,
                $http->getLastError()
            ),
            \Seitenbau\Log::WARN,
            $logId
        );
        $logger->log(__METHOD__, __LINE__, "Body:\n".$output, \Seitenbau\Log::INFO, $logId);
      }
    }
    
    return $responseCode;
  }

  /**
   * @param  string     $destinationFile
   * @param  stringref  $image
   * @return string
   */
  protected function writeImage($destinationFile, &$image)
  {
    return file_put_contents($destinationFile, $image);
  }

  /**
   *
   */
  protected function setOptions()
  {
    if (array_key_exists(self::CONFIG_SELECTION, $this->config)) {
      $externalServiceConfig = $this->config[self::CONFIG_SELECTION];

      if (array_key_exists(SBScreenshot::OPTIONS, $externalServiceConfig)) {
        $this->options = $externalServiceConfig[SBScreenshot::OPTIONS];
        if (!array_key_exists('status', $this->options)) {
          $this->options['status'] = array();
        }
        if (!array_key_exists('trigger', $this->options)) {
          $this->options['trigger'] = array();
        }
      }
      
      if (array_key_exists('timeout', $externalServiceConfig)) {
        $this->callTimeout = intval($externalServiceConfig['timeout']);
      }
      
      if (array_key_exists('maxRedirects', $externalServiceConfig)) {
        $this->callMaxRedirects = intval($externalServiceConfig['maxRedirects']);
      }
    }
  }

  /**
   * @param array $config
   */
  protected function checkRequiredOptions(array $config = array())
  {
    parent::checkRequiredOptions($config);

    if (array_key_exists(self::CONFIG_SELECTION, $config)) {
      $externalServiceConfig = $config[self::CONFIG_SELECTION];
      if (!\array_key_exists('hosts', $externalServiceConfig)) {
        throw new SBScreenshot\InvalidConfigException('Configuration must have keys for "hosts" that defined the available rukzuk screenshot service hosts');
      }
      if (!\array_key_exists('endpoint', $externalServiceConfig)) {
        throw new SBScreenshot\InvalidConfigException('Configuration must have key "endpoint" that defined the url for the service enpoints');
      }
      if (!\array_key_exists('status', $externalServiceConfig['endpoint'])) {
        throw new SBScreenshot\InvalidConfigException('Configuration must have key "endpoint.status" that defined the status-service request');
      }
      if (!\array_key_exists('url', $externalServiceConfig['endpoint']['status'])) {
        throw new SBScreenshot\InvalidConfigException('Configuration must have key "endpoint.status.url" that defined the url for the status-service enpoints');
      }
      if (!\array_key_exists('trigger', $externalServiceConfig['endpoint'])) {
        throw new SBScreenshot\InvalidConfigException('Configuration must have key "endpoint.trigger" that defined the trigger-service request');
      }
      if (!\array_key_exists('url', $externalServiceConfig['endpoint']['trigger'])) {
        throw new SBScreenshot\InvalidConfigException('Configuration must have key "endpoint.trigger.url" that defined the url for the trigger-service enpoints');
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
