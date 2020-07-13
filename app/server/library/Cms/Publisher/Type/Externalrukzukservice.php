<?php
namespace Cms\Publisher\Type;

use Cms\Publisher\Publisher as PublisherBase;
use Cms\Publisher\PublisherException as PublisherException;
use Cms\Publisher\InvalidConfigException as InvalidConfigException;
use Cms\Data\PublisherStatus as PublisherStatusData;
use Seitenbau\Json as SbJson;

/**
 * @package      Cms
 * @subpackage   Publisher
 */
class Externalrukzukservice extends PublisherBase
{
  const CONFIG_SELECTION = 'externalrukzukservice';
  
  const VERSION = 2;
  
  const ACTION_PUBLISH  = 'publish';
  const ACTION_STATUS   = 'status';
  const ACTION_DELETE   = 'delete';
  
  private $serviceHosts     = null;
  private $maxServiceHosts  = 2;
  private $endpointPublish  = null;
  private $endpointStatus   = null;
  private $endpointDelete   = null;
  private $liveHostingDomainProtocol = 'https';
  private $liveHostingDomain = '{{id}}.example.com';

  private $acceptedCodes = array(200);

  /**
   * set options only for the given implementation
   *
   * @return array
   */
  public function getSupportedPublishTypes()
  {
    return array(
      'internal',
      'external',
    );
  }

  /**
   * @param \Cms\Data\Website $website
   * @param array             $publishData
   *
   * @return string
   */
  public function getLiveUrl($website, $publishData)
  {
    // external must provide url (we can't know it)
    if ($publishData['type'] === 'external') {
      if (isset($publishData['url'])) {
        return $publishData['url'];
      }
      return '';
    }

    // default (internal)
    if (isset($publishData['cname']) &&
      $publishData['cname'] !== '') {
      return $this->liveHostingDomainProtocol . $publishData['cname'];
    }

    return $this->getInternalLiveUrl($website);
  }

  /**
   * Internal Live Domain (e.g. ef3sbae.zuk.io)
   *
   * @param \Cms\Data\Website $website
   *
   * @return string
   */
  public function getInternalLiveUrl($website)
  {
    return $this->liveHostingDomainProtocol . $this->getInternalLiveDomainName($website);
  }

  /**
   * Internal Live Domain (e.g. ef3sbae.zuk.io)
   *
   * @param \Cms\Data\Website $website
   *
   * @return string
   */
  protected function getInternalLiveDomainName($website)
  {
    $shortId = $website->getShortId();
    return str_replace('{{id}}', $shortId, $this->liveHostingDomain);
  }

  /**
   * Get full publish configuration
   * @param  \Cms\Data\Website $website
   * @return array
   */
  public function getPublishData($website)
  {
    $publishData = SbJson::decode($website->getPublish());

    // use default publish data
    if (!is_array($publishData) || count($publishData) <= 0) {
      $publishData = $this->getDefaultPublishData();
    }

    // add the website domain for live hosting (this should not be overwritten by user input)
    if ($publishData['type'] === 'internal') {
      $publishData['domain'] = $this->getInternalLiveDomainName($website);
    } else {
      // add default ports for ftp/sftp
      if (isset($publishData['protocol']) &&
        (!isset($publishData['port']) || !$publishData['port'])) {
        if ($publishData['protocol'] === 'ftp') {
          $publishData['port'] = 21;
        } elseif ($publishData['protocol'] === 'sftp') {
          $publishData['port'] = 22;
        }
      }
    }

    // default config from configuration files
    $publishDefaultData = $this->getDefaultPublishData($publishData['type']);

    return array_replace_recursive($publishDefaultData, $publishData);
  }

  /**
   * @param  string $websiteId
   * @param  string $publishingId
   * @param  string $publishingFilePath
   * @param  array $publishConfig
   * @param  array $serviceUrls
   * @throws \Cms\Publisher\PublisherException
   * @return \Cms\Data\PublisherStatus
   */
  public function publishImplementations($websiteId, $publishingId, $publishingFilePath, $publishConfig, $serviceUrls)
  {
    $params = $this->getServiceParams($websiteId, $publishConfig, $serviceUrls, array(
      'data' => $this->getPublishDataFromPublishConfig($websiteId, $publishConfig),
    ));

    $responseBody = null;
    if ($this->callService(self::ACTION_PUBLISH, $params, $responseBody) != 200) {
      throw new PublisherException("error calling external service");
    }
    return $this->getStatusImplementations($websiteId, $publishingId, $publishConfig, $serviceUrls);
  }

  /**
   * @param  string $websiteId
   * @param  string $publishingId
   * @param  array $publishConfig
   * @param  array $serviceUrls
   * @throws \Cms\Publisher\PublisherException
   * @return \Cms\Data\PublisherStatus
   */
  public function getStatusImplementations($websiteId, $publishingId, $publishConfig, $serviceUrls)
  {
    $params = $this->getServiceParams($websiteId, $publishConfig, $serviceUrls, array());

    $responseBody = null;
    if ($this->callService(self::ACTION_STATUS, $params, $responseBody) != 200) {
      throw new PublisherException("error calling external service");
    }
    $publishedStatus = new PublisherStatusData();
    $publishedStatus->setFromArray(SbJson::decode($responseBody, SbJson::TYPE_ARRAY));
    $publishedStatus->setId($publishingId);
    return $publishedStatus;
  }

  /**
   * @param  string $websiteId
   * @param  array $publishConfig
   * @throws PublisherException
   */
  protected function deleteImplementations($websiteId, $publishConfig)
  {
    $params = $this->getServiceParams($websiteId, $publishConfig, array(), array(
      'data' => $this->getPublishDataFromPublishConfig($websiteId, $publishConfig),
    ));

    $responseBody = null;
    if ($this->callService(self::ACTION_DELETE, $params, $responseBody) != 200) {
      throw new PublisherException("error calling external service with action DELETE");
    }
  }

