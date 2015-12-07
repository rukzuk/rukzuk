<?php
namespace Cms\Service\Media;

use Cms\Exception as CmsException;
use Cms\Service\Base\Plain as PlainServiceBase;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as Log;

/**
 * Media File service
 *
 * @package      Cms
 * @subpackage   Service
 */
class File extends PlainServiceBase
{
  /**
   * @var string
   */
  private $mediaDirectory;

  /**
   * @param  string $mediaDirectory
   * @throws Cms\Exception
   */
  public function __construct($mediaDirectory)
  {
    $this->mediaDirectory = $mediaDirectory;
    if (!is_dir($mediaDirectory)) {
      $exceptionMessage = sprintf(
          "Media directory '%s' doesn't exist",
          $mediaDirectory
      );
      throw new \InvalidArgumentException($exceptionMessage);
    }
  }
  /**
   * @param  string $websiteId
   * @param  string $name
   * @return boolen
   */
  public function delete($websiteId, $name)
  {
    $deletableFilename = $this->mediaDirectory
      . DIRECTORY_SEPARATOR . $websiteId
      . DIRECTORY_SEPARATOR . $name;
    
    if (file_exists($deletableFilename)) {
      return unlink($deletableFilename);
    }

    return false;
  }
  /**
   * @param string $sourceWebsiteId
   * @param string $destinationWebsiteId
   * @return boolean
   */
  public function copyMediaFileToNewWebsite($sourceWebsiteId, $destinationWebsiteId)
  {
    $sourceMediaDirectory = $this->mediaDirectory
      . DIRECTORY_SEPARATOR . $sourceWebsiteId;
    
    if (!is_dir($sourceMediaDirectory)) {
      $exceptionMessage = sprintf(
          "Source media directory '%s' doesn't exist",
          $sourceMediaDirectory
      );
      throw new \InvalidArgumentException($exceptionMessage);
    }

    $destinationMediaDirectoryId = $this->makeMediaWebsiteDirectory(
        $destinationWebsiteId
    );

    if (is_dir($destinationMediaDirectoryId)) {
      $directoryIterator = new \DirectoryIterator($sourceMediaDirectory);

      foreach ($directoryIterator as $iterator) {
        if (!$iterator->isDot() && $iterator->isFile()) {
          $sourceFile = $iterator->getPathname();
          $destinationFile = $destinationMediaDirectoryId
            . DIRECTORY_SEPARATOR . $iterator->getFilename();

          copy($sourceFile, $destinationFile);
        }
      }
      return true;
    }
    return false;
  }
  /**
   * @param  string $websiteId
   * @return string The created or existing media directory
   */
  public function makeMediaWebsiteDirectory($websiteId)
  {
    $mediaWebsiteDirectory = $this->mediaDirectory
      . DIRECTORY_SEPARATOR . $websiteId;

    if (!is_dir($mediaWebsiteDirectory)) {
      if (mkdir($mediaWebsiteDirectory)) {
        return $mediaWebsiteDirectory;
      }
    }
    return $mediaWebsiteDirectory;
  }
  /**
   * @param  string $file
   * @param  string $mediaId
   * @return string The md5 hashed file
   */
  public function hashFilename($file, $mediaId)
  {
    $fileExtension = $this->getExtensionFromFilename($file);
    $filePath = dirname($file);

    $md5HashedFilename = md5($mediaId) .  '.' . $fileExtension;
    $md5HashedFile = $filePath . DIRECTORY_SEPARATOR . $md5HashedFilename;
    if (rename($file, $md5HashedFile)) {
      return $md5HashedFile;
    }
    return null;
  }
  /**
   * @param  string $filename
   * @return string
   */
  private function getExtensionFromFilename($filename)
  {
    return substr(strrchr($filename, '.'), 1);
  }
}
