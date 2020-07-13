<?php
namespace Cms\Publisher\Type;

use Cms\Publisher\Publisher as PublisherBase;
use Seitenbau\Json as SbJson;

/*
 * Use Externalrukzukservies for external and Standalone for internal
 */
class Hybrid extends PublisherBase
{
  // no config required
  const VERSION = 1;

  /**
   * The actual impl
   * @var PublisherBase
   */
  private $impl = null;


  private function getImpl($publishType)
  {
    if ($this->impl === null) {

      $this->impl = $publishType === 'internal' ? new Standalone() : new Externalrukzukservice();
    }
    return $this->impl;
  }

  private function getPublishDataByWebsite($website)
  {
    return SbJson::decode($website->getPublish());
  }

  private function getTypeFromPublishData($publishData)
  {
    // use default publish data
    if (!is_array($publishData) || count($publishData) <= 0) {
      $publishData = $this->getDefaultPublishData();
    }

    return $publishData['type'];
  }

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
   * @return array
   */
  public function getPublishData($website) {
    return $this->getImpl($this->getTypeFromPublishData($this->getPublishDataByWebsite($website)))->getPublishData($website);
  }

  /**
   * @inheritDoc
   */
  public function getLiveUrl($website, $publishData)
  {
    return $this->getImpl($this->getTypeFromPublishData($publishData))->getLiveUrl($website, $publishData);
  }

  /**
   * @inheritDoc
   */
  public function getInternalLiveUrl($website)
  {
    // internal stuff only applies to 'internal' type so standalone in our case
    $standalone = new Standalone();
    $standalone->getInternalLiveUrl($website);
  }

  /**
   * @inheritDoc
   */
  protected function publishImplementations($websiteId, $publishingId, $publishingFilePath, $publishConfig, $serviceUrls)
  {
    return $this->getImpl($this->getTypeFromPublishData($publishConfig))->publishImplementations($websiteId, $publishingId, $publishingFilePath, $publishConfig, $serviceUrls);
  }

  /**
   * @inheritDoc
   */
  protected function getStatusImplementations($websiteId, $publishingId, $publishConfig, $serviceUrls)
  {
    return $this->getImpl($this->getTypeFromPublishData($publishConfig))->getStatusImplementations($websiteId, $publishingId, $publishConfig, $serviceUrls);
  }

  /**
   * @inheritDoc
   */
  protected function deleteImplementations($websiteId, $publishConfig)
  {
    return $this->getImpl($this->getTypeFromPublishData($publishConfig))->deleteImplementations($websiteId, $publishConfig);
  }

  /**
   * @inheritDoc
   */
  protected function setOptions()
  {
    // empty
  }

  /**
   * @inheritDoc
   */
  protected function checkRequiredOptions(array $config = array())
  {
    return true;
  }
}
