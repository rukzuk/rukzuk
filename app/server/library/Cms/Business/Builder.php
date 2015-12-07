<?php
namespace Cms\Business;

use Cms\Data\Build as BuildData;
use Cms\Data\Creator as CreatorData;
use Cms\Data\PublisherStatus as PublisherStatusData;
use Cms\Exception as CmsException;
use Seitenbau\Registry as Registry;
use Seitenbau\FileSystem as FS;
use Seitenbau\Json as SbJson;
use Seitenbau\Log as Log;

/**
 * Stellt die Business-Logik fuer Builder zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */
class Builder extends Base\Service
{
  const BUILDER_BUILD_ACTION = 'BUILDER_BUILD_ACTION';
  const BUILDER_PUBLISH_ACTION = 'BUILDER_PUBLISH_ACTION';

  const VERSION = 1;
  const VERSION_FILE_BASENAME = 'version';
  const WEBSITE_SUBDIR = 'website';
  const META_SUBDIR = '';

  const BUILD_FILE_EXTENSION = '.zip';

  /**
   * @var string
   */
  private $lastCreatorData = null;

  /**
   * @param  string $websiteId
   *
   * @return \Cms\Data\Build[]
   */
  public function getWebsiteBuilds($websiteId)
  {
    $websiteService = new Website('Website');
    if (!$websiteService->existsWebsiteAlready($websiteId)) {
      throw new CmsException('602', __METHOD__, __LINE__);
    }

    $buildsOfWebsite = $this->sortBuildsByVersion(
        $this->retrieveWebsiteBuilds($websiteId)
    );

    $buildsOfWebsite = $this->applyWebsiteBuildsThreshold(
        $buildsOfWebsite,
        $websiteId
    );

    return $buildsOfWebsite;
  }

  /**
   * @param  string  $websiteId
   * @param  string  $buildId
   * @param  boolean $includingPublishedStatus
   *
   * @return \Cms\Data\Build
   */
  public function getWebsiteBuildById($websiteId, $buildId, $includingPublishedStatus = true)
  {
    $websiteBuildFile = $this->getWebsiteBuildFilePath($websiteId, $buildId);
    return $this->getBuildInfoFromFile($websiteId, $websiteBuildFile, $includingPublishedStatus);
  }

  /**
   * @param  string $websiteId
   * @param  string $buildId
   *
   * @return boolean
   */
  public function getWebsiteBuildFilePath($websiteId, $buildId)
  {
    $websiteService = new Website('Website');
    if (!$websiteService->existsWebsiteAlready($websiteId)) {
      throw new CmsException('602', __METHOD__, __LINE__, array('id' => $websiteId));
    }

    $websiteBuildFilePath = realpath(FS::joinPath(
        $this->getWebsiteBuildsDirectory($websiteId),
        $buildId . self::BUILD_FILE_EXTENSION
    ));
    if (file_exists($websiteBuildFilePath)) {
      return $websiteBuildFilePath;
    } else {
      throw new CmsException('622', __METHOD__, __LINE__, array('id' => $buildId));
    }
  }

  /**
   * @param  string $websiteId
   *
   * @return null or Cms\Data\Build
   */
  public function getActualPublishingBuild($websiteId)
  {
    $allBuilds = $this->getWebsiteBuilds($websiteId);
    foreach ($allBuilds as $nextBuild) {
      $publisherStatus = $nextBuild->getLastPublished();
      if ($publisherStatus instanceof PublisherStatusData) {
        if ($publisherStatus->isPublishing()) {
          return $nextBuild;
        }
      }
    }
    return;
  }

  /**
   * @param $websiteId
   */
  public function deleteAllWebsiteBuilds($websiteId)
  {
    try {
      $websiteBuildsDirectory = realpath($this->getWebsiteBuildsDirectory($websiteId));
      $checkDir = realPath(Registry::getConfig()->builds->directory);
      if (!empty($websiteBuildsDirectory) && strstr($websiteBuildsDirectory, $checkDir)) {
        FS::rmdir($websiteBuildsDirectory);
      }
    } catch (\Exception $e) {
      $errorMessage = sprintf(
          "Unable to remove website builds directory '%s' (%s)",
          $websiteBuildsDirectory,
          $e->getMessage()
      );
      Registry::getLogger()->log(__METHOD__, __LINE__, $errorMessage, Log::ERR);
    }
  }

