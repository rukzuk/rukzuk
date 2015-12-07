<?php
namespace Cms\Service\Media;

use Cms\Exception as CmsException;
use Cms\Service\Base\Plain as PlainServiceBase;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as Log;

/**
 * Cache
 *
 * @package      Cms
 * @subpackage   Service
 */
class Cache extends PlainServiceBase
{
  /**
   * @var string
   */
  private $cacheDirectory;

  /**
   * @param  string $cacheDirectory
   * @throws Cms\Exception
   */
  public function __construct($cacheDirectory)
  {
    $this->cacheDirectory = $cacheDirectory;
    if (!is_dir($cacheDirectory)) {
      $exceptionMessage = sprintf(
          "Cache directory '%s' doesn't exist",
          $cacheDirectory
      );
      throw new \InvalidArgumentException($exceptionMessage);
    }
  }
  /**
   * @param string $websiteId
   * @param string $name
   * @return boolean
   */
  public function delete($websiteId, $name)
  {
    $nameParts = explode('.', $name);
    $fileExtension = end($nameParts);
    $fileBasename = array_shift($nameParts);

    $websiteIdCacheDirectory = $this->cacheDirectory
      .  DIRECTORY_SEPARATOR .  $websiteId;

    if (is_dir($websiteIdCacheDirectory)) {
      $directoryIterator = new \DirectoryIterator($websiteIdCacheDirectory);

      foreach ($directoryIterator as $fileInfo) {
        if (!$fileInfo->isDot()) {
          $matchPosition = strpos(
              $fileInfo->getBasename('.' . $fileExtension),
              $fileBasename
          );
          if ($matchPosition !== false) {
            $deletableFilename = $websiteIdCacheDirectory
              . DIRECTORY_SEPARATOR . $fileInfo->getFilename();

            if (file_exists($deletableFilename)) {
              unlink($deletableFilename);
            }
          }
        }
      }
      
      return true;
    }

    return false;
  }
}
