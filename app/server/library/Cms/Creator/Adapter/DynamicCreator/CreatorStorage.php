<?php


namespace Cms\Creator\Adapter\DynamicCreator;

use Render\ImageToolFactory\ImageTool;
use Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage;
use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaCDNHelper\MediaRequest;
use Render\MediaUrlHelper\CDNMediaUrlHelper;
use Render\MediaUrlHelper\ValidationHelper\NoneValidationHelper;
use Seitenbau\FileSystem as FS;

class CreatorStorage extends AbstractCreatorStorage
{
  const STORAGE_NAME = 'dynamic';
  const VERSION = 1;

  /**
   * @var array
   */
  private $usedModule = array();
  /**
   * @var array
   */
  private $usedMedia = array();
  /**
   * @var array
   */
  private $usedAlbum = array();

  /**
   * @var array
   */
  private $websiteSettings = array();

  /**
   * @var array
   */
  private $colors = array();

  /**
   * @var array
   */
  private $resolutions = array();

  /**
   * @var array
   */
  private $navigation = array();

  /**
   * @var array
   */
  private $pageUrls = array();

  /**
   * @var array
   */
  private $mediaUrlCalls = array();

  /**
   * @return string
   */
  protected function getStorageName()
  {
    return self::STORAGE_NAME;
  }

  /**
   * Finalizing the creator storage
   */
  public function finalize()
  {
    $this->createUsedModules();
    $this->createUsedMediaItems();
    $this->createUsedAlbumInfo();
    $this->createWebsiteSettingsInfo();
    $this->createHtaccessFromWebsiteSettings();
    $this->createColorInfo();
    $this->createResolutionInfo();
    $this->createNavigation();
    $this->createPageUrls();
    $this->createMediaModificationInfo();
    parent::finalize();
  }

  /**
   * return array
   */
  public function toArray()
  {
    return array(
      'legacy' => $this->legacySupportActivated(),
      'moduleIds' => array_keys($this->usedModule),
      'mediaIds' => array_keys($this->usedMedia),
      'albumIds' => array_keys($this->usedAlbum),
    );
  }

  /**
   * @param string[] $moduleIds
   */
  public function addModule(array $moduleIds)
  {
    foreach ($moduleIds as $moduleId) {
      if (isset($this->usedModule[$moduleId])) {
        continue;
      }
      $this->usedModule[$moduleId] = true;
    }
  }

  /**
   * @param string[] $mediaIds
   */
  public function addMedia(array $mediaIds)
  {
    foreach ($mediaIds as $mediaId) {
      if (isset($this->usedMedia[$mediaId])) {
        continue;
      }
      $this->usedMedia[$mediaId] = true;
    }
  }

  /**
   * @param string[] $albumIds
   */
  public function addAlbum(array $albumIds)
  {
    foreach ($albumIds as $albumId) {
      if (isset($this->usedAlbum[$albumId])) {
        continue;
      }
      $mediaIds = $this->getCreatorContext()->getMediaIdsByAlbumId(
          $this->getWebsiteId(),
          $albumId
      );
      $this->addMedia($mediaIds);
      $this->usedAlbum[$albumId] = $mediaIds;
    }
  }

  /**
   * @param array $mediaUrlCalls
   */
  public function addMediaUrlCalls(array $mediaUrlCalls)
  {
    $this->mediaUrlCalls = array_replace($this->mediaUrlCalls, $mediaUrlCalls);
  }

  /**
   * @param array $websiteSettings
   */
  public function setWebsiteSettings(array $websiteSettings)
  {
    $this->websiteSettings = $websiteSettings;
  }

  /**
   * @param array $colors
   */
  public function setColors(array $colors)
  {
    $this->colors = $colors;
  }

  /**
   * @param array $resolutions
   */
  public function setResolutions(array $resolutions)
  {
    $this->resolutions = $resolutions;
  }

  /**
   * @param array $nav
   */
  public function setNavigation(array $nav)
  {
    $this->navigation = $nav;
  }

  /**
   * @param string $pageId
   */
  public function addPage($pageId) {
    $this->addPageUrl($pageId);
  }

  /**
   * @param string $pageId
   */
  public function addLegacyPage($pageId) {
    $this->activateLegacySupport();
    $this->addPageUrl($pageId);
  }

  /**
   * @param string $pageId
   * @param array  $pageMeta
   * @param array  $pageGlobal
   * @param array  $pageAttributes
   * @param array  $pageContent
   * @param string $cssCacheValue
   */
  public function createPage(
    $pageId,
    array $pageMeta,
    array $pageGlobal,
    array $pageAttributes,
    array $pageContent,
    $cssCacheValue
  ) {
    $pageMeta['legacy'] = false;
    $this->createPageFiles($pageId, $pageMeta, $pageGlobal, $pageAttributes, $pageContent, $cssCacheValue);
  }