  /**
   * @param  array  $buildsOfWebsite
   * @param  string $websiteId
   *
   * @return array
   */
  private function applyWebsiteBuildsThreshold(array $buildsOfWebsite, $websiteId)
  {
    if (count($buildsOfWebsite) > 0) {
      $buildThreshold = $this->getBuildThreshold();

      if ($buildThreshold > 0) {
        $buildsOfWebsiteAboveThreshold = array();

        $buildCount = 0;
        $lastSuccessedPublishedBuildId = null;
        foreach ($buildsOfWebsite as $nextBuild) {
          $publishedStatus = $nextBuild->getLastPublished();
          if ($publishedStatus instanceof PublisherStatusData) {
            // adding last success build to list of builds above threshold
            if (!isset($lastSuccessedPublishedBuildId)
              && $publishedStatus->getStatus() == PublisherStatusData::STATUS_FINISHED
            ) {
              $buildsOfWebsiteAboveThreshold[] = $nextBuild;
              $lastSuccessedPublishedBuildId = $nextBuild->getId();
              continue;
            }

            // adding actual publishing build to list of builds above threshold
            if ($publishedStatus->isPublishing()) {
              $buildsOfWebsiteAboveThreshold[] = $nextBuild;
              continue;
            }
          }

          // build count above threshold
          if (count($buildsOfWebsiteAboveThreshold) < $buildThreshold) {
            $buildsOfWebsiteAboveThreshold[] = $nextBuild;
            continue;
          }

          // delete build below threshold
          $this->removeWebsiteBuild($nextBuild, $websiteId);
        }

        $buildsOfWebsite = $buildsOfWebsiteAboveThreshold;
      }
    }

    return $buildsOfWebsite;
  }


  /**
   * @param \Cms\Data\Build $removableBuild
   * @param string         $websiteId
   */
  private function removeWebsiteBuild(BuildData $removableBuild, $websiteId)
  {
    $websiteBuildsDirectory = $this->getWebsiteBuildsDirectory($websiteId);
    if (!is_dir($websiteBuildsDirectory)) {
      return false;
    }
    $websiteBuildZipToRemove = $this->getBuildFileNameFromBuildId(
        $removableBuild->getId()
    );
    $websiteBuildZipToRemove = FS::joinPath($websiteBuildsDirectory, $websiteBuildZipToRemove);
    if (file_exists($websiteBuildZipToRemove)) {
      unlink($websiteBuildZipToRemove);
    }
    return true;
  }

  /**
   * @return integer
   */
  private function getBuildThreshold()
  {
    $config = Registry::getConfig();

    if (!$config->builds) {
      return 0;
    }

    $configuredThreshold = $config->builds->threshold;

    return (int)$configuredThreshold;
  }

  /**
   * @param  array []  Cms\Data\Build $builds
   *
   * @return array
   */
  private function sortBuildsByVersion(array $builds)
  {
    if (count($builds) > 0) {
      $versionedBuilds = array();
      foreach ($builds as $build) {
        $versionedBuilds[$build->getVersion()] = $build;
      }
      krsort($versionedBuilds);
      return array_values($versionedBuilds);
    }

    return $builds;
  }

  /**
   * @param  string $id
   *
   * @return integer
   */
  private function getVersionFromBuildId($id)
  {
    list($versionPart, $timestampPart) = explode('-', $id);
    return (int)substr($versionPart, 1);
  }

  /**
   * @param  string $id
   *
   * @return integer
   */
  private function getTimestampFromBuildId($id)
  {
    list($versionPart, $timestampPart) = explode('-', $id);
    return (int)$timestampPart;
  }

  /**
   * @param  string $id
   *
   * @return string
   */
  private function getBuildFileNameFromBuildId($id)
  {
    return sprintf('%s%s', $id, self::BUILD_FILE_EXTENSION);
  }

  /**
   * @param  string $buildZipPath
   *
   * @return boolean
   */
  private function getBuildZipArchiveComment($buildZipPath)
  {
    if (!file_exists($buildZipPath)) {
      return false;
    }

    $buildZip = new \ZipArchive();
    if (!$buildZip->open($buildZipPath)) {
      return false;
    }
    $buildZipComment = $buildZip->getArchiveComment();
    $buildZip->close();

    return $buildZipComment;
  }