  /**
   * @param  string $websiteId
   * @param array $publishConfig
   * @param array $serviceUrls
   * @param array $additionalParams
   * @internal param string $publishingId
   * @return array
   */
  protected function getServiceParams($websiteId, array $publishConfig, array $serviceUrls, array $additionalParams)
  {
    return array_merge($additionalParams, array(
      'client_version'  => self::VERSION,
      'token'           => $this->getPublishToken($websiteId, $publishConfig),
      'download_url'    => (isset($serviceUrls['download']) ? $serviceUrls['download'] : null),
      'status_url'      => (isset($serviceUrls['status']) ? $serviceUrls['status'] : null),
    ));
  }

  /**
   * @param string $websiteId
   * @param $publishConfig
   * @return string config as JSON
   */
  protected function getPublishDataFromPublishConfig($websiteId, $publishConfig)
  {
    return SbJson::encode($publishConfig);
  }

  /**
   * Publisher Token
   * @param string $websiteId
   * @param array $publishConfig
   * @throws \Cms\Publisher\PublisherException
   * @return string
   */
  protected function getPublishToken($websiteId, $publishConfig)
  {
    // live hosting (internal)
    if (isset($this->config[self::CONFIG_SELECTION]['tokens'][$publishConfig['type']])) {
      return $this->config[self::CONFIG_SELECTION]['tokens'][$publishConfig['type']];
    }

    throw new PublisherException('Could not determine a token for this type: ' . $publishConfig['type']);
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
    $request = $this->getActionRequest($action);
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
   * @throws \Cms\Publisher\PublisherException
   * @internal param array $serviceParams
   * @return array
   */
  protected function getActionRequest($action)
  {
    switch($action) {
      case self::ACTION_PUBLISH:
            return $this->endpointPublish;
        break;
      case self::ACTION_STATUS:
            return $this->endpointStatus;
        break;
      case self::ACTION_DELETE:
            return $this->endpointDelete;
        break;
    }
    throw new PublisherException('unknown action');
  }

  /**
   * @param  string $host
   * @param  array $request
   * @param  stringref $responseBody
   * @internal param array $serviceParams
   * @return string
   */
  protected function callUrl($host, $request, &$responseBody)
  {
    $responseBody = $responseHeaders = null;
    $http = $this->getHttpClient();
    $responseCode = $http->callUrl($host, $request, $responseHeaders, $responseBody, $http::METHOD_POST);

    if ($responseCode != 200 && !in_array($responseCode, $this->acceptedCodes)) {
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
        $logger->log(__METHOD__, __LINE__, "Body:\n".trim($output), \Seitenbau\Log::INFO, $logId);
      }
    }

    return $responseCode;
  }

  protected function setOptions()
  {
    $externalServiceConfig = $this->config[self::CONFIG_SELECTION];
    $this->serviceHosts     = $externalServiceConfig['hosts'];
    $this->endpointPublish  = $externalServiceConfig['endpoint']['publish'];
    $this->endpointStatus   = $externalServiceConfig['endpoint']['status'];
    $this->endpointDelete   = $externalServiceConfig['endpoint']['delete'];
    $this->liveHostingDomainProtocol = $externalServiceConfig['liveHostingDomainProtocol'];
    $this->liveHostingDomain = $externalServiceConfig['liveHostingDomain'];
    if (isset($this->config[self::CONFIG_SELECTION]['maxHosts'])) {
      $this->maxServiceHosts  = $this->config[self::CONFIG_SELECTION]['maxHosts'];
    }
  }

  protected function checkRequiredOptions(array $config = array())
  {
    if (!isset($config[self::CONFIG_SELECTION]) || !is_array($config[self::CONFIG_SELECTION])) {
      throw new InvalidConfigException('no configuration for external rukzuk publish service exists');
    }
    $externalServiceConfig = $config[self::CONFIG_SELECTION];
    if (!isset($externalServiceConfig['liveHostingDomainProtocol'])) {
      throw new InvalidConfigException('Configuration must have keys for "liveHostingDomainProtocol".');
    }
    if (!isset($externalServiceConfig['liveHostingDomain'])) {
      throw new InvalidConfigException('Configuration must have keys for "liveHostingDomain".');
    }
    if (!isset($externalServiceConfig['hosts']) || !is_array($externalServiceConfig['hosts'])) {
       throw new InvalidConfigException('Configuration must have keys for "hosts" that defined the available rukzuk publisher service hosts');
    }
    if (!isset($externalServiceConfig['endpoint']) || !is_array($externalServiceConfig['endpoint'])) {
      throw new InvalidConfigException('Configuration must have key "endpoint" that defined the url for the service enpoints');
    }
    if (!isset($externalServiceConfig['endpoint']['status']) || !is_array($externalServiceConfig['endpoint']['status'])) {
      throw new InvalidConfigException('Configuration must have key "endpoint.status" that defined the status-service request');
    }
    if (!isset($externalServiceConfig['endpoint']['status']['url'])) {
      throw new InvalidConfigException('Configuration must have key "endpoint.status.url" that defined the url for the status-service enpoints');
    }
    if (!isset($externalServiceConfig['endpoint']['publish']) || !is_array($externalServiceConfig['endpoint']['publish'])) {
      throw new InvalidConfigException('Configuration must have key "endpoint.publish" that defined the publish-service request');
    }
    if (!isset($externalServiceConfig['endpoint']['publish']['url'])) {
      throw new InvalidConfigException('Configuration must have key "endpoint.publish.url" that defined the url for the publish-service enpoints');
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
