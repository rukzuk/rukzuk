<?php


namespace Cms\Creator\Adapter\DynamicCreator;

use Render\MediaCDNHelper\MediaCache;

class DynamicCreatorMediaCache extends MediaCache
{
  /**
   * @param string $websiteId
   *
   * @return string
   */
  protected function getCacheDirectory($websiteId)
  {
    return $this->getCacheBaseDirectory() . DIRECTORY_SEPARATOR;
  }
}