  /**
   * @param  string  $websiteId
   * @param  string  $websiteBuildFile
   * @param  boolean $includingPublishedStatus
   *
   * @return boolean
   */
  private function getBuildInfoFromFile($websiteId, $websiteBuildFile, $includingPublishedStatus = true)
  {
    $build = null;
    if ($this->hasBuildZipArchiveComment($websiteBuildFile)) {
      $build = $this->getBuildDataFromBuildZipArchiveComment($websiteBuildFile);
    } else {
      $build = $this->getBuildDataFromBuildZipFile($websiteBuildFile);
    }
    if ($build !== null) {
      if ($includingPublishedStatus) {
        $this->addPublishedStatusToBuildData($websiteId, $build);
      }
      return $build;
    }

    return;
  }

  /**
   * @param  string $buildZipPath
   *
   * @return boolean
   */
  private function hasBuildZipArchiveComment($buildZipPath)
  {
    $buildZipCommentJson = $this->getBuildZipArchiveComment($buildZipPath);
    if ($buildZipCommentJson !== false && $buildZipCommentJson !== '') {
      $buildZipComment = json_decode($buildZipCommentJson, true);

      $expectedBuildZipCommentKeys = array(
        'id',
        'comment',
        'timestamp',
      );
      if (is_array($buildZipComment) && count($buildZipComment) >= count($expectedBuildZipCommentKeys)) {
        $intersectedKeys = array_intersect($expectedBuildZipCommentKeys, array_keys($buildZipComment));
        sort($intersectedKeys);
        sort($expectedBuildZipCommentKeys);
        return $intersectedKeys === $expectedBuildZipCommentKeys;
      }
      return false;
    }
    return false;
  }

  /**
   * @param  string $buildZipPath
   *
   * @return \Cms\Data\Build
   */
  private function getBuildDataFromBuildZipArchiveComment($buildZipPath)
  {
    $buildZipCommentJson = $this->getBuildZipArchiveComment($buildZipPath);
    $buildZipComment = json_decode($buildZipCommentJson, true);

    $build = new BuildData;
    $build->setFromArray($buildZipComment);
    if (is_null($build->getVersion())) {
      $build->setVersion($this->getVersionFromBuildId($build->getId()));
    }

    return $build;
  }

  /**
   * @param  string $buildZipPath
   *
   * @return \Cms\Data\Build
   */
  private function getBuildDataFromBuildZipFile($buildZipPath)
  {
    if (!file_exists($buildZipPath)) {
      return null;
    }

    $buildPathInfo = pathinfo($buildZipPath);
    $buildId = $buildPathInfo['filename'];

    $build = new BuildData;

    $build->setId($buildId)
      ->setVersion($this->getVersionFromBuildId($buildId))
      ->setTimestamp($this->getTimestampFromBuildId($buildId));

    return $build;
  }

  /**
   * @param  string         $websiteId
   * @param  \Cms\Data\Build &$buildData
   */
  private function addPublishedStatusToBuildData($websiteId, BuildData &$buildData)
  {
    try {
      $publishedStatus = $this->getBusiness('Publisher')->getPublishedStatusByBuildId($websiteId, $buildData->getId());
      $buildData->setLastPublished($publishedStatus);
    } catch (\Exception $logOnly) {
      Registry::getLogger()->log(__METHOD__, __LINE__, $logOnly->getMessage(), Log::ERR);
    }
  }

  /**
   * @param  string $websiteId
   *
   * @return array[] Cms\Data\Build
   */
  private function retrieveWebsiteBuilds($websiteId)
  {
    $websiteBuildsDirectory = $this->getWebsiteBuildsDirectory($websiteId);
    $buildsOfWebsite = array();

    if (!is_dir($websiteBuildsDirectory)) {
      return $buildsOfWebsite;
    }

    $iterator = new \DirectoryIterator($websiteBuildsDirectory);

    foreach ($iterator as $fileinfo) {
      $pathInfo = pathinfo($fileinfo->getFilename());

      if ($pathInfo['extension'] === 'zip') {
        $websiteBuildFile = FS::joinPath($websiteBuildsDirectory, $fileinfo->getFilename());
        $build = $this->getBuildInfoFromFile($websiteId, $websiteBuildFile);
        if (!is_null($build)) {
          $buildsOfWebsite[] = $build;
        }
      }
    }

    return $buildsOfWebsite;
  }