  /**
   * @param string $pageId
   * @param array  $pageMeta
   * @param array  $pageGlobal
   * @param array  $pageAttributes
   * @param array  $pageContent
   * @param string $cssCacheValue
   */
  public function createLegacyPage(
    $pageId,
    array $pageMeta,
    array $pageGlobal,
    array $pageAttributes,
    array $pageContent,
    $cssCacheValue
  ) {
    $pageMeta['legacy'] = true;
    $this->createPageFiles($pageId, $pageMeta, $pageGlobal, $pageAttributes, $pageContent, $cssCacheValue);
  }

  /**
   * @param string $pageId
   * @param array  $pageMeta
   * @param array  $pageGlobal
   * @param array  $pageAttributes
   * @param array  $pageContent
   * @param string $cssCacheValue
   */
  protected function createPageFiles(
    $pageId,
    array $pageMeta,
    array $pageGlobal,
    array $pageAttributes,
    array $pageContent,
    $cssCacheValue
  ) {
    $cssFileName = $this->getPageCssFileName($pageId);
    $pageMeta['css'] = array(
      'file'  => $cssFileName,
      'url'   => $cssFileName . '?m=' . time(),
    );

    $pageDataDirectory = $this->getPageDataDirectory($pageId);
    FS::createDirIfNotExists($pageDataDirectory, true);
    $this->createPageMetaFile($pageId, $pageMeta, $pageDataDirectory);
    $this->createPageGlobalFile($pageId, $pageGlobal, $pageDataDirectory);
    $this->createPageAttributesFile($pageId, $pageAttributes, $pageDataDirectory);
    $this->createPageContentArrayFile($pageId, $pageContent, $pageDataDirectory);
    $this->createPageStructureFile($pageId);
    $this->createPageCssCacheFile($cssFileName, $cssCacheValue);
  }

  /**
   * @param $pageId
   */
  protected function addPageUrl($pageId)
  {
    if (!isset($this->pageUrls[$pageId])) {
      $this->pageUrls[$pageId] = $this->getPageUrl($pageId, false);
    }
  }

  /**
   * @param string $pageId
   * @param array  $pageMeta
   * @param string $pageDataDirectory
   */
  protected function createPageMetaFile(
      $pageId,
      array $pageMeta,
      $pageDataDirectory
  ) {
    $comments = array("page meta", "page id: " . $pageId);
    $metaFilePath = FS::joinPath($pageDataDirectory, 'meta.php');
    $this->exportDataToFile($metaFilePath, $pageMeta, $comments);
  }

  /**
   * @param string $pageId
   * @param array  $pageGlobal
   * @param string $pageDataDirectory
   */
  protected function createPageGlobalFile(
      $pageId,
      array $pageGlobal,
      $pageDataDirectory
  ) {
    $comments = array("page global variables", "page id: " . $pageId);
    $metaFilePath = FS::joinPath($pageDataDirectory, 'global.php');
    $this->exportDataToFile($metaFilePath, $pageGlobal, $comments);
  }

  /**
   * @param string $pageId
   * @param array  $pageAttributes
   * @param string $pageDataDirectory
   */
  protected function createPageAttributesFile(
      $pageId,
      array $pageAttributes,
      $pageDataDirectory
  ) {
    $comments = array("page attributes", "page id: " . $pageId);
    $metaFilePath = FS::joinPath($pageDataDirectory, 'attributes.php');
    $this->exportDataToFile($metaFilePath, $pageAttributes, $comments);
  }

  /**
   * @param string $pageId
   * @param array  $pageContent
   * @param string $pageDataDirectory
   */
  protected function createPageContentArrayFile(
      $pageId,
      array $pageContent,
      $pageDataDirectory
  ) {
    $comments = array("page content", "page id: " . $pageId);
    $metaFilePath = FS::joinPath($pageDataDirectory, 'contentarray.php');
    $this->exportDataToFile($metaFilePath, $pageContent, $comments);
  }

  /**
   * @param string $pageId
   */
  protected function createPageStructureFile($pageId)
  {
    $data = array(
      'PAGE_ID' => $pageId,
      'YEAR' => date('Y'),
      'INSTALLATION_PATH' => $this->getInstallationPathForPage($pageId),
    );
    $pageStructureFilePath = $this->getPageStructureFilePath($pageId);
    FS::createDirIfNotExists(dirname($pageStructureFilePath), true);
    $this->createFileFromTemplate(
        $pageStructureFilePath,
        'page.index.php.tpl',
        $data
    );
  }

