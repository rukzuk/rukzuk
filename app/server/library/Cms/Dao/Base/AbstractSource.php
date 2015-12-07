<?php


namespace Cms\Dao\Base;

/**
 * @package Cms\Dao
 */
abstract class AbstractSource
{
  const SOURCE_LOCAL = 'local';
  const SOURCE_REPOSITORY = 'repo';
  const SOURCE_UNKNOWN = 'unknown';

  /**
   * @var string
   */
  private $websiteId;

  /**
   * @var array
   */
  private $sources;

  /**
   * @var string
   */
  private $cacheKey;

  /**
   * @param string       $websiteId
   * @param SourceItem[] $sources
   */
  public function __construct($websiteId, array $sources = array())
  {
    $this->websiteId = $websiteId;
    $this->initSources($sources);
  }

  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @return SourceItem[]
   */
  public function getSources()
  {
    return $this->sources;
  }

  /**
   * @return string
   */
  public function getCacheKey()
  {
    if (isset($this->cacheKey)) {
      return $this->cacheKey;
    }
    $websiteId = $this->getWebsiteId();
    $sources = $this->getSources();
    if (count($sources) <= 0) {
      return $websiteId;
    }

    $cacheString = '';
    foreach ($sources as $sourceItem) {
      $cacheString .= $sourceItem->getDirectory();
    }
    $this->cacheKey = $websiteId . '::' . md5($cacheString);
    return $this->cacheKey;
  }

  /**
   * @param SourceItem[] $sources
   */
  protected function initSources(array $sources)
  {
    $this->sources = $sources;
  }
}