  /**
   * @param  string $websiteId
   * @param  string $comment
   *
   * @return \Cms\Data\Build
   */
  public function buildWebsite($websiteId, $comment)
  {
    $websiteService = new Website('Website');

    if (!$websiteService->existsWebsiteAlready($websiteId)) {
      throw new CmsException('602', __METHOD__, __LINE__);
    }

    $websiteToCreate = $websiteService->getById($websiteId);
    if (!$websiteToCreate->getPublishingEnabled()) {
      throw new CmsException('624', __METHOD__, __LINE__, array(
        'websiteId' => $websiteId,
      ));
    }

    $creatorBusiness = new \Cms\Business\Creator('Creator');
    $creatorData = $creatorBusiness->createWebsite($websiteId);
    $this->setLastCreatorData($creatorData);

    $increasedVersion = $websiteService->increaseVersion($websiteId);

    $buildTimestamp = $this->getBuildTimestamp();

    $buildData = new BuildData;
    $buildData->setId($this->getBuildId($increasedVersion, $buildTimestamp))
      ->setVersion($increasedVersion)
      ->setTimestamp($buildTimestamp)
      ->setComment($comment)
      ->setWebsiteId($websiteToCreate->getId())
      ->setWebsiteName($websiteToCreate->getName())
      ->setBuilderVersion(self::VERSION)
      ->setCreatorName($creatorData->getName())
      ->setCreatorVersion($creatorData->getVersion());

    if ($this->storeBuildVersionAsJson($buildData)) {
      $this->createAndAnnotateBuildZip($websiteId, $buildData);
      $this->removeCreatedWebsiteDirectory($websiteId);
    } else {
      $this->removeCreatedWebsiteDirectory($websiteId);
      $exceptionMessage = sprintf(
          "Unable to store build version file in '%s'",
          $this->getLastCreatorDirectory()
      );
      throw new \Exception($exceptionMessage);
    }

    return $buildData;
  }

  /**
   * @return integer
   */
  protected function getBuildTimestamp()
  {
    return time();
  }

  /**
   * @param  integer $version
   * @param  integer $time
   *
   * @return string
   */
  private function getBuildId($version, $time)
  {
    return sprintf('v%s-%d', $version, $time);
  }

  /**
   * @return string
   */
  public function getLastCreatorDirectory()
  {
    return $this->lastCreatorData->getBaseDirectory();
  }

  /**
   * @return string
   */
  public function getLastCreatorInfoFilesDirectory()
  {
    return FS::joinPath(
        $this->lastCreatorData->getBaseDirectory(),
        $this->lastCreatorData->getInfoFilesSubDirectory()
    );
  }

  /**
   * @return string
   */
  public function getLastCreatorMetaDirectory()
  {
    return FS::joinPath(
        $this->lastCreatorData->getBaseDirectory(),
        $this->lastCreatorData->getMetaSubDirectory()
    );
  }

  /**
   * @param  \Cms\Data\Creator $creatorData
   */
  protected function setLastCreatorData(CreatorData $creatorData)
  {
    $this->lastCreatorData = $creatorData;
  }

  /**
   * @param  string $websiteId
   *
   * @return string
   */
  private function getWebsiteBuildsDirectory($websiteId)
  {
    $config = Registry::getConfig();
    return FS::joinPath($config->builds->directory, $websiteId);
  }

  /**
   * @param \Cms\Data\Build $buildData
   * @param string         $buildVersionJson
   */
  private function storeBuildVersionAsJson(BuildData $buildData)
  {
    $buildVersionAsJson = json_encode($buildData->toArray());
    return $this->storeBuildVersionFiles($buildVersionAsJson, 'json');
  }

  /**
   * @param  string $websiteId
   *
   * @return boolean
   */
  private function removeCreatedWebsiteDirectory($websiteId)
  {
    try {
      $websiteCreatorDirectory = $this->getLastCreatorDirectory();
      if (realpath($websiteCreatorDirectory)) {
        FS::rmdir($websiteCreatorDirectory);
      }
    } catch (\Exception $e) {
      $errorMessage = sprintf(
          "Unable to remove last creator directory '%s' (%s)",
          $websiteCreatorDirectory,
          $e->getMessage()
      );
      Registry::getLogger()->log(__METHOD__, __LINE__, $errorMessage, Log::ERR);
    }
  }