  /**
   * @param string $pageId
   * TODO: unused, remove?
   */
  protected function createPageCssFile($pageId)
  {
    $data = array(
      'PAGE_ID' => $pageId,
      'YEAR' => date('Y'),
    );
    $pageCssDirectory = FS::joinPath($this->getCssBaseDirectory(), $pageId);
    FS::createDirIfNotExists($pageCssDirectory, true);
    $pageCssFilePath = FS::joinPath($pageCssDirectory, 'style.php');
    $this->createFileFromTemplate(
        $pageCssFilePath,
        'page.css.php.tpl',
        $data
    );
  }

  protected function createPageCssCacheFile($cssFileName, $cssCacheValue)
  {
    $pageCssFilePath = FS::joinPath($this->getCssBaseDirectory(), $cssFileName);
    FS::writeContentToFile($pageCssFilePath, $cssCacheValue);
  }


  /**
   * copy module files and create module list file
   */
  protected function createUsedModules()
  {
    $moduleInfoStorage = $this->getCreatorContext()->getModuleInfoStorage(
        $this->getWebsiteId()
    );
    $moduleList = array();
    foreach (array_keys($this->usedModule) as $moduleId) {
      try {
        $this->copyModuleCode($moduleInfoStorage, $moduleId);
        $this->copyModuleAssets($moduleInfoStorage, $moduleId);
        $moduleList[$moduleId] = $this->getModuleInfo(
            $moduleInfoStorage,
            $moduleId
        );
      } catch (\Exception $e) {
        $this->addError('Error at create module ' . $moduleId, $e);
      }
    }
    $moduleInfoFilePath = FS::joinPath(
        $this->getDataDirectory(),
        'modules.php'
    );
    $comments = array("modules info array", "site: " . $this->getWebsiteId());
    $this->exportDataToFile($moduleInfoFilePath, $moduleList, $comments);
  }

  /**
   * @param IModuleInfoStorage $moduleInfoStorage
   * @param string             $moduleId
   *
   * @return array
   */
  protected function getModuleInfo($moduleInfoStorage, $moduleId)
  {
    $mainClassFilePath = $moduleInfoStorage->getModuleMainClassFilePath($moduleId);
    return array(
      'manifest' => $moduleInfoStorage->getModuleManifest($moduleId),
      'codePath' => $moduleId,
      'mainClassFilePath' => $moduleId . '/' . basename($mainClassFilePath),
      'mainClassName' => $moduleInfoStorage->getModuleClassName($moduleId),
      'customData' => $moduleInfoStorage->getModuleCustomData($moduleId),
      'defaultFormValues' => array(),
    );
  }

  /**
   * @param IModuleInfoStorage $moduleInfoStorage
   * @param string             $moduleId
   */
  protected function copyModuleCode($moduleInfoStorage, $moduleId)
  {
    $srcDirectory = $moduleInfoStorage->getModuleCodePath($moduleId);
    $destDirectory = FS::joinPath(
        $this->getModuleBaseDirectory(),
        $moduleId
    );
    FS::createDirIfNotExists($destDirectory, true);
    $this->copyModuleFiles($srcDirectory, $destDirectory);
  }

  /**
   * @param IModuleInfoStorage $moduleInfoStorage
   * @param  string            $moduleId
   */
  protected function copyModuleAssets($moduleInfoStorage, $moduleId)
  {
    $srcAssetDirectory = $moduleInfoStorage->getModuleAssetPath($moduleId);
    $destAssetDirectory = FS::joinPath(
        $this->getModuleAssetsBaseDirectory(),
        $moduleId
    );
    $this->copyModuleFiles($srcAssetDirectory, $destAssetDirectory);
  }

  /**
   * @param string $srcDirectory
   * @param string $destDirectory
   */
  protected function copyModuleFiles($srcDirectory, $destDirectory)
  {
    FS::createDirIfNotExists($destDirectory, true);
    if (!is_dir($srcDirectory)) {
      return;
    }
    foreach (new \DirectoryIterator($srcDirectory) as $fileInfo) {
      if ($fileInfo->isDot()) {
        continue;
      }
      $this->copyModuleFile(
          $srcDirectory,
          $destDirectory,
          $fileInfo->getFilename()
      );
    }
  }

