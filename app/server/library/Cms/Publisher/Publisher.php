<?php
namespace Cms\Publisher;

use Seitenbau\Registry as Registry;

/**
 * Publisher Interface
 *
 * @package      Cms
 * @subpackage   Publisher
 */
abstract class Publisher
{
  protected $config = array();

  public function __construct()
  {
    $config = Registry::getConfig()->publisher->toArray();
    $this->checkRequiredOptions($config);
    $this->config = $config;
    $this->setOptions();
  }

  /**
   * @param string|null $type
   *
   * @return array
   */
  public function getDefaultPublishData($type = null)
  {
    if (empty($type)) {
      $type = $this->config['defaultPublish']['type'];
    }
    $publishData = $this->getDefaultPublishDataByType($type);
    $publishData['type'] = $type;
    return $publishData;
  }

  /**
   * @param $type
   *
   * @return array
   */
  protected function getDefaultPublishDataByType($type)
  {
    if (!isset($this->config['defaultPublish']['config'][$type])) {
      return array();
    }
    return  $this->config['defaultPublish']['config'][$type];
  }

  /**
   * @param  string $websiteId
   * @param  string $publishingId
   * @param  string $publishingFilePath
   * @param  array  $publishConfig
   * @param  array  $serviceUrls
   *
   * @return \Cms\Data\PublisherStatus
   * @throws \Cms\Exception
   */
  final public function publish($websiteId, $publishingId, $publishingFilePath, $publishConfig, $serviceUrls)
  {
    return $this->publishImplementations($websiteId, $publishingId, $publishingFilePath, $publishConfig, $serviceUrls);
  }


  /**
   * @param  string $websiteId
   * @param  string $publishingId
   * @param  array  $publishConfig
   * @param  array  $serviceUrls
   *
   * @return \Cms\Data\PublisherStatus
   * @throws \Cms\Exception
   */
  final public function getStatus($websiteId, $publishingId, $publishConfig, $serviceUrls)
  {
    return $this->getStatusImplementations($websiteId, $publishingId, $publishConfig, $serviceUrls);
  }

  /**
   * @param  string $websiteId
   * @param  array  $publishConfig
   *
   * @throws \Cms\Exception
   */
  final public function delete($websiteId, $publishConfig)
  {
    $this->deleteImplementations($websiteId, $publishConfig);
  }

  /**
   * Get full publish configuration
   *
   * @param  \Cms\Data\Website $website
   *
   * @return array
   */
  public function getPublishData($website)
  {
    $publishData = json_decode($website->getPublish(), true);

    // use default publish data
    if (!is_array($publishData) || count($publishData) <= 0) {
      $publishData = $this->getDefaultPublishData();
    }

    // this should not be overwritten by user input
    $publishData['shortId'] = $website->getShortId();

    // default config from configuration files
    $publishDefaultData = $this->getDefaultPublishData($publishData['type']);

    return array_replace_recursive($publishDefaultData, $publishData);
  }

  /**
   * set options only for the given implementation
   *
   * @return array
   */
  abstract public function getSupportedPublishTypes();

  /**
   * Returns the live url (based on the publish mode and the provided data)
   * http://an.example.com/your/site
   *
   * @param \Cms\Data\Website $website
   * @param array             $publishData
   *
   * @return string
   */
  abstract public function getLiveUrl($website, $publishData);

  /**
   * Internal Live Domain (e.g. ef3sbae.zuk.io)
   *
   * @param \Cms\Data\Website $website
   *
   * @return string
   */
  abstract public function getInternalLiveUrl($website);

  /**
   * @param  string $websiteId
   * @param  string $publishingId
   * @param  string $publishingFilePath
   * @param  array  $publishConfig
   * @param  array  $serviceUrls
   *
   * @return \Cms\Data\PublisherStatus
   * @throws \Cms\Exception
   */
  abstract protected function publishImplementations($websiteId, $publishingId, $publishingFilePath, $publishConfig, $serviceUrls);

  /**
   * @param  string $websiteId
   * @param  string $publishingId
   * @param  array  $publishConfig
   * @param  array  $serviceUrls
   *
   * @return \Cms\Data\PublisherStatus
   * @throws \Cms\Exception
   */
  abstract protected function getStatusImplementations($websiteId, $publishingId, $publishConfig, $serviceUrls);


  /**
   * @param  string $websiteId
   * @param  array  $publishConfig
   *
   * @throws \Cms\Exception
   */
  abstract protected function deleteImplementations($websiteId, $publishConfig);

  /**
   * set options only for the given implementation
   */
  abstract protected function setOptions();

  /**
   * checks the options for the given implementation
   */
  abstract protected function checkRequiredOptions(array $config = array());
}
