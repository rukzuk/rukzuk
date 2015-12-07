<?php


namespace Cms\Dao\Base;

/**
 * @package Cms\Dao
 */
abstract class AbstractSourceItem
{
  const SOURCE_LOCAL = 'local';
  const SOURCE_REPOSITORY = 'repo';
  const SOURCE_UNKNOWN = 'unknown';

  /**
   * @var string
   */
  private $directory;

  /**
   * @var string
   */
  private $url;

  /**
   * @var string
   */
  private $type;

  /**
   * @var bool
   */
  private $readonly;

  /**
   * @var bool
   */
  private $exportable;

  /**
   * @var string
   */
  private $cacheKey;

  /**
   * @param string  $directory
   * @param string  $url
   * @param string  $type
   * @param boolean $readonly
   * @param boolean $exportable
   */
  public function __construct($directory, $url, $type, $readonly, $exportable)
  {
    $this->directory = $directory;
    $this->url = $url;
    $this->type = $type;
    $this->readonly = $readonly;
    $this->exportable = $exportable;
  }

  /**
   * @return string
   */
  public function getDirectory()
  {
    return $this->directory;
  }

  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }

  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @return boolean
   */
  public function isReadonly()
  {
    return (bool)$this->readonly;
  }

  /**
   * @return boolean
   */
  public function isExportable()
  {
    return $this->exportable;
  }

  /**
   * @return string
   */
  public function getCacheKey()
  {
    if (isset($this->cacheKey)) {
      return $this->cacheKey;
    }
    $this->cacheKey = md5(json_encode($this->toArray()));
    return $this->cacheKey;
  }

  /**
   * @return string
   */
  public function resetCacheKeyCache()
  {
    $this->cacheKey = null;
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'directory' => $this->getDirectory(),
      'url' => $this->getUrl(),
      'type' => $this->getType(),
      'readonly' => $this->isReadonly(),
      'exportable' => $this->isExportable(),
    );
  }
}