  /**
   * @param string $srcDirectory
   * @param string $destDirectory
   * @param string $fileName
   */
  private function copyModuleFile($srcDirectory, $destDirectory, $fileName)
  {
    $srcPathName = FS::joinPath($srcDirectory, $fileName);
    $destPathName = FS::joinPath($destDirectory, $fileName);

    $lowerCaseFileName = strtolower($fileName);
    if ($lowerCaseFileName == 'notlive') {
      return;
    }

    if (is_dir($srcPathName)) {
      FS::copyDir($srcPathName, $destPathName);
    } else {
      FS::copyFile($srcPathName, $destPathName);
    }
  }

  /**
   * copy media files and create media item info list
   */
  protected function createUsedMediaItems()
  {
    $mediaInfoStorage = $this->getCreatorContext()->getMediaInfoStorage(
        $this->getWebsiteId(),
        $this->getCreatorContext()->createCmsMediaUrlHelper()
    );
    $mediaList = array();
    foreach (array_keys($this->usedMedia) as $mediaId) {
      try {
        $mediaItem = $mediaInfoStorage->getItem($mediaId);
        $this->copyMediaFile($mediaItem);
        $mediaList[$mediaId] = $this->getMediaInfo($mediaItem);
      } catch (\Exception $e) {
        $this->addError('Error at create media ' . $mediaId, $e);
      }
    }
    $mediaInfoFilePath = FS::joinPath(
        $this->getDataDirectory(),
        'media.php'
    );
    $comments = array("media info array", "site: " . $this->getWebsiteId());
    $this->exportDataToFile($mediaInfoFilePath, $mediaList, $comments);
  }

