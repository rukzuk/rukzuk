<?php


namespace Cms\Publisher\Type;

use Cms\Publisher\InvalidConfigException;
use Cms\Publisher\Publisher as PublisherBase;
use Cms\Data\PublisherStatus as PublisherStatusData;
use Cms\Exception as CmsException;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;

class Standalone extends PublisherBase
{
  const CONFIG_SELECTION = 'standalone';

  const STANDALONE_PUBLISHER_WARNING_ACTION = 'STANDALONE_PUBLISHER_WARNING_ACTION';

  const VERSION = 1;

  /**
   * @var string
   */
  private $liveHostingCNameProtocol = 'http://';

  /**
   * @var string
   */
  private $liveHostingCNamePort = null;

  /**
   * @var string
   */
  private $liveHostingDirectory = null;

  /**
   * @var string
   */
  private $liveHostingWebPath = null;

  /**
   * set options only for the given implementation
   *
   * @return array
   */
  public function getSupportedPublishTypes()
  {
    return array(
      'internal'
    );
  }

  /**
   * Returns the live url (based on the publish mode and the provided data)
   * http://an.example.com/your/site
   *
   * @param \Cms\Data\Website $website
   * @param array             $publishData
   *
   * @return string
   */
  public function getLiveUrl($website, $publishData)
  {
    if (isset($publishData['cname']) && !empty($publishData['cname'])) {
      $liveUrl = $this->liveHostingCNameProtocol . $publishData['cname'];
      if (!empty($this->liveHostingCNamePort)) {
        $liveUrl .= ':'.$this->liveHostingCNamePort;
      }
      return $liveUrl;
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
    $shortId = $website->getShortId();
    return Registry::getBaseUrl() . $this->liveHostingWebPath . '/' . $shortId;
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
  protected function getStatusImplementations(
      $websiteId,
      $publishingId,
      $publishConfig,
      $serviceUrls
  ) {
    // return always finished
    $publishedStatus = new PublisherStatusData();
    $publishedStatus->setId($publishingId);
    $publishedStatus->setStatus(PublisherStatusData::STATUS_FINISHED);
    $publishedStatus->setTimestamp(time());
    return $publishedStatus;
  }

  /**
   * @param  string $websiteId
   * @param  array  $publishConfig
   *
   * @throws \Cms\Exception
   */
  protected function deleteImplementations($websiteId, $publishConfig)
  {
    $liveDirectory = realpath($this->getInternalLiveDirectory($publishConfig['shortId']));
    if (empty($liveDirectory) || !is_dir($liveDirectory)) {
      return;
    }
    if (strpos($liveDirectory, $this->liveHostingDirectory) !== 0) {
      throw new CmsException(902, __METHOD__, __LINE__, array(
        'msg' => sprintf('Cannot delete file: Access is denied (%s)', $liveDirectory),
      ));
    }
    FS::rmdir($liveDirectory);
    $this->removeAllSymlinksToLiveDirectory($liveDirectory);
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
  protected function publishImplementations(
      $websiteId,
      $publishingId,
      $publishingFilePath,
      $publishConfig,
      $serviceUrls
  ) {
    // get and create temp directory
    $tempDirectory = $this->getTempDirectory($websiteId);
    FS::rmdir($tempDirectory);
    FS::createDirIfNotExists($tempDirectory, true, 0755);

    // unzip publish file into temp directory
    $this->unzipPublishFile($publishingFilePath, $tempDirectory);

    // get writeable and cache directories
    $writeableDirectories = $this->getWriteableDirectories($tempDirectory);
    $cacheDirectories = $this->getCacheDirectories($tempDirectory);

    // get and create live directory
    $liveDirectory = $this->getInternalLiveDirectory($publishConfig['shortId']);
    FS::createDirIfNotExists($liveDirectory, true, 0755);
    $liveDirectory = realpath($liveDirectory);

    // update live directory
    $websiteDirectory = FS::joinPath($tempDirectory, 'website');
    $this->updateLive($liveDirectory, $websiteDirectory, $writeableDirectories, $cacheDirectories);

    // remove symlinks
    $this->removeAllSymlinksToLiveDirectory($liveDirectory);

    // create symlinks if necessary
    $this->createSymlinksToLiveDirectory($publishConfig, $liveDirectory);

    // remove temp directory
    FS::rmdir($tempDirectory);

    // return status
    return $this->getStatusImplementations(
        $websiteId,
        $publishingId,
        $publishConfig,
        $serviceUrls
    );
  }

  /**
   * @param string $liveDirectory
   * @param string $srcDirectory
   * @param array  $writeableDirectories
   * @param array  $cacheDirectories
   * @param string $subDirectory
   *
   * @throws CmsException
   */
  protected function updateLive(
      $liveDirectory,
      $srcDirectory,
      $writeableDirectories,
      $cacheDirectories,
      $subDirectory = ''
  ) {
    if (!is_dir($srcDirectory)) {
      throw new CmsException(902, __METHOD__, __LINE__, array(
        'msg' => sprintf('source directory %s not exists', $srcDirectory),
      ));
    }

    // create live directory
    if (!is_dir($liveDirectory)) {
      FS::createDirIfNotExists($liveDirectory, true, 0755);
    }

    // set directory rights
    if (!empty($subDirectory) && $this->isInsideDirectory($subDirectory, $writeableDirectories)) {
      // writeable
      FS::chmod($liveDirectory, 0777);
    } else {
      FS::chmod($liveDirectory, 0755);
    }

    // get info of the directories
    $liveDirInfo = $this->getDirectoryAsArray($liveDirectory);
    $srcDirInfo = $this->getDirectoryAsArray($srcDirectory);

    // delete live directories
    foreach ($liveDirInfo['directories'] as $liveSubDirectoryName => $liveSubDirectory) {
      if ($this->isInsideDirectory($subDirectory . $liveSubDirectoryName, $writeableDirectories)) {
        FS::chmod($liveSubDirectory, 0777);
        continue;
      }
      if (!isset($srcDirInfo['directories'][$liveSubDirectoryName])) {
        FS::rmdir($liveDirectory);
      }
    }

    // delete live files
    foreach ($liveDirInfo['files'] as $liveFileName => $liveFilePathName) {
      if ($this->isInsideDirectory($subDirectory . $liveFileName, $writeableDirectories)) {
        FS::chmod($liveFilePathName, 0777);
        continue;
      }
      if (!isset($srcDirInfo['files'][$liveFileName])) {
        FS::rmFile($liveFilePathName);
      }
    }

    // copy source directories
    foreach ($srcDirInfo['directories'] as $srcSubDirectoryName => $srcSubDirectory) {
      $subDirPathName = $subDirectory . $srcSubDirectoryName . DIRECTORY_SEPARATOR;
      $newLiveDirectory = FS::joinPath($liveDirectory, $srcSubDirectoryName);
      $this->updateLive(
          $newLiveDirectory,
          $srcSubDirectory,
          $writeableDirectories,
          $cacheDirectories,
          $subDirPathName
      );
    }

    // copy source files
    foreach ($srcDirInfo['files'] as $srcFileName => $srcFilePathName) {
      $newLiveFilePathName = FS::joinPath($liveDirectory, $srcFileName);
      FS::copyFile($srcFilePathName, $newLiveFilePathName);
      if ($this->isInsideDirectory($subDirectory . $srcFileName, $writeableDirectories)) {
        FS::chmod($newLiveFilePathName, 0777);
      } else {
        FS::chmod($newLiveFilePathName, 0755);
      }
    }
  }

  /**
   * @param string $directory
   *
   * @return array
   */
  protected function getWriteableDirectories($directory)
  {
    $content = FS::readContentFromFile(FS::joinPath($directory, 'writeable.txt'));
    return explode("\n", $content);
  }

  /**
   * @param string $directory
   *
   * @return array
   */
  protected function getCacheDirectories($directory)
  {
    $content = FS::readContentFromFile(FS::joinPath($directory, 'cache.txt'));
    return explode("\n", $content);
  }

  /**
   * @param array $publishConfig
   * @param string $liveDirectory
   */
  protected function createSymlinksToLiveDirectory($publishConfig, $liveDirectory)
  {
    if (!isset($publishConfig['cname']) || empty($publishConfig['cname'])) {
      return;
    }
    $linkTarget = basename($liveDirectory);
    @symlink($linkTarget, FS::joinPath($this->liveHostingDirectory, $publishConfig['cname']));
    // if the domain starts with www. we remove the www and also provide a vhost without www
    if (substr($publishConfig['cname'], 0, 4) == 'www.') {
        @symlink($linkTarget, FS::joinPath($this->liveHostingDirectory, substr($publishConfig['cname'], 4)));
    }
  }

  /**
   * @param string $liveDirectory
   */
  protected function removeAllSymlinksToLiveDirectory($liveDirectory)
  {
    $shortId = basename($liveDirectory);

    $dirInfo = $this->getDirectoryAsArray($this->liveHostingDirectory);
    if (is_null($dirInfo)) {
      return;
    }

    foreach ($dirInfo['symlinks'] as $symlinkName => $symlinkPathName) {
      $target = @readlink($symlinkPathName);

      // could not read link - this should not happen
      if ($target === false) {
        Registry::getActionLogger()->logAction(Standalone::STANDALONE_PUBLISHER_WARNING_ACTION, array('Could not read link', $symlinkPathName, $liveDirectory));
        continue;
      }

      // link does not point to our website
      if ($target !== $shortId) {
        continue;
      }
      @unlink($symlinkPathName);
    }
  }

  /**
   * @param string $shortId
   *
   * @return string
   */
  protected function getInternalLiveDirectory($shortId)
  {
    return FS::joinPath($this->liveHostingDirectory, $shortId);
  }

  /**
   * @param string $websiteId
   *
   * @return string
   */
  protected function getTempDirectory($websiteId)
  {
    if (isset($this->config[self::CONFIG_SELECTION]['tempDirectory'])) {
      $baseTempDir = $this->config[self::CONFIG_SELECTION]['tempDirectory'];
    } else {
      $baseTempDir = sys_get_temp_dir();
    }
    return FS::joinPath($baseTempDir, $websiteId);
  }

  /**
   * @param string $publishingFilePath
   * @param string $tempDirectory
   *
   * @throws CmsException
   */
  protected function unzipPublishFile($publishingFilePath, $tempDirectory)
  {
    $zip = new \ZipArchive();
    $zipHandle = $zip->open($publishingFilePath);
    if ($zipHandle === true) {
      $zip->extractTo($tempDirectory);
      $zip->close();
      return;
    }

    switch ($zipHandle) {
      case \ZipArchive::ER_EXISTS:
        $errorMessage = 'File already exists.';
            break;
      case \ZipArchive::ER_INCONS:
        $errorMessage = 'Zip archive inconsistent.';
            break;
      case \ZipArchive::ER_MEMORY:
        $errorMessage = 'Malloc failure at open zip archive.';
            break;
      case \ZipArchive::ER_NOZIP:
        $errorMessage = 'Not a zip archive.';
            break;
      case \ZipArchive::ER_READ:
        $errorMessage = 'Error reading zip archive.';
            break;
      case \ZipArchive::ER_OPEN:
        $errorMessage = 'Can\'t open zip archive.';
            break;
      case \ZipArchive::ER_SEEK:
        $errorMessage = 'Seek error at reading zip archive.';
            break;
      default:
        $errorMessage = 'Unknow error at open zip archive (' . $zipHandle . ')';
    }
    throw new CmsException(902, __METHOD__, __LINE__, array('msg' => $errorMessage));
  }

  /**
   * @param string $directory
   *
   * @return array|null
   */
  protected static function getDirectoryAsArray($directory)
  {
    $info = array(
      'directories' => array(),
      'files' => array(),
      'symlinks' => array(),
      'others' => array(),
    );

    if (!is_dir($directory)) {
      return null;
    }

    $dirIterator = new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS);
    foreach ($dirIterator as $fileInfo) {
      if ($fileInfo->isLink()) {
        $type = 'symlinks';
      } elseif ($fileInfo->isFile()) {
        $type = 'files';
      } elseif ($fileInfo->isDir()) {
        $type = 'directories';
      } else {
        $type = 'others';
      }
      $info[$type][$fileInfo->getFilename()] = $fileInfo->getPathname();
    }

    return $info;
  }

  /**
   * @param string $needle
   * @param array  $directories
   *
   * @return bool
   */
  protected function isInsideDirectory($needle, $directories)
  {
    if (in_array($needle, $directories)) {
      return true;
    }

    foreach ($directories as $directory) {
      if (strpos($needle, $directory) === 0) {
        return true;
      }
    }

    return false;
  }

  /**
   * @return string|null
   */
  protected function getPortFromBaseUrl()
  {
    $baseUrlParted = parse_url(Registry::getBaseUrl());
    if (!isset($baseUrlParted['port']) || empty($baseUrlParted['port'])) {
      return null;
    }
    return $baseUrlParted['port'];
  }

  /**
   * @return string|null
   */
  protected function getProtocolFromBaseUrl()
  {
    $baseUrlParted = parse_url(Registry::getBaseUrl());
    if (!isset($baseUrlParted['scheme']) || empty($baseUrlParted['scheme'])) {
      return null;
    }
    return $baseUrlParted['scheme'] . '://';
  }

  /**
   * set options only for the given implementation
   */
  protected function setOptions()
  {
    $externalServiceConfig = $this->config[self::CONFIG_SELECTION];
    $this->liveHostingWebPath = $externalServiceConfig['liveHostingWebPath'];
    $this->liveHostingDirectory = $externalServiceConfig['liveHostingDirectory'];
    if (isset($externalServiceConfig['liveHostingCNameProtocol'])
      && !empty($externalServiceConfig['liveHostingCNameProtocol'])
    ) {
      $this->liveHostingCNameProtocol = $externalServiceConfig['liveHostingCNameProtocol'];
    } else {
      $protocol = $this->getProtocolFromBaseUrl();
      if (!empty($protocol)) {
        $this->liveHostingCNameProtocol = $protocol;
      }
    }
    if (isset($externalServiceConfig['liveHostingCNamePort'])
      && !empty($externalServiceConfig['liveHostingCNamePort'])
    ) {
      $this->liveHostingCNamePort = $externalServiceConfig['liveHostingCNamePort'];
    } else {
      $this->liveHostingCNamePort = $this->getPortFromBaseUrl();
    }
  }

  /**
   * checks the options for the given implementation
   */
  protected function checkRequiredOptions(array $config = array())
  {
    if (!isset($config[self::CONFIG_SELECTION]) || !is_array($config[self::CONFIG_SELECTION])) {
      throw new InvalidConfigException('no configuration for standalone publish service exists');
    }
    $externalServiceConfig = $config[self::CONFIG_SELECTION];
    if (!isset($externalServiceConfig['liveHostingWebPath'])) {
      throw new InvalidConfigException('Configuration must have keys for "liveHostingWebPath".');
    }
    if (!isset($externalServiceConfig['liveHostingDirectory'])) {
      throw new InvalidConfigException('Configuration must have keys for "liveHostingDirectory".');
    }
  }
}
