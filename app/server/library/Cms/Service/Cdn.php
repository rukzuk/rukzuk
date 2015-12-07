<?php
namespace Cms\Service;

use Cms\Service\Base\Plain as PlainServiceBase;
use Seitenbau\Thumbnail;
use Seitenbau\Registry as Registry;

/**
 * Cdn service
 *
 * @package      Cms
 * @subpackage   Service
 */
class Cdn extends PlainServiceBase
{

  public function __construct()
  {

  }

  /**
   * Erstellt ein Thumbnail der angegebenen Orginaldatei
   *
   * @param string $originFilePath
   * @param string $cacheFilePath
   * @param int $width
   * @param int $height
   * @return Thumbnail|false
   */
  public function getThumbnail($originFilePath, $cacheFilePath, $width, $height)
  {
    try {
      return new Thumbnail($originFilePath, $cacheFilePath, $width, $height);
    } catch (\Exception $e) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $e, \Seitenbau\Log::ERR);
      return false;
    }
  }
}