  /**
   * create media modification info file
   */
  protected function createMediaModificationInfo()
  {
    $mediaModList = array();
    foreach ($this->mediaUrlCalls as $call) {
      $uniqueKey = md5(json_encode(array(
        'id' => $call['id'],
        'type' => $call['type'],
        'operations' => $call['operations'],
      )));
      $mediaModList[$uniqueKey] = true;
    }
    $mediaModFilePath = FS::joinPath(
        $this->getDataDirectory(),
        'media.mod.php'
    );
    $comments = array("media modification array", "site: " . $this->getWebsiteId());
    $this->exportDataToFile($mediaModFilePath, $mediaModList, $comments);
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return array
   */
  protected function getMediaInfo($mediaItem)
  {
    return array(
      'id' => $mediaItem->getId(),
      'file' => basename($mediaItem->getFilePath()),
      'name' => $mediaItem->getName(),
      'size' => $mediaItem->getSize(),
      'lastModified' => $mediaItem->getLastModified(),
    );
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   */
  protected function copyMediaFile($mediaItem)
  {
    $fileName = basename($mediaItem->getFilePath());
    $destFilePath = FS::joinPath($this->getMediaFilesDirectory(), $fileName);
    FS::copyFile($mediaItem->getFilePath(), $destFilePath);
  }

  /**
   * create album info list
   */
  protected function createUsedAlbumInfo()
  {
    $albumInfoFilePath = FS::joinPath(
        $this->getDataDirectory(),
        'album.php'
    );
    $comments = array("album info array", "site: " . $this->getWebsiteId());
    $this->exportDataToFile($albumInfoFilePath, $this->usedAlbum, $comments);
  }

  protected function createWebsiteSettingsInfo()
  {
    $websiteSettingsInfoFilePath = FS::joinPath(
        $this->getDataDirectory(),
        'websitesettings.php'
    );
    $comments = array("website settings info array", "site: " . $this->getWebsiteId());
    $this->exportDataToFile($websiteSettingsInfoFilePath, $this->websiteSettings, $comments);
  }

  protected function createHtaccessFromWebsiteSettings()
  {
    if (array_key_exists('htaccess', $this->websiteSettings)) {
      if(isset($this->websiteSettings['htaccess']['htaccessContent']) 
         && !empty($this->websiteSettings['htaccess']['htaccessContent'])) {
        $content = $this->websiteSettings['htaccess']['htaccessContent'];
      } else {
        return; // end here
      }
    }

    $htaccessFilePath = FS::joinPath(
        $this->getWebsiteDirectory(),
        '.htaccess'
    );
    FS::writeContentToFile(
        $htaccessFilePath,
        $content,
        "Error at creating file '%s' (%s): %s"
    );
  }

  protected function createColorInfo()
  {
    $colorsInfoFilePath = FS::joinPath(
        $this->getDataDirectory(),
        'colors.php'
    );
    $comments = array("colors info array", "site: " . $this->getWebsiteId());
    $this->exportDataToFile($colorsInfoFilePath, $this->colors, $comments);
  }

  protected function createResolutionInfo()
  {
    $resolutionsInfoFilePath = FS::joinPath(
        $this->getDataDirectory(),
        'resolutions.php'
    );
    $comments = array("resolutions info array", "site: " . $this->getWebsiteId());
    $this->exportDataToFile($resolutionsInfoFilePath, $this->resolutions, $comments);
  }

  protected function createNavigation()
  {
    $navInfoFilePath = FS::joinPath(
        $this->getDataDirectory(),
        'navigation.php'
    );
    $comments = array("navigation info array", "site: " . $this->getWebsiteId());
    $this->exportDataToFile($navInfoFilePath, $this->navigation, $comments);
  }

  protected function createPageUrls()
  {
    $navInfoFilePath = FS::joinPath(
        $this->getDataDirectory(),
        'urls.php'
    );
    $comments = array("page urls info array", "site: " . $this->getWebsiteId());
    $this->exportDataToFile($navInfoFilePath, $this->pageUrls, $comments);
  }

  /**
   * @return IMediaInfoStorage
   */
  protected function getMediaInfoStorage()
  {
    $mediaUrlHelper = new CDNMediaUrlHelper(new NoneValidationHelper(), '');
    return $this->getCreatorContext()->getMediaInfoStorage(
        $this->getWebsiteId(),
        $mediaUrlHelper
    );
  }

  /**
   * @return DynamicCreatorMediaCache
   */
  protected function getMediaCache()
  {
    return new DynamicCreatorMediaCache($this->getMediaCacheDirectory());
  }

  /**
   * @param ImageTool                $imageTool
   * @param MediaCache               $cmsMediaCache
   * @param DynamicCreatorMediaCache $mediaCache
   * @param MediaInfoStorageItem     $mediaItem
   * @param string                   $cdnType
   * @param array                    $operations
   */
  protected function createMediaCacheFile(
      ImageTool $imageTool,
      MediaCache $cmsMediaCache,
      DynamicCreatorMediaCache $mediaCache,
      MediaInfoStorageItem $mediaItem,
      $cdnType,
      array $operations
  ) {
    if (count($operations) <= 0) {
      return;
    }
    $isIcon = false;
    switch ($cdnType) {
      case MediaRequest::TYPE_IMAGE:
        $filePath = $mediaItem->getFilePath();
            break;
      case MediaRequest::TYPE_ICON:
        $isIcon = true;
        $filePath = $mediaItem->getIconFilePath();
            break;
      case MediaRequest::TYPE_PREVIEW:
        $filePath = $mediaItem->getFilePath();
        if (!$imageTool->isImageFile($filePath)) {
          $isIcon = true;
          $filePath = $mediaItem->getIconFilePath();
        }
            break;
      default:
            return;
    }
    $cmsCacheFilePath = $this->createCmsCacheFile(
        $imageTool,
        $cmsMediaCache,
        $mediaItem,
        $operations,
        $isIcon,
        $filePath
    );
    $liveCacheFilePath = $mediaCache->getCacheFilePath(
        $mediaItem,
        $operations,
        $isIcon
    );
    FS::copyFile($cmsCacheFilePath, $liveCacheFilePath);
  }

  /**
   * @param ImageTool            $imageTool
   * @param MediaCache           $cmsMediaCache
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   * @param string               $isIcon
   * @param string               $filePath
   *
   * @return string
   */
  protected function createCmsCacheFile(
      ImageTool $imageTool,
      MediaCache $cmsMediaCache,
      MediaInfoStorageItem $mediaItem,
      array $operations,
      $isIcon,
      $filePath
  ) {
    $cmsMediaCache->prepareCache($mediaItem);
    $cmsCacheFilePath = $cmsMediaCache->getCacheFilePath(
        $mediaItem,
        $operations,
        $isIcon
    );
    if ($this->isCmsCacheFileValid($mediaItem, $filePath, $cmsCacheFilePath)) {
      return $cmsCacheFilePath;
    }
    $imageTool->open($filePath);
    $imageTool->modify($operations);
    $imageTool->save($cmsCacheFilePath);
    $imageTool->close();
    return $cmsCacheFilePath;
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param string               $filePath
   * @param string               $cmsCacheFilePath
   *
   * @return bool
   */
  protected function isCmsCacheFileValid(
      MediaInfoStorageItem $mediaItem,
      $filePath,
      $cmsCacheFilePath
  ) {
    $orgFileTime = @\filemtime($filePath);
    $cacheFileTime = @\filemtime($cmsCacheFilePath);
    $cacheFileSize = @\filesize($cmsCacheFilePath);
    if (empty($orgFileTime) || empty($cacheFileTime) || empty($cacheFileSize)) {
      return false;
    }
    if ($orgFileTime >= $cacheFileTime) {
      return false;
    }
    if ($mediaItem->getLastModified() >= $cacheFileTime) {
      return false;
    }

    return true;
  }
}