  /**
   * @param  string         $websiteId
   * @param  \Cms\Data\Build $build
   *
   * @return boolean
   */
  private function createAndAnnotateBuildZip($websiteId, BuildData $build)
  {
    $this->createWebsiteBuildsDirectory($websiteId);
    $this->createBuildZip($websiteId, $build->getId(), $build);
  }

  /**
   * @param  string         $websiteId
   * @param  string         $name Zip name
   * @param  \Cms\Data\Build $build
   *
   * @return string Name of the export zip file
   */
  private function createBuildZip($websiteId, $name, BuildData $build)
  {
    $zipFile = FS::joinPath($this->getWebsiteBuildsDirectory($websiteId), $name . self::BUILD_FILE_EXTENSION);

    $websiteCreatorDirectory = $this->getLastCreatorDirectory();

    $zip = new \ZipArchive();
    $zip->open($zipFile, \ZipArchive::CREATE);
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($websiteCreatorDirectory),
        \RecursiveIteratorIterator::SELF_FIRST
    );

    while ($iterator->valid()) {
      if (!$iterator->isDot()) {
        if ($iterator->isDir()) {
          $zip->addEmptyDir(str_replace('\\', '/', $iterator->getSubPathName()));
        } else {
          $zip->addEmptyDir(str_replace('\\', '/', $iterator->getSubPath()));
          $zip->addFile($iterator->key(), str_replace('\\', '/', $iterator->getSubPathName()));
        }
      }
      $iterator->next();
    }

    $zip->setArchiveComment($this->getBuiltArchiveComment($build));
    $zip->close();

    return $zipFile;
  }

  /**
   * @param  BuildData $build
   *
   * @return string
   */
  private function getBuiltArchiveComment(BuildData $build)
  {
    $buildArchiveComment = $build->toArray();
    return json_encode($buildArchiveComment);
  }

  /**
   * @param  string $websiteId
   */
  private function createWebsiteBuildsDirectory($websiteId)
  {
    $websiteBuildsDirectory = $this->getWebsiteBuildsDirectory($websiteId);
    FS::createDirIfNotExists($websiteBuildsDirectory, true);
  }

  /**
   * @param  string $versionContent
   * @param  string $fileExtension
   *
   * @return boolean
   */
  private function storeBuildVersionFiles($versionContent, $fileExtension)
  {
    $buildVersionFileName = self::VERSION_FILE_BASENAME . '.' . $fileExtension;

    $metaDirectory = $this->getLastCreatorMetaDirectory();
    if (!$this->writeBuildVersionFile($versionContent, $metaDirectory, $buildVersionFileName)) {
      return false;
    }

    $infoFilesDirectory = $this->getLastCreatorInfoFilesDirectory();
    if (!$this->writeBuildVersionFile($versionContent, $infoFilesDirectory, 'builder.'.$fileExtension)) {
      return false;
    }

    return true;
  }

  /**
   * @param  string $versionContent
   * @param  string $directory
   * @param  string $fileName
   *
   * @return boolean
   */
  private function writeBuildVersionFile($versionContent, $directory, $fileName)
  {
    if (!is_dir($directory)) {
      $logMessage = sprintf(
          "Directory '%s' to write build version file to doesn't exist or isn't writeable",
          $directory
      );
      Registry::getLogger()->log(__METHOD__, __LINE__, $logMessage, \Seitenbau\Log::ERR);
      return false;
    }

    $buildVersionFilePath = FS::joinPath($directory, $fileName);
    try {
      FS::writeContentToFile($buildVersionFilePath, $versionContent);
    } catch (\Exception $e) {
      $logMessage = sprintf(
          "Unable to write build version file (%s)",
          $fileName,
          $directory,
          $e->getMessage()
      );
      Registry::getLogger()->log(__METHOD__, __LINE__, $logMessage, \Seitenbau\Log::ERR);
      return false;
    }

    return true;
  }

  /**
   * @param string $identity
   * @param string $rightname
   * @param array  $check
   *
   * @return boolean
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    if ($this->isSuperuser($identity)) {
      return true;
    }

    switch ($rightname) {
      case 'publisherStatusChanged':
            return true;
        break;
      case 'getWebsiteBuilds':
      case 'getWebsiteBuildById':
      case 'publishWebsite':
      case 'buildWebsite':
      case 'buildAndPublishWebsite':
        if ($this->checkWebsitePrivilegeForIdentity($identity, $check['websiteId'], 'website', 'publish')) {
          return true;
        }
            break;
    }

    return false;
  }
}
