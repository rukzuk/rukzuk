<?php
namespace Cms\Business;

use Cms\Dao\Base\AbstractSourceItem;
use Seitenbau\Registry;
use Cms\Exception as CmsException;
use Cms\Version;
use Cms\Quota;
use Cms\Data;
use Orm\Data as Unit;
use Orm\Data\Website\ColorItem as DataColorItem;
use Cms\Request\Base as BaseRequest;
use Symfony\Component\Finder\Finder;
use Seitenbau\FileSystem as FS;
use Dual\Render\WebsiteColor as WebsiteColor;
use Seitenbau\Image as ImageTool;
use Cms\Data\Modul as DataModule;
use Cms\Data\Package as DataPackage;
use Seitenbau\Log as SbLog;

/**
 * Stellt die Business-Logik fuer Export zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Export extends Base\Service
{
  const EXPORT_MODE_MODULE = 'MODULE';
  const EXPORT_MODE_TEMPLATESNIPPET = 'TEMPLATESNIPPET';
  const EXPORT_MODE_WEBSITE = 'WEBSITE';
  const EXPORT_FILE_EXTENSION = 'rukzuk';

  /**
   * paths should be the same as defined in
   * @see \Cms\Dao\Modul\FileSystem
   */
  const MODULE_SUBDIR_DATA             = 'module';
  const MODULE_SUBDIR_ASSETS           = 'assets';
  const MODULE_FILE_MANIFEST           = 'manifest.json';
  const MODULE_FILE_LEGACY_MANIFEST    = 'moduleManifest.json';

  private $mediaDirectory;
  private $exportDirectory;
  private $currentExportDirectory;
  private $currentExportName;
  private $extendingColorIdCallbacks = array();
  
  private $exportedItemsIds = array();
  
  private $websiteBusiness = null;
  private $templateBusiness = null;
  private $templateSnippetBusiness = null;
  private $moduleBusiness = null;
  private $mediaBusiness = null;
  private $albumBusiness = null;
  private $screenshotBusiness = null;
  private $pageBusiness = null;
  private $websiteSettingsBusiness = null;
  private $packageBusiness = null;

  public function __construct($businessname)
  {
    parent::__construct($businessname);
    $config = Registry::getConfig();
    $this->mediaDirectory = $config->media->files->directory;
    $this->exportDirectory = $config->export->directory;
  }

  /**
   * @param $exportFile
   * @return string
   */
  public function removeExport($exportFile)
  {
    $exportPath = dirname(realpath($exportFile));
    if (strpos($exportPath, $this->exportDirectory) == 0 && $exportPath != $this->exportDirectory) {
      FS::rmdir($exportPath);
    }
  }

  /**
   * @param  string $mode
   * @param  string $websiteId
   * @param  array  $ids
   * @param  string $exportName
   * @return string The export Uri for the Cdn Controller
   * @throws \Exception
   */
  public function export($mode, $websiteId, array $ids, $exportName = null)
  {
    $this->checkExportQuota();

    $exportName = $this->initExport($websiteId, $mode, $exportName);
    
    $exportJsonFilename = $this->currentExportDirectory
      . DIRECTORY_SEPARATOR . 'export.json';
    file_put_contents($exportJsonFilename, $this->getExportJson($websiteId, $mode));

    switch ($mode) {
      
      case self::EXPORT_MODE_MODULE:
          $exportZipFile = $this->exportModule($websiteId, $ids, true);
          $exportCdnUri = $this->buildExportCdnUri($exportZipFile, $exportName);
            break;

      case self::EXPORT_MODE_TEMPLATESNIPPET:
          $exportZipFile = $this->exportTemplateSnippets($websiteId, $ids, true);
          $exportCdnUri = $this->buildExportCdnUri($exportZipFile, $exportName);
            break;

      default:
          $this->deleteLeftoverExport();
            break;
    }

    return $exportCdnUri;
  }

  /**
   * @param  string   $websiteId
   * @param  string   $mode
   * @param  string   $exportName
   * @return string   the cleaned export name
   */
  protected function initExport($websiteId, $mode, $exportName = null)
  {
    $this->exportedItemsIds = array();
    $this->exportedItemsIds[$websiteId] = array(
      'media' => array(),
    );

    $exportName = $this->createCleanExportName($exportName, $websiteId, $mode);
    $this->createExportDirectory($exportName);
    
    return $exportName;
  }

  /**
   * @param  string $exportName
   * @return string
   */
  private function cleanExportName($exportName)
  {
    $structureNameSearch = array('/[\x{00d6}]/u',       // 'Oe'
                                 '/[\x{00f6}]/u',       // 'oe'
                                 '/[\x{00c4}]/u',       // 'Ae'
                                 '/[\x{00e4}]/u',       // 'ae'
                                 '/[\x{00dc}]/u',       // 'Ue'
                                 '/[\x{00fc}]/u',       // 'ue'
                                 '/[\x{00df}]/u',       // 'ss'
                                 '/:/u',                // '-'
                                 '/[^0-9a-z _\-]/i',    // ' '
                                 '/[ ]{2,}/i',          // ' '
                           );
    $structureNameReplace = array('Oe',
                                  'oe',
                                  'Ae',
                                  'ae',
                                  'Ue',
                                  'ue',
                                  'ss',
                                  '-',
                                  ' ',
                                  ' ',
                            );
    return preg_replace($structureNameSearch, $structureNameReplace, $exportName);
  }
  /**
   * @param  string $exportZipFile
   * @param  string $exportName
   * @return string
   */
  public function buildExportCdnUri($exportZipFile, $exportName)
  {
    $config = Registry::getConfig();
    $serverUrl = $config->server->url;
    $exportCdnUri = sprintf(
        '%s/cdn/export/%s/%s',
        $serverUrl,
        BaseRequest::REQUEST_PARAMETER,
        \Zend_Json::encode(array(
        'name' => $exportName,
        ))
    );
    
    return $exportCdnUri;
  }

  /**
   * @param string  $websiteId
   * @param string  $exportName
   * @param bool $complete Boolean flag for exporting also non used entities
   * @param bool     $checkExportQuota
   *
   * @return array   'file': Export Zip file; 'name': Export name
   */
  public function exportWebsite(
      $websiteId,
      $exportName = null,
      $complete = false,
      $checkExportQuota = true
  ) {
    if ($checkExportQuota) {
      $this->checkExportQuota();
    }

    $exportName = $this->initExport($websiteId, self::EXPORT_MODE_WEBSITE, $exportName);

    $website = $this->getWebsiteBusiness()->getById($websiteId);

    $websiteDirectory = FS::joinPath($this->currentExportDirectory, 'website');
    FS::createDirIfNotExists($websiteDirectory, true);

    $pageIds = $this->getPageBusiness()->getIdsByWebsiteId($websiteId);
    $this->exportPages($websiteId, $pageIds, false, $complete);
    
    $this->exportTemplateSnippets($websiteId, array(), false, true);

    $this->exportPackages($websiteId, array(), false, true);

    $this->insertScreenshot(self::EXPORT_MODE_WEBSITE, $websiteId);
    
    FS::writeContentToFile(
        FS::joinPath($this->currentExportDirectory, 'export.json'),
        $this->getExportJson($websiteId, self::EXPORT_MODE_WEBSITE)
    );
    
    FS::writeContentToFile(
        FS::joinPath($this->currentExportDirectory, 'usergroup.json'),
        $this->getUsergroupJson($websiteId)
    );
    
    FS::writeContentToFile(
        FS::joinPath($websiteDirectory, 'website.json'),
        $this->getWebsiteJson($website)
    );

    FS::writeContentToFile(
        FS::joinPath($websiteDirectory, 'navigation.json'),
        $website->getNavigation()
    );

    $this->exportWebsiteSettings($websiteId, $websiteDirectory);

    $zipfile = $this->createExportZip();
    $this->deleteExportDirectories();
    return array(
      'file'  => $zipfile,
      'name'  => $exportName,
    );
  }

  /**
   * @param string $websiteId
   * @param string $websiteDirectory
   */
  protected function exportWebsiteSettings($websiteId, $websiteDirectory)
  {
    $allWebsiteSettings = $this->getWebsiteSettingsBusiness()->getAll($websiteId);

    FS::writeContentToFile(
        FS::joinPath($websiteDirectory, 'websitesettings.json'),
        $this->getWebsiteSettingsJson($allWebsiteSettings)
    );
  }
  
  /**
   * @param string  $websiteId
   * @param array   $pageIds
   * @param bool $zipAndRemoveDirectories Defaults to false
   * @param bool $complete Boolean flag for exporting also non used entities
   * @return string
   */
  private function exportPages($websiteId, array $pageIds, $zipAndRemoveDirectories = false, $complete)
  {
    $relatedTemplateIds = array();
    $relatedMediaIds = array();
    $pageBusiness = $this->getPageBusiness();
    $pagesDirectory = FS::joinPath($this->currentExportDirectory, 'pages');
    $exportMediaDirectory = FS::joinPath($this->currentExportDirectory, 'media');

    if ($complete) {
      $allTemplatesOfWebsite = $this->execute('getTemplatesByWebsiteId', array($websiteId));
      if (is_array($allTemplatesOfWebsite) && count($allTemplatesOfWebsite) > 0) {
        foreach ($allTemplatesOfWebsite as $templateOfWebsite) {
          $relatedTemplateIds[] = $templateOfWebsite->getId();
        }
      }

      $allMediaOfWebsite = $this->execute('getMediaByWebsiteId', array($websiteId));
      if (is_array($allMediaOfWebsite) && count($allMediaOfWebsite) > 0) {
        foreach ($allMediaOfWebsite as $mediaOfWebsite) {
          $relatedMediaIds[] = $mediaOfWebsite->getId();
        }
      }
    }

    mkdir($pagesDirectory);
    $pageIds = array_unique($pageIds);
    foreach ($pageIds as $pageId) {
      $page = $pageBusiness->getById($pageId, $websiteId);
      $pageDirectory = FS::joinPath($pagesDirectory, $page->getId());
      mkdir($pageDirectory);

      $pageJsonFilename = FS::joinPath($pageDirectory, 'page.json');
      file_put_contents($pageJsonFilename, $this->getPageJson($page));

      $relatedTemplateIds[] = $page->getTemplateid();
      $relatedMediaIds = array_merge(
          $relatedMediaIds,
          $this->getMediaIdsForPage($page)
      );
    }

    $relatedTemplateIds = array_unique($relatedTemplateIds);
    $this->exportTemplates($websiteId, $relatedTemplateIds, false, $complete);
    
    $relatedMediaIds = array_unique($relatedMediaIds);
    if (count($relatedMediaIds) > 0) {
      if (!is_dir($exportMediaDirectory)) {
        mkdir($exportMediaDirectory);
      }

      foreach ($relatedMediaIds as $mediaId) {
        $this->copyMediaAndCreateMediaJson($websiteId, $mediaId);
      }
    }
    if ($zipAndRemoveDirectories) {
      $zipfile = $this->createExportZip();
      $this->deleteExportDirectories();
      return $zipfile;
    }
  }

  /**
   * @param string  $websiteId
   * @param array   $templateIds
   * @param bool $zipAndRemoveDirectories Defaults to false
   * @param bool $complete                Defaults to false
   * @return string
   */
  private function exportTemplates(
      $websiteId,
      array $templateIds,
      $zipAndRemoveDirectories = false,
      $complete = false
  ) {
    $templates = $this->execute(
        'getTemplatesByWebsiteIdAndTemplateIds',
        array($websiteId, $templateIds)
    );

    $templatesDirectory = $this->currentExportDirectory
      . DIRECTORY_SEPARATOR . 'templates';
    $exportMediaDirectory = $this->currentExportDirectory
      . DIRECTORY_SEPARATOR . 'media';

    mkdir($templatesDirectory);

    foreach ($templates as $template) {
      $templateDirectory = $templatesDirectory
        . DIRECTORY_SEPARATOR . $template->getId();
      mkdir($templateDirectory);

      $templateJsonFilename = $templateDirectory
        . DIRECTORY_SEPARATOR . 'template.json';
      file_put_contents($templateJsonFilename, $this->getTemplateJson($template));
    }

    $relatedMediaIds = $this->getMediaIdsForTemplates($templates);

    if (count($relatedMediaIds) > 0) {
      if (!is_dir($exportMediaDirectory)) {
        mkdir($exportMediaDirectory);
      }

      foreach ($relatedMediaIds as $mediaId) {
        $this->copyMediaAndCreateMediaJson($websiteId, $mediaId);
      }
    }

    $relatedModuleIds = array();
    if ($complete) {
      $allModulesOfWebsite = $this->getModuleBusiness()->getAll($websiteId);
      if (is_array($allModulesOfWebsite) && count($allModulesOfWebsite) > 0) {
        foreach ($allModulesOfWebsite as $moduleOfWebsite) {
          $relatedModuleIds[] = $moduleOfWebsite->getId();
        }
      }
    } else {
      $templateBusiness = $this->getTemplateBusiness();
      foreach ($templates as $template) {
        $relatedModuleIds = array_merge(
            $relatedModuleIds,
            $templateBusiness->getUsedModuleIds($template->getWebsiteId(), $template->getId())
        );
      }
    }
    $relatedModuleIds = array_unique($relatedModuleIds);
    
    $this->exportModule($websiteId, $relatedModuleIds);

    if ($zipAndRemoveDirectories) {
      $zipfile = $this->createExportZip();
      $this->deleteExportDirectories();
      return $zipfile;
    }
  }

  /**
   * @param string  $websiteId
   * @param array   $modulIds
   * @param bool $zipAndRemoveDirectories Defaults to false
   * @param bool $complete                Defaults to false
   * @return string
   */
  protected function exportModule(
      $websiteId,
      array $modulIds,
      $zipAndRemoveDirectories = false,
      $complete = false
  ) {
    /** @var \Cms\Data\Modul[] $modules */
    $modules = $this->getModuleBusiness()->getByIds($modulIds, $websiteId);
    foreach ($modules as $module) {
      if (!$module->getSource()->isExportable()) {
        continue;
      }
      $this->copyModule($module);
    }

    if ($zipAndRemoveDirectories) {
      $zipfile = $this->createExportZip();
      $this->deleteExportDirectories();
      return $zipfile;
    }
  }
  

  /**
   * @param string  $websiteId
   * @param array   $templateSnippetIds
   * @param bool $zipAndRemoveDirectories Defaults to false
   * @param bool $complete                Defaults to false
   * @return string
   */
  protected function exportTemplateSnippets(
      $websiteId,
      array $templateSnippetIds,
      $zipAndRemoveDirectories = false,
      $complete = false
  ) {
    $templateSnippetsDirectory = $this->currentExportDirectory
      . DIRECTORY_SEPARATOR . 'templatesnippets';
    $exportMediaDirectory = $this->currentExportDirectory
      . DIRECTORY_SEPARATOR . 'media';
    
    if (!is_dir($templateSnippetsDirectory)) {
      mkdir($templateSnippetsDirectory);
    }

    if ($complete) {
      $templateSnippets = $this->getTemplateSnippetBusiness()->getAll($websiteId);
    } else {
      $templateSnippets = $this->getTemplateSnippetBusiness()->getByIds($websiteId, $templateSnippetIds);
    }

    /** @var $templateSnippets \Cms\Data\TemplateSnippet[] */
    foreach ($templateSnippets as $templateSnippet) {
    // export only local snippets
      if ($templateSnippet->getSourceType() != $templateSnippet::SOURCE_LOCAL) {
        continue;
      }

      $templateSnippetDirectory = $templateSnippetsDirectory
        . DIRECTORY_SEPARATOR . $templateSnippet->getId();
      mkdir($templateSnippetDirectory);

      $templateSnippetJsonFilename = $templateSnippetDirectory
        . DIRECTORY_SEPARATOR . 'templateSnippet.json';
      file_put_contents($templateSnippetJsonFilename, $this->getTemplateSnippetJson($templateSnippet));
    }

    $relatedMediaIds = $this->getMediaIdsForTemplateSnippets($templateSnippets);
    if (count($relatedMediaIds) > 0) {
      if (!is_dir($exportMediaDirectory)) {
        mkdir($exportMediaDirectory);
      }
      foreach ($relatedMediaIds as $mediaId => $useCount) {
        $this->copyMediaAndCreateMediaJson($websiteId, $mediaId);
      }
    }

    if ($zipAndRemoveDirectories) {
      $zipfile = $this->createExportZip();
      $this->deleteExportDirectories();
      return $zipfile;
    }
  }

  /**
   * @param string $websiteId
   * @param array  $packageIds
   * @param bool   $zipAndRemoveDirectories Defaults to false
   * @param bool   $complete                Defaults to false
   *
   * @return string
   */
  protected function exportPackages(
      $websiteId,
      array $packageIds,
      $zipAndRemoveDirectories = false,
      $complete = false
  ) {
    $basePackageExportDirectory = FS::joinPath($this->currentExportDirectory, 'packages');

    /** @var DataPackage[] $packages */
    $allPackages = $this->getPackageBusiness()->getAll($websiteId);
    if ($complete) {
      $packages = $allPackages;
    } else {
      $packages = array();
      foreach ($allPackages as $package) {
        if (in_array($package->getId(), $packageIds)) {
          $packages[] = $package;
        }
      }
    }

    foreach ($packages as $package) {
      // export only local snippets
      if ($package->getSourceType() != AbstractSourceItem::SOURCE_LOCAL) {
        continue;
      }
      $this->copyLocalPackage($basePackageExportDirectory, $package);
    }

    if ($zipAndRemoveDirectories) {
      $zipfile = $this->createExportZip();
      $this->deleteExportDirectories();
      return $zipfile;
    }
  }

  /**
   * @param DataPackage $basePackageExportDirectory
   * @param DataPackage $package
   */
  protected function copyLocalPackage($basePackageExportDirectory, DataPackage $package)
  {
    $sourcePackageDirectory = $package->getSource()->getDirectory();
    if (!is_dir($sourcePackageDirectory)) {
      Registry::getLogger()->log(
          __CLASS__,
          __METHOD__,
          sprintf(
              "Error exporting package '%s/%s'. Package source directory '%s' not exists.",
              $package->getWebsiteid(),
              $package->getId(),
              $sourcePackageDirectory
          ),
          SbLog::ERR
      );
      return;
    }

    $packageExportDirectory = FS::joinPath($basePackageExportDirectory, $package->getId());
    FS::createDirIfNotExists($packageExportDirectory, true);
    FS::copyDir($sourcePackageDirectory, $packageExportDirectory);
  }

  protected function deleteLeftoverExport()
  {
    if (!$this->checkCurrentExportDirectory()) {
      return;
    }
            
    $iterator  = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($this->currentExportDirectory),
        \RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $path) {
      if (!$iterator->isDot()) {
        $file = $path->getPathname();
        if ($path->isDir()) {
          rmdir($path->getPathname());
        } else {
          unlink($path->getPathname());
        }
      }
    }
    if (is_dir($this->currentExportDirectory)) {
      rmdir($this->currentExportDirectory);
    }
  }
  
  protected function deleteExportDirectories()
  {
    if (!$this->checkCurrentExportDirectory()) {
      return;
    }

    $iterator = new \DirectoryIterator($this->currentExportDirectory);
    foreach ($iterator as $fileInfo) {
      if ($fileInfo->isDot()) {
        continue;
      }
      if ($fileInfo->getExtension() == self::EXPORT_FILE_EXTENSION) {
        continue;
      }
      $pathName = $fileInfo->getPathName();
      if (!strstr($pathName, $this->currentExportDirectory)) {
        continue;
      }
      FS::rmdir($pathName);
    }
  }
  
  protected function checkCurrentExportDirectory()
  {
    $currentExportDirectory = realpath($this->currentExportDirectory);
    if (empty($currentExportDirectory)) {
      return false;
    }
    if (strpos($currentExportDirectory, realpath($this->exportDirectory)) != 0) {
      return false;
    }
    if (!file_exists($currentExportDirectory)) {
      return false;
    }
    return true;
  }
  
  /**
   * @param  string $filename
   * @return string
   */
  private function getExtensionFromFilename($filename)
  {
    return substr(strrchr($filename, '.'), 1);
  }
  /**
   * Gets the media ids for the given page
   *
   * @param  \Cms\Data\Page
   * @return array
   */
  private function getMediaIdsForPage(\Cms\Data\Page $page)
  {
    $regexpMedia = '(' . preg_quote(Unit\Media::ID_PREFIX, '/')
      . '.*?' . preg_quote(Unit\Media::ID_SUFFIX, '/') . ')';

    $mediaIds = array();
    $relatedColumns = array('Content', 'Templatecontent');

    foreach ($relatedColumns as $column) {
      $getMethod = 'get' . $column;
      $matchArray = lcfirst($column) . 'Matches';

      if (preg_match_all(
          '/' . $regexpMedia . '/',
          $page->$getMethod(),
          $matchArray,
          PREG_SET_ORDER
      )) {
        foreach ($matchArray as $matches) {
          foreach ($matches as $match) {
            $mediaIds[] = $match;
          }
        }
      }
    }
    return array_unique($mediaIds);
  }
  
  /**
   * Gets the media ids for the given templates (Orm\Entity\Template)
   *
   * @param  array $templates
   * @return array
   */
  private function getMediaIdsForTemplates(array $templates)
  {
    $regexpMedia = '(' . preg_quote(Unit\Media::ID_PREFIX, '/')
      . '.*?' . preg_quote(Unit\Media::ID_SUFFIX, '/') . ')';

    $mediaIds = array();
    $relatedColumns = array('Content');

    foreach ($templates as $template) {
      foreach ($relatedColumns as $column) {
        $getMethod = 'get' . $column;
        $matchArray = lcfirst($column) . 'Matches';
        if (preg_match_all(
            '/' . $regexpMedia . '/',
            $template->$getMethod(),
            $matchArray,
            PREG_SET_ORDER
        )) {
          foreach ($matchArray as $matches) {
            foreach ($matches as $match) {
              $mediaIds[] = $match;
            }
          }
        }
      }
    }
    return array_unique($mediaIds);
  }

  /**
   * Gets the media ids for the given templateSnippets (Orm\Entity\TemplateSnippet)
   *
   * @param  array $templateSnippets
   * @return array
   */
  private function getMediaIdsForTemplateSnippets(array $templateSnippets)
  {
    $regexpMedia = '(' . preg_quote(Unit\Media::ID_PREFIX, '/')
      . '.*?' . preg_quote(Unit\Media::ID_SUFFIX, '/') . ')';

    $mediaIds = array();
    $relatedColumns = array('Content');

    foreach ($templateSnippets as $templateSnippet) {
      foreach ($relatedColumns as $column) {
        $getMethod = 'get' . $column;
        $matchArray = lcfirst($column) . 'Matches';
        $value = $templateSnippet->$getMethod();
        if (preg_match_all('/' . $regexpMedia . '/', $value, $matchArray, PREG_SET_ORDER)) {
          foreach ($matchArray as $matches) {
            foreach ($matches as $match) {
              $mediaIds[$match] = isset($mediaIds[$match]) ? ($mediaIds[$match] + 1) : 1;
            }
          }
        }
      }
    }
    return array_unique($mediaIds);
  }

  /**
   * @return string Name of the export zip file
   * @throws \Cms\Exception
   */
  protected function createExportZip()
  {
    $zipFile = $this->currentExportDirectory
      . DIRECTORY_SEPARATOR . $this->currentExportName . '.'. self::EXPORT_FILE_EXTENSION;

    $zip = new \ZipArchive();
    if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
      throw new CmsException(36, __METHOD__, __LINE__, array('file' => $zipFile));
    }
    
    $iterator  = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($this->currentExportDirectory),
        \RecursiveIteratorIterator::SELF_FIRST
    );
    while ($iterator->valid()) {
      if (!$iterator->isDot()) {
        if ($iterator->isDir()) {
          $test = $iterator->getSubPathName();
          $zip->addEmptyDir(str_replace('\\', '/', $iterator->getSubPathName()));
        } else {
          $test = $iterator->getSubPathName();
          $zip->addFile($iterator->key(), str_replace('\\', '/', $iterator->getSubPathName()));
        }
      }
      $iterator->next();
    }
    $zip->close();

    return $zipFile;
  }
  
  /**
   * Copies the whole module to the export module directory
   * @param DataModule $module
   */
  private function copyModule($module)
  {
    $moduleDirectory = $module->getSource()->getDirectory();
    $exportModuleDirectory = $this->getExportModuleDirectory($module->getId());

    if (!is_dir($moduleDirectory)) {
      Registry::getLogger()->log(
          __CLASS__,
          __METHOD__,
          sprintf(
              "Error exporting module '%s/%s'. Module source directory '%s' not exists.",
              $module->getWebsiteid(),
              $module->getId(),
              $moduleDirectory
          ),
          SbLog::ERR
      );
      return;
    }

    FS::createDirIfNotExists($exportModuleDirectory, true);
    FS::copyDir($moduleDirectory, $exportModuleDirectory);
  }

  private function getExportModuleDirectory($moduleId)
  {
      return FS::joinPath($this->currentExportDirectory, 'modules', $moduleId);
  }
  
  /**
   * @param  string $websiteId
   * @param  string $mediaId
   * @return boolean
   * @throws \Exception
   */
  private function copyMediaAndCreateMediaJson($websiteId, $mediaId)
  {
    if (isset($this->exportedItemsIds[$websiteId]['media'][$mediaId])
        && $this->exportedItemsIds[$websiteId]['media'][$mediaId] === true
    ) {
      // media already exported
      return;
    }

    $sourceMediaDirectory = $this->mediaDirectory
      . DIRECTORY_SEPARATOR . $websiteId;

    if (!is_dir($sourceMediaDirectory)) {
      $exceptionMessage = sprintf(
          "Source media directory '%s' doesn't exist",
          $sourceMediaDirectory
      );
      throw new \Exception($exceptionMessage);
    }

    try {
      $media = $this->getMediaBusiness()->getById($mediaId, $websiteId);
    } catch (\Cms\Exception $e) {
      $media = null;
    }

    if ($media instanceof Data\Media) {
      $exportMediaDirectory = $this->currentExportDirectory
        . DIRECTORY_SEPARATOR . 'media'
        . DIRECTORY_SEPARATOR . $mediaId;

      if (!is_dir($exportMediaDirectory)) {
        mkdir($exportMediaDirectory);
      }

      $sourceMediaFile = $sourceMediaDirectory
        . DIRECTORY_SEPARATOR . $media->getFile();
      $exportMediaFile = $exportMediaDirectory
        . DIRECTORY_SEPARATOR . $media->getFile();
      copy($sourceMediaFile, $exportMediaFile);

      $mediaJsonFilename =  $exportMediaDirectory
        . DIRECTORY_SEPARATOR . 'media.json';
      file_put_contents($mediaJsonFilename, $this->getMediaJson($media));

      if ($media->getAlbumId() !== null) {
        $this->addAlbumEntryToAlbumJson($websiteId, $media->getAlbumId());
      }
      
      // mark as exported
      $this->exportedItemsIds[$websiteId]['media'][$mediaId] = true;
    }
  }

  /**
   * @param string $websiteId
   * @param string $albumId
   */
  private function addAlbumEntryToAlbumJson($websiteId, $albumId)
  {
    $albumJsonFilename = $this->currentExportDirectory
      . DIRECTORY_SEPARATOR . 'media'
      . DIRECTORY_SEPARATOR . 'album.json';

    if (!file_exists($albumJsonFilename)) {
      $this->createAlbumJson();
    }

    $currentAlbumJsonContent = file_get_contents($albumJsonFilename);

    if ($currentAlbumJsonContent === '') {
      $album = $this->getAlbumBusiness()->getById($albumId, $websiteId);

      $initialAlbumJson['albums'][] = array(
        'id' => $albumId,
        'name' => $album->getName()
      );
      file_put_contents($albumJsonFilename, json_encode($initialAlbumJson));
    } else {
      $currentAlbumJsonContentAsArray = json_decode($currentAlbumJsonContent, true);
      $alreadyStoredAlbums = $currentAlbumJsonContentAsArray['albums'];
      
      foreach ($alreadyStoredAlbums as $index => $album) {
        foreach ($album as $key => $value) {
          if ($key === 'id') {
            if ($value === $albumId) {
              return;
            }
          }
        }
      }
      $album = $this->getAlbumBusiness()->getById($albumId, $websiteId);

      $currentAlbumJsonContentAsArray['albums'][] = array(
        'id' => $albumId,
        'name' => $album->getName()
      );
      file_put_contents(
          $albumJsonFilename,
          json_encode($currentAlbumJsonContentAsArray)
      );
    }
  }
  /**
   * @return boolean
   */
  private function createAlbumJson()
  {
    $albumJsonFilename = $this->currentExportDirectory
      . DIRECTORY_SEPARATOR . 'media'
      . DIRECTORY_SEPARATOR . 'album.json';

    if (!file_exists($albumJsonFilename)) {
      file_put_contents($albumJsonFilename, '');
    }
    return true;
  }
  
  /**
   * @param string   $exportName
   * @return boolean
   */
  private function createExportDirectory($exportName)
  {
    $this->currentExportName = md5($exportName);
    $this->currentExportDirectory = FS::joinPath($this->exportDirectory, $this->currentExportName);
    
    if (is_dir($this->currentExportDirectory)) {
      $this->deleteLeftoverExport();
    }
    return FS::createDirIfNotExists($this->currentExportDirectory, true, 0750);
  }

  /**
   * @param  string $websiteId
   * @return string
   */
  private function getUsergroupJson($websiteId)
  {
    $usergroupJson = array();

    $groups = $this->getService('Group')->existsGroupsForWebsite($websiteId, true);

    if (is_array($groups) && count($groups) > 0) {
      foreach ($groups as $group) {
        $groupMinified = array(
          'id' => $group->getId(),
          'name' => $group->getName(),
          'rights' => $group->getRights(),
        );
        $usergroupJson[] = $groupMinified;
      }
    }

    return json_encode($usergroupJson);
  }
  /**
   * @return integer
   */
  private function getExportTime()
  {
    return time();
  }
  
  /**
   * @param  string $websiteId
   * @param  string $mode
   * @return string
   */
  private function getExportJson($websiteId, $mode)
  {
    $userlogin = 'unknown-userlogin';
    
    $identity = $this->getAccessManager()->getIdentityAsArray();
    if (isset($identity['email']) && !empty($identity['email'])) {
      $userlogin = $identity['email'];
    }
    
    $export = array(
      'version' => Version::EXPORT,
      'date' => $this->getExportTime(),
      'websiteId' => $websiteId,
      'user' => $userlogin,
      'mode' => $mode
    );
    
    return json_encode($export);
  }

  /**
   * @param Data\Media $media
   * @return string
   */
  private function getMediaJson(Data\Media $media)
  {
    $mediaColumnsAndValues = $media->getExportColumnsAndValues();
    return json_encode($mediaColumnsAndValues);
  }

  /**
   * @param  Data\Page $page
   * @return string
   */
  private function getPageJson(Data\Page $page)
  {
    $pageColumnsAndValues = $page->getExportColumnsAndValues();
    $pageJson = json_encode($pageColumnsAndValues);
    $this->extendingColorIds($page->getWebsiteid(), $pageJson);
    return $pageJson;
  }

  /**
   * @param  Data\Website $website
   * @return string
   */
  private function getWebsiteJson(Data\Website $website)
  {
    $websiteColumnsAndValues = $website->getExportColumnsAndValues();
    return json_encode($websiteColumnsAndValues);
  }

  /**
   * @param  Data\WebsiteSettings[] $allWebsiteSettings
   * @return string
   */
  private function getWebsiteSettingsJson(array $allWebsiteSettings)
  {
    $allExportWebsiteSettings = new \stdClass();
    foreach ($allWebsiteSettings as $id => $websiteSettings) {
      $allExportWebsiteSettings->$id = $websiteSettings->getExportColumnsAndValues();
    }
    return json_encode($allExportWebsiteSettings);
  }

  /**
   * @param  Data\Template $template
   * @return string
   */
  private function getTemplateJson(Data\Template $template)
  {
    $templateColumnsAndValues = $template->getExportColumnsAndValues();
    $templateJson = json_encode($templateColumnsAndValues);
    $this->extendingColorIds($template->getWebsiteid(), $templateJson);
    return $templateJson;
  }
  
  /**
   * @param Data\TemplateSnippet $templateSnippet
   * @return string
   */
  private function getTemplateSnippetJson(Data\TemplateSnippet $templateSnippet)
  {
    $templateSnippetColumnsAndValues = $templateSnippet->getExportColumnsAndValues();
    $templateSnippetJson = json_encode($templateSnippetColumnsAndValues);
    $this->extendingColorIds($templateSnippet->getWebsiteid(), $templateSnippetJson);
    return $templateSnippetJson;
  }
  
  /**
   * @param  string $exportName
   * @param  string $websiteId
   * @param  string $mode
   * @return string
   */
  protected function createCleanExportName($exportName, $websiteId, $mode)
  {
    if ($exportName === null) {
      try {
        $website = $this->getWebsiteBusiness()->getById($websiteId);
        $websiteName = $website->getName();
      } catch (\Cms\Exception $e) {
        $websiteName = 'noname';
      }
      
      $exportName = sprintf(
          '%s_%s_%s',
          $websiteName,
          strtolower($mode),
          $this->getExportTime()
      );
    }
    
    return $this->cleanExportName($exportName);
  }
  
  /**
   * Extends the ColorIds included in the string with the original color value
   *
   * @param  string     $websiteId
   * @param  mixed ref  $mixedData
   */
  protected function extendingColorIds($websiteId, &$mixedData)
  {
    if (is_string($mixedData)) {
      $replaceCallback = $this->getExtendingColorIdCallback($websiteId);
      $regexpColorId = '/((' . preg_quote(DataColorItem::ID_PREFIX, '/')
        . ')(.*?)(' . preg_quote(DataColorItem::ID_SUFFIX, '/') . '))/i';
      $mixedData = preg_replace_callback($regexpColorId, $replaceCallback, $mixedData);
      
    } elseif (is_array($mixedData)) {
      foreach ($mixedData as $key => &$item) {
        // extending colorIds in item value
        $this->extendingColorIds($websiteId, $item);
        
        // extending colorIds in key value
        $orgKey = $key;
        $this->extendingColorIds($websiteId, $key);
        if ($orgKey != $key) {
          $mixedData[$key] = $mixedData[$orgKey];
          unset($mixedData[$orgKey]);
        }
      }
    }
  }
  
  /**
   * Extends the found ColorIds
   *
   * @param  string   $websiteId
   * @return callback
   */
  protected function getExtendingColorIdCallback($websiteId)
  {
    if (isset($this->extendingColorIdCallbacks[$websiteId])
        && is_callable($this->extendingColorIdCallbacks[$websiteId])
    ) {
      return $this->extendingColorIdCallbacks[$websiteId];
    }
    
    $website = $this->getWebsiteBusiness()->getById($websiteId);
    $colorscheme = $website->getColorscheme();
    $colors = array();
    
    // decode colorscheme
    if (is_string($colorscheme)) {
      try {
        // JSON decodieren und Array aufbereiten
        $colorscheme = \Zend_Json::decode($colorscheme);
      } catch (\Exception $e) {
      // Fehler
        Registry::getLogger()->log(__CLASS__, __METHOD__, 'Farbeschema konnte nicht erstellt werden: '.$e->errorMessage, SbLog::WARN);
      }
    }
    
    // create color values
    if (is_array($colorscheme)) {
      foreach ($colorscheme as $colorValue) {
        $nextColor = new DataColorItem($colorValue);
        $colors[$nextColor->getBaseId()] = $nextColor;
      }
    }
    
    // create callback
    $this->extendingColorIdCallbacks[$websiteId] = function ($matches) use ($colors) {
      $orgColorId = $matches[0];
      $baseColorId = WebsiteColor::getBaseIdFromColorId($orgColorId);
      if (isset($colors[$baseColorId])) {
        // return colorId with fallback color
        return $colors[$baseColorId]->getIdWithFallbackColor();
      }
      
      // return original colorId
      return $orgColorId;
    };
    
    return $this->extendingColorIdCallbacks[$websiteId];
  }

  
  /**
   * @param  string   $mode
   * @param  string   $websiteId
   * @param  string   $id
   */
  protected function insertScreenshot($mode, $websiteId, $id = null)
  {
    $screenshotBusiness = $this->getScreenshotBusiness();
    $screenshotFile = $screenshotBusiness->getScreenshotFilePath($websiteId, null, $screenshotBusiness::SCREEN_TYPE_WEBSITE);
    if (empty($screenshotFile) || !file_exists($screenshotFile)) {
      return;
    }

    $filePathInExport = FS::joinPath($this->currentExportDirectory, 'screenshot.jpg');
    $image = $this->getImageAdapter();
    $image->setFile($screenshotFile);
    $imageInfo = $image->getImageInfo();
    if ($imageInfo['type'] != $image::TYPE_JPG) {
      $image->load();
      $image->quality(array('quality' => 100));
      $image->save($filePathInExport, $image::TYPE_JPG);
    } else {
      FS::copyFile($screenshotFile, $filePathInExport);
    }
  }

  /**
   * @return \Seitenbau\Image\Image
   */
  protected function getImageAdapter()
  {
    $imageAdapterConfig = null;
    $config = Registry::getConfig();
    if (isset($config->export->imageAdapter)) {
      $imageAdapterConfig = $config->export->imageAdapter;
    }
    return ImageTool::factory($imageAdapterConfig);
  }

  /**
   * @return \Cms\Business\Modul
   */
  protected function getModuleBusiness()
  {
    if (isset($this->moduleBusiness)) {
      return $this->moduleBusiness;
    }
    
    $this->moduleBusiness = $this->getBusiness('Modul');
    return $this->moduleBusiness;
  }

  /**
   * @return \Cms\Business\Screenshot
   */
  protected function getScreenshotBusiness()
  {
    if (isset($this->screenshotBusiness)) {
      return $this->screenshotBusiness;
    }
    
    $this->screenshotBusiness = $this->getBusiness('Screenshot');
    return $this->screenshotBusiness;
  }

  /**
   * @return \Cms\Business\Template
   */
  protected function getTemplateBusiness()
  {
    if (isset($this->templateBusiness)) {
      return $this->templateBusiness;
    }
    
    $this->templateBusiness = $this->getBusiness('Template');
    return $this->templateBusiness;
  }

  /**
   * @return \Cms\Business\Website
   */
  protected function getWebsiteBusiness()
  {
    if (isset($this->websiteBusiness)) {
      return $this->websiteBusiness;
    }
    
    $this->websiteBusiness = $this->getBusiness('Website');
    return $this->websiteBusiness;
  }
  
  /**
   * @return \Cms\Business\Media
   */
  protected function getMediaBusiness()
  {
    if (isset($this->mediaBusiness)) {
      return $this->mediaBusiness;
    }
    
    $this->mediaBusiness = $this->getBusiness('Media');
    return $this->mediaBusiness;
  }
  
  /**
   * @return \Cms\Business\Album
   */
  protected function getAlbumBusiness()
  {
    if (isset($this->albumBusiness)) {
      return $this->albumBusiness;
    }
    
    $this->albumBusiness = $this->getBusiness('Album');
    return $this->albumBusiness;
  }
  
  /**
   * @return \Cms\Business\TemplateSnippet
   */
  protected function getTemplateSnippetBusiness()
  {
    if (isset($this->templateSnippetBusiness)) {
      return $this->templateSnippetBusiness;
    }
    
    $this->templateSnippetBusiness = $this->getBusiness('TemplateSnippet');
    return $this->templateSnippetBusiness;
  }

  /**
   * @return \Cms\Business\Page
   */
  protected function getPageBusiness()
  {
    if (isset($this->pageBusiness)) {
      return $this->pageBusiness;
    }
    $this->pageBusiness = $this->getBusiness('Page');
    return $this->pageBusiness;
  }

  /**
   * @return \Cms\Business\WebsiteSettings
   */
  protected function getWebsiteSettingsBusiness()
  {
    if (isset($this->websiteSettingsBusiness)) {
      return $this->websiteSettingsBusiness;
    }
    $this->websiteSettingsBusiness = $this->getBusiness('WebsiteSettings');
    return $this->websiteSettingsBusiness;
  }

  /**
   * @return \Cms\Business\Package
   */
  protected function getPackageBusiness()
  {
    if (isset($this->packageBusiness)) {
      return $this->packageBusiness;
    }
    $this->packageBusiness = $this->getBusiness('Package');
    return $this->packageBusiness;
  }

  /**
   * Checks if the export is allowed. Throws Exception if not!
   *
   * @throws \Cms\Exception
   */
  protected function checkExportQuota()
  {
    $quota = new Quota();
    $exportQuota = $quota->getExportQuota();
    if (!$exportQuota->getExportAllowed()) {
      throw new CmsException(2302, __METHOD__, __LINE__);
    }
  }
}
