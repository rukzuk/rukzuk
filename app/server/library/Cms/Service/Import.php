<?php
namespace Cms\Service;

use Cms\Dao\Factory as DaoFactory;
use Cms\Exception as CmsException;
use Cms\Service\Base\Dao as DaoServiceBase;
use Cms\Business\Export as ExportBusiness;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\ModuleId as ModuleIdValidator;
use Orm\Data\Media as DataMedia;
use Orm\Data\TemplateSnippet as DataTemplateSnippet;
use Orm\Data\Page as DataPage;
use Orm\Data\Template as DataTemplate;
use Orm\Data\Website\ColorItem as DataColorItem;
use Dual\Media\Type as MediaType;
use Seitenbau\Registry as Registry;
use Seitenbau\FileSystem as FS;
use Cms\Business\Lock as LockBusiness;
use Dual\Render\WebsiteColor as WebsiteColor;
use Cms\Data\Modul as DataModule;
use Cms\Quota;
use Cms\Service\Import\ConflictException;
use Cms\Business\Import\Latch as LatchBusiness;
use Cms\Version;
use Cms\ExceptionStack as CmsExceptionStack;
use Seitenbau\Log as SbLog;
use Cms\Validator\PackageId as PackageIdValidator;
use Cms\ContentUpdater\LegacyDefaultFormValuesUpdater as LegacyDefaultFormValuesUpdater;

/**
 * Import Service
 *
 * TODO: please refactor my methods
 */
class Import extends DaoServiceBase
{
  const MODULE_LEGACY_FILE_RENDERER = 'moduleRenderer.php';
  const MODULE_LEGACY_FILE_CSS = 'moduleCss.php';
  const MODULE_LEGACY_FILE_HEADER = 'moduleHeader.php';

  /**
   * @var string
   */
  private $currentImportFile;
  /**
   * @var string
   */
  private $currentImportUnzipDirectory;
  /**
   * @var string
   */
  private $mediaDirectory;
  /**
   * @var string
   */
  private $importDirectory;
  /**
   * @var array
   */
  private $availableImportModes;
  /**
   * Name des Albums welcher verwendet werden soll wenn eine Import
   * gegen einen Export aus Pre-Album Zeiten durchgefuehrt wird.
   *
   * @var string
   */
  private $defaultAlbumName;
  /**
   * @var \Cms\Business\Lock
   */
  private $lockBusiness;
  /**
   * @var \Cms\Service\Modul
   */
  private $moduleService;
  /**
   * @var \Cms\Service\Website
   */
  private $websiteService;
  /**
   * @var \Cms\Service\TemplateSnippet
   */
  private $templateSnippetService;
  /**
   * @var \Cms\Service\Media
   */
  private $mediaService;
  /**
   * @var \Cms\Service\WebsiteSettings
   */
  private $websiteSettingsService;
  /**
   * @var \Cms\Service\PageType
   */
  private $pageTypeService;
  /**
   * @var \Cms\Service\Package
   */
  private $packageService;
  /**
   * @var ContentUpdaterService
   */
  private $contentUpdaterService;
  /**
   * @var array
   */
  private $currentConflictItems = array();
  /**
   * @var array
   */
  private $currentImportMediaInfo = array();
  /**
   * @var array
   */
  private $currentImportTemplateSnippetInfo = array();
  /**
   * @var array
   */
  private $currentImportModuleInfo = array();
  /**
   * @var array
   */
  private $currentImportTemplateInfo = array();
  /**
   * @var array
   */
  private $currentImportPageInfo = array();
  /**
   * @var array
   */
  private $currentImportPackageInfo = array();
  /**
   * @var array
   */
  private $cleaningColorIdCallbacks = array();
  /**
   * @var callable
   */
  private $resetColorIdCallback = null;
  /**
   * @var \Orm\Entity\Album
   */
  private $defaultAlbum = null;
  /**
   * @var \Cms\Dao\Album
   */
  private $albumDao = null;
  /**
   * @var string|null
   */
  private $defaultPageTypeId = null;

  /**
   * @var \Cms\Validator\ModuleId
   */
  private $moduleIdValidator = null;

  /**
   * @var \Cms\Validator\PackageId
   */
  private $packageIdValidator;

  public function __construct()
  {
    parent::__construct('Import');
    $this->availableImportModes = array(
      ExportBusiness::EXPORT_MODE_TEMPLATESNIPPET,
      ExportBusiness::EXPORT_MODE_MODULE,
      ExportBusiness::EXPORT_MODE_WEBSITE,
    );
    $config = Registry::getConfig();
    $this->defaultAlbumName = $config->import->default->album->name;
    $this->defaultAlbum = null;
    $this->mediaDirectory = realpath($config->media->files->directory);
    $this->importDirectory = realpath($config->import->directory);

    $this->lockBusiness = new LockBusiness('Lock');
  }

  /**
   * @param array  $templateIds
   * @param array  $moduleIds
   * @param array  $templateSnippetIds
   * @param array  $mediaIds
   * @param string $importId
   *
   * @throws \Cms\Exception
   * @throws \Exception
   * @return array|null
   */
  public function overwriteImport(
      array $templateIds,
      array $moduleIds,
      array $templateSnippetIds,
      array $mediaIds,
      $importId
  ) {
    $latch = $this->getLatchBusiness();
    if (!$latch->existsLatchDataForImport($importId)) {
      return null;
    }

    $latchData = $latch->getLatchDataForImport($importId);
    if (!isset($latchData['file']) || !isset($latchData['websiteId'])) {
      throw new CmsException(1, __METHOD__, __LINE__);
    }
    $websiteId = $latchData['websiteId'];
    $latchedImportFile = $latchData['file'];
    $importFile = $this->importDirectory
      . DIRECTORY_SEPARATOR . basename($latchedImportFile);

    copy($latchedImportFile, $importFile);

    $this->currentImportFile = $this->importDirectory
      . DIRECTORY_SEPARATOR . basename($importFile);

    $this->currentImportUnzipDirectory = dirname($this->currentImportFile)
      . DIRECTORY_SEPARATOR . basename($this->currentImportFile, '.zip');

    try {
      $this->unzipImport();
      $this->prepareImport($websiteId, null, true);

      $importData = $this->_import(
          $websiteId,
          null,
          true,
          $templateIds,
          $moduleIds,
          $templateSnippetIds,
          $mediaIds
      );

      $this->removeImportFiles($this->currentImportFile);
      $latch->unlatchImportFile($importId);

      if (CmsExceptionStack::hasErrors()) {
        CmsExceptionStack::throwErrors();
      }
    } catch (\Exception $e) {
      $latch->unlatchImportFile($importId);
      $this->removeImportFiles($this->currentImportFile);
      throw $e;
    }

    return $importData;
  }

  /**
   * @param string      $websiteId
   * @param string      $importFile
   * @param null|string $allowedImportMode
   *
   * @throws \Exception
   * @return array|null
   */
  public function import($websiteId, $importFile, $allowedImportMode)
  {
    if (!file_exists($importFile)) {
      throw new CmsException(24, __METHOD__, __LINE__, array(
        'file' => $importFile));
    }
    $this->currentImportFile = $this->importDirectory
      . DIRECTORY_SEPARATOR . basename($importFile);

    $this->currentImportUnzipDirectory = dirname($this->currentImportFile)
      . DIRECTORY_SEPARATOR . basename($this->currentImportFile, '.zip');

    try {
      $this->unzipImport();
      $this->prepareImport($websiteId, $allowedImportMode);
      $this->handleConflicts($websiteId, $importFile);

      $importData = $this->_import($websiteId);

      if (CmsExceptionStack::hasErrors()) {
        CmsExceptionStack::throwErrors();
      }
    } catch (\Exception $e) {
      $this->removeImportFiles($this->currentImportFile);
      throw $e;
    }

    return $importData;
  }

  /**
   * @param string      $websiteId
   * @param string      $importDirectory
   * @param string|null $allowedImportMode
   * @param string|null $websiteName
   *
   * @return array|null
   * @throws CmsException
   * @throws ConflictException
   * @throws \Cms\ExceptionStackException
   * @throws \Exception
   */
  public function importFromDirectory($websiteId, $importDirectory, $allowedImportMode, $websiteName)
  {
    if (!file_exists($importDirectory)) {
      throw new CmsException(24, __METHOD__, __LINE__, array('file' => $importDirectory));
    }
    $this->currentImportFile = null;
    $this->currentImportUnzipDirectory = $importDirectory;

    $this->prepareImport($websiteId, $allowedImportMode);
    $this->handleConflicts($websiteId, $importDirectory);

    $importData = $this->_import($websiteId, $websiteName);

    if (CmsExceptionStack::hasErrors()) {
      CmsExceptionStack::throwErrors();
    }

    return $importData;
  }

  /**
   * @param null|string $websiteId
   * @param string      $importFile
   *
   * @throws Import\ConflictException
   */
  protected function handleConflicts($websiteId, $importFile)
  {
    // check if import-conflicts exists
    if (count($this->currentConflictItems) <= 0) {
      return;
    }
    $conflictData = array(
      'websiteId' => $websiteId,
      'conflict' => array(),
    );
    foreach ($this->currentConflictItems as $itemType => $conflicts) {
      $conflictData['conflict'][$itemType] = array();
      if (is_array($conflicts)) {
        $conflictData['conflict'][$itemType] = array_values($conflicts);
      }
    }
    $latchBusiness = $this->getLatchBusiness();
    $conflictData['importId'] = $latchBusiness->latchImportFile(
        $conflictData['websiteId'],
        $importFile
    );
    throw new ConflictException(11, __METHOD__, __LINE__, $conflictData);
  }

  /**
   * @param string      $websiteId
   * @param string|null $websiteName
   * @param bool        $overwrite
   * @param array       $templateIds
   * @param array       $moduleIds
   * @param array       $templateSnippetIds
   * @param array       $mediaIds
   * @param array       $packageIds
   *
   * @return array
   */
  protected function _import(
      $websiteId,
      $websiteName = null,
      $overwrite = false,
      array $templateIds = array(),
      array $moduleIds = array(),
      array $templateSnippetIds = array(),
      array $mediaIds = array(),
      array $packageIds = array()
  ) {
    $importedModules = array();
    $importedTemplateSnippets = array();
    $importedTemplates = array();
    $importedPages = array();
    $importedMedia = array();
    $importedAlbums = array();
    $importedWebsite = array();
    $importedUsergroups = array();
    $importedWebsiteSettings = array();
    $importedPackages = array();

    $importMode = $this->getImportMode();

    $this->removeLocks(
        $importMode,
        $websiteId,
        $overwrite,
        $templateIds,
        $moduleIds,
        $templateSnippetIds,
        $mediaIds
    );

    // check if website should be created
    if ($importMode !== ExportBusiness::EXPORT_MODE_WEBSITE) {
      if (!$this->getWebsiteService()->existsWebsiteAlready($websiteId)) {
        $website = $this->createNewWebsite($websiteName);
        $importedWebsite[] = array(
          'id' => $website->getId(),
          'name' => $website->getName(),
        );
        $websiteId = $website->getId();
      }
    }

    switch ($importMode) {
      case ExportBusiness::EXPORT_MODE_WEBSITE:
        $website = $this->importWebsite($importMode, $websiteName);
        $websiteId = $website->getId();
        $importedWebsite[] = array(
          'id' => $website->getId(),
          'name' => $website->getName(),
        );
        $importedModules = $this->importModule($websiteId, $overwrite, $moduleIds);
        $importedTemplateSnippets = $this->importTemplateSnippets($websiteId, $overwrite, $templateSnippetIds);
        $importedTemplates = $this->importTemplates($websiteId, $overwrite, $templateIds);
        $importedPages = $this->importPages($websiteId);
        $importedAlbums = $this->importAlbums($websiteId);
        $importedMedia = $this->importMedia($websiteId, $overwrite, $mediaIds, $importedAlbums);
        $importedUsergroups = $this->importUsergroups($websiteId);
        $importedPackages = $this->importPackages($websiteId, $overwrite, $packageIds);
        $importedWebsiteSettings = $this->importWebsiteSettingsFromJsonFile($websiteId, $this->currentImportUnzipDirectory);
            break;

      case ExportBusiness::EXPORT_MODE_MODULE:
        $importedModules = $this->importModule($websiteId, $overwrite, $moduleIds);
            break;

      case ExportBusiness::EXPORT_MODE_TEMPLATESNIPPET:
        $importedTemplateSnippets = $this->importTemplateSnippets($websiteId, $overwrite, $templateSnippetIds);
        $importedAlbums = $this->importAlbums($websiteId);
        $importedMedia = $this->importMedia($websiteId, $overwrite, $mediaIds, $importedAlbums);
            break;
    }

    return array(
      'websiteId' => $websiteId,
      'websitesettings' => $importedWebsiteSettings,
      'modules' => $importedModules,
      'templatesnippets' => $importedTemplateSnippets,
      'templates' => $importedTemplates,
      'pages' => $importedPages,
      'media' => $importedMedia,
      'albums' => array_values($importedAlbums),
      'website' => $importedWebsite,
      'usergroups' => $importedUsergroups,
      'packages' => $importedPackages,
    );
  }

  /**
   * @return string
   */
  protected function getImportMode()
  {
    $exportJson = $this->getExportJson();
    return $exportJson['mode'];
  }

  /**
   * @return string
   */
  protected function getImportVersion()
  {
    $exportJson = $this->getExportJson();
    return (isset($exportJson['version']) ? $exportJson['version'] : '0.0.0');
  }

  private function inObjectArray($searchKey, $searchValue, array $data, $rekursivKey = null)
  {
    $found = false;
    foreach ($data as $value) {
      if (property_exists($value, $searchKey) && $value->$searchKey == $searchValue) {
        return true;
      }

      if (property_exists($value, $rekursivKey) && is_array($value->$rekursivKey)) {
        $found = $this->inObjectArray($searchKey, $searchValue, $value->$rekursivKey, $rekursivKey);
      }
    }
    return $found;
  }

  /**
   * @param string      $importMode
   * @param string|null $websiteName
   *
   * @return \Cms\Data\Website
   */
  protected function importWebsite($importMode, $websiteName)
  {
    $websiteUnzipDirectory = FS::joinPath(
        $this->currentImportUnzipDirectory,
        'website'
    );

    if (is_dir($websiteUnzipDirectory)) {
      return $this->createWebsiteFromWebsiteAndNavigationJson(
          FS::joinPath($websiteUnzipDirectory, 'website.json'),
          FS::joinPath($websiteUnzipDirectory, 'navigation.json'),
          $websiteName
      );
    } else {
      return $this->createNewWebsite($websiteName);
    }
  }

  /**
   * @param  string $websiteId
   * @param  string $importMode
   */
  protected function collectConflicts($websiteId, $importMode)
  {
    $this->currentConflictItems = array();
    if (empty($websiteId)) {
      return;
    }
    if (!$this->getWebsiteService()->existsWebsiteAlready($websiteId)) {
      return;
    }

    $conflictingMedia = array();
    $conflictingTemplateSnippets = array();
    $conflictingModules = array();
    $conflictingTemplates = array();
    $conflictingPages = array();
    $conflictingPackages = array();

    switch ($importMode) {
      case ExportBusiness::EXPORT_MODE_MODULE:
        $conflictingModules = $this->collectConflictingModules($websiteId);
            break;

      case ExportBusiness::EXPORT_MODE_TEMPLATESNIPPET:
        $conflictingMedia = $this->collectConflictingMedia($websiteId);
        $conflictingTemplateSnippets = $this->collectConflictingTemplateSnippets($websiteId);
            break;
    }

    if (count($conflictingMedia) > 0) {
      $this->currentConflictItems['media'] = $conflictingMedia;
    }
    if (count($conflictingTemplateSnippets) > 0) {
      $this->currentConflictItems['templatesnippets'] = $conflictingTemplateSnippets;
    }
    if (count($conflictingModules) > 0) {
      $this->currentConflictItems['modules'] = $conflictingModules;
    }
    if (count($conflictingTemplates) > 0) {
      $this->currentConflictItems['templates'] = $conflictingTemplates;
    }
    if (count($conflictingPages) > 0) {
      $this->currentConflictItems['pages'] = $conflictingPages;
    }
    if (count($conflictingPackages) > 0) {
      $this->currentConflictItems['packages'] = $conflictingPackages;
    }
  }

  /**
   * @param   string $websiteId
   *
   * @return  array
   */
  private function collectConflictingMedia($websiteId)
  {
    $conflicts = array();

    $mediaService = $this->getMediaService();
    foreach ($this->currentImportMediaInfo as $currentMediaId => $mediaInfo) {
      if ($mediaService->existsMedia($websiteId, $currentMediaId)) {
        if (!isset($conflicts[$currentMediaId])) {
          $conflictingMedia = $mediaService->getById($currentMediaId, $websiteId);
          $conflicts[$currentMediaId] = array(
            'id' => $conflictingMedia->getId(),
            'oldName' => $conflictingMedia->getName(),
            'newName' => $mediaInfo['name'],
          );
        }
      }
    }

    return $conflicts;
  }

  /**
   * @param   string $websiteId
   *
   * @return  array
   */
  private function collectConflictingTemplateSnippets($websiteId)
  {
    $conflicts = array();

    $templateSnippetService = $this->getTemplateSnippetService();
    foreach ($this->currentImportTemplateSnippetInfo as $currentTemplateSnippetId => $templateSnippetInfo) {
      if ($templateSnippetService->existsSnippet($websiteId, $currentTemplateSnippetId)) {
        if (!isset($conflicts[$currentTemplateSnippetId])) {
          $conflictingtemplateSnippet = $templateSnippetService->getById(
              $websiteId,
              $currentTemplateSnippetId
          );
          $conflicts[$currentTemplateSnippetId] = array(
            'id' => $conflictingtemplateSnippet->getId(),
            'oldName' => $conflictingtemplateSnippet->getName(),
            'newName' => $templateSnippetInfo['name'],
          );
        }
      }
    }

    return $conflicts;
  }

  /**
   * @param   string $websiteId
   *
   * @return  array
   */
  private function collectConflictingTemplates($websiteId)
  {
    $conflicts = array();

    $templateDao = DaoFactory::get('Template');
    foreach ($this->currentImportTemplateInfo as $currentTemplateId => $templateInfo) {
      if ($templateDao->existsTemplate($currentTemplateId, $websiteId)) {
        if (!isset($conflicts[$currentTemplateId])) {
          $conflictingTemplate = $templateDao->getById($currentTemplateId, $websiteId);
          $conflicts[$currentTemplateId] = array(
            'id' => $conflictingTemplate->getId(),
            'oldName' => $conflictingTemplate->getName(),
            'newName' => $templateInfo['name'],
          );

          $locks = $this->collectingLockInfo(
              $websiteId,
              $currentTemplateId,
              'template'
          );
          if (isset($locks)) {
            $conflicts[$currentTemplateId]['locks'] = $locks;
          }
        }
      }
    }

    return $conflicts;
  }

  /**
   * @param   string $websiteId
   *
   * @return  array
   */
  private function collectConflictingModules($websiteId)
  {
    $conflicts = array();
    $moduleService = $this->getModuleService();

    foreach ($this->currentImportModuleInfo as $currentModuleId => $moduleInfo) {
      if ($moduleService->existsModulAlready($currentModuleId, $websiteId)) {
        if (!isset($conflicts[$currentModuleId])) {
          $conflictingModule = $moduleService->getById($currentModuleId, $websiteId);
          $conflicts[$currentModuleId] = array(
            'id' => $conflictingModule->getId(),
            'oldName' => $conflictingModule->getName(),
            'newName' => $moduleInfo['name'],
          );

          $locks = $this->collectingLockInfo(
              $websiteId,
              $currentModuleId,
              'module'
          );
          if (isset($locks)) {
            $conflicts[$currentModuleId]['locks'] = $locks;
          }
        }
      }
    }

    return $conflicts;
  }

  /**
   * @param string      $websiteJsonFilePath
   * @param string      $navigationJsonFilePath
   * @param string|null $websiteName
   *
   * @return \Cms\Data\Website
   */
  protected function createWebsiteFromWebsiteAndNavigationJson(
      $websiteJsonFilePath,
      $navigationJsonFilePath,
      $websiteName
  ) {
    $websiteJson = json_decode(file_get_contents($websiteJsonFilePath));
    $websiteJsonArrayObject = new \ArrayObject($websiteJson);
    $websiteJsonValues = $websiteJsonArrayObject->getArrayCopy();
    $navigationJson = null;

    if (file_exists($navigationJsonFilePath)) {
      $navigationJson = file_get_contents($navigationJsonFilePath);
    }

    if (!is_string($websiteName)) {
      $dateTimeFormatted = $this->getImportFormattedDateTime();
      $websiteName = $websiteJsonValues['name'] . '_' . $dateTimeFormatted;
    }

    $colorscheme = null;
    if (\array_key_exists('colorscheme', $websiteJsonValues)) {
      $colorscheme = $websiteJsonValues['colorscheme'];
      $this->resetColorIdsToBaseId($colorscheme);
    }

    if (\array_key_exists('resolutions', $websiteJsonValues)) {
      $resolutionsJson = $this->createResolutionsJson($websiteJsonValues['resolutions']);
    } else {
      $resolutionsJson = $this->getDefaultResolutionsJson();
    }

    $usedSetId = null;
    if (version_compare($this->getImportVersion(), '1.8.0') < 0) {
      if (isset($websiteJsonValues['modulerepository'])) {
        $usedSetId = $this->getUsedSetFromModuleRepository($websiteJsonValues['modulerepository']);
      }
    } else {
      $usedSetId = $websiteJsonValues['usedsetid'];
    }

    $columnsValues = array(
      'name' => $websiteName,
      'description' => $websiteJsonValues['description'],
      'colorscheme' => $colorscheme,
      'resolutions' => $resolutionsJson,
      'navigation' => $navigationJson,
      'home' => (isset($websiteJsonValues['home']) ? $websiteJsonValues['home'] : null),
      'usedsetid' => $usedSetId,
    );

    return $this->createWebsite($columnsValues);
  }

  /**
   * @param string $moduleRepositoryJson
   *
   * @return string|null
   */
  protected function getUsedSetFromModuleRepository($moduleRepositoryJson)
  {
    $repoConfig = json_decode($moduleRepositoryJson, true);
    if (!is_array($repoConfig) || !isset($repoConfig['id']) || empty($repoConfig['id'])) {
      return null;
    }
    return $repoConfig['id'];
  }

  /**
   * @return string
   */
  public function getImportFormattedDateTime()
  {
    $dateTime = new \DateTime();
    return $dateTime->format('d.m.Y H:i:s');
  }

  /**
   * @param  string $templateSnippetId
   * @param  string $websiteId
   * @param  string $templateSnippetJsonFilePath
   *
   * @return array
   */
  private function updateOrCreateTemplateSnippetFromTemplateSnippetJson(
      $templateSnippetId,
      $websiteId,
      $templateSnippetJsonFilePath
  ) {
    $templateSnippetJsonString = file_get_contents($templateSnippetJsonFilePath);
    $this->cleaningColorIds($websiteId, $templateSnippetJsonString);
    $templateSnippetJson = json_decode($templateSnippetJsonString);
    $templateSnippetJsonArrayObject = new \ArrayObject($templateSnippetJson);
    $templateSnippetJsonValues = $templateSnippetJsonArrayObject->getArrayCopy();

    $importElement = array(
      'id' => $templateSnippetId,
      'name' => $templateSnippetJsonValues['name']
    );

    $columnsValues = array(
      'id' => $templateSnippetId,
      'name' => $templateSnippetJsonValues['name'],
      'description' => isset($templateSnippetJsonValues['description']) ? $templateSnippetJsonValues['description'] : null,
      'category' => isset($templateSnippetJsonValues['category']) ? $templateSnippetJsonValues['category'] : null,
      'content' => isset($templateSnippetJsonValues['content']) ? $templateSnippetJsonValues['content'] : null,
    );

    if (version_compare($this->getImportVersion(), '1.7.0') < 0) {
      if (!empty($columnsValues['content'])) {
        $this->updateContentWithLegacyDefaultValues($websiteId, $columnsValues['content']);
      }
    }

    $templateSnippetService = $this->getTemplateSnippetService();
    if ($templateSnippetService->existsSnippet($websiteId, $templateSnippetId)) {
      $templateSnippet = $templateSnippetService->update($websiteId, $templateSnippetId, $columnsValues);
    } else {
      $templateSnippet = $templateSnippetService->create($websiteId, $columnsValues, true);
    }

    return $importElement;
  }

  /**
   * @param  \Cms\Service\Modul $moduleService
   * @param  string             $moduleId
   * @param  string             $websiteId
   * @param  string             $moduleJsonFilePath
   *
   * @return DataModule
   */
  private function createOrOverwriteModuleFromModuleJson(
      $moduleService,
      $moduleId,
      $websiteId,
      $moduleJsonFilePath
  ) {
    $moduleJsonString = file_get_contents($moduleJsonFilePath);
    $this->cleaningColorIds($websiteId, $moduleJsonString);
    $moduleJson = json_decode($moduleJsonString);
    $moduleJsonArrayObject = new \ArrayObject($moduleJson);
    $moduleJsonValues = $moduleJsonArrayObject->getArrayCopy();

    if (version_compare($this->getImportVersion(), '1.3.0') < 0) {
      // insert namespace
      $namespaceCode = "<?php namespace Dual\\Render; ?>\n";
      $moduleJsonValues['renderer'] = $namespaceCode . $moduleJsonValues['renderer'];
      $moduleJsonValues['css'] = $namespaceCode . $moduleJsonValues['css'];
      $moduleJsonValues['header'] = $namespaceCode . $moduleJsonValues['header'];
    }

    $newModule = new DataModule();
    $newModule->setId($moduleId);
    $newModule->setName($moduleJsonValues['name']);
    $newModule->setDescription($moduleJsonValues['description']);
    $newModule->setCategory($moduleJsonValues['category']);
    $newModule->setVersion($moduleJsonValues['version']);
    $newModule->setIcon($moduleJsonValues['icon']);
    $newModule->setModuletype($moduleJsonValues['moduleType']);
    $newModule->setAllowedchildmoduletype($moduleJsonValues['allowedChildModuleType']);

    if (isset($moduleJsonValues['form']) && is_string($moduleJsonValues['form'])) {
      $newModule->setForm(json_decode($moduleJsonValues['form']));
    } else {
      $newModule->setForm($moduleJsonValues['form']);
    }
    if (isset($moduleJsonValues['formValues']) && is_string($moduleJsonValues['formValues'])) {
      $newModule->setFormvalues(json_decode($moduleJsonValues['formValues']));
    } else {
      $newModule->setFormvalues($moduleJsonValues['formValues']);
    }

    if (isset($moduleJsonValues['reRenderRequired'])) {
      $newModule->setRerenderrequired($moduleJsonValues['reRenderRequired']);
    }
    if (isset($moduleJsonValues['customData'])) {
      $newModule->setCustomData($moduleJsonValues['customData']);
    }
    if (isset($moduleJsonValues['apiType'])) {
      $newModule->setApiType($moduleJsonValues['apiType']);
    }
    if (isset($moduleJsonValues['sessionRequired'])) {
      $newModule->setSessionRequired($moduleJsonValues['sessionRequired']);
    }
    if (isset($moduleJsonValues['config'])) {
      $newModule->setConfig($moduleJsonValues['config']);
    }
    $createdModule = $this->createOrOverwriteModule($moduleService, $websiteId, $newModule);
    $this->saveModuleLegacyCode($moduleService, $createdModule, array(
      'css' => $moduleJsonValues['css'],
      'renderer' => $moduleJsonValues['renderer'],
      'header' => $moduleJsonValues['header'],
    ));

    return $createdModule;
  }

  /**
   * @param \Cms\Service\Modul $moduleService
   * @param DataModule         $module
   * @param array              $codeArray
   *
   * @throws FS\FileSystemException
   */
  protected function saveModuleLegacyCode($moduleService, DataModule $module, array $codeArray)
  {
    $dataPath = $moduleService->getDataPath($module->getWebsiteid(), $module->getId());

    $rendererFile = FS::joinPath($dataPath, self::MODULE_LEGACY_FILE_RENDERER);
    if (isset($codeArray['renderer'])) {
      FS::writeContentToFile($rendererFile, $codeArray['renderer']);
    } elseif (file_exists($rendererFile)) {
      FS::rmFile($rendererFile);
    }

    $cssFile = FS::joinPath($dataPath, self::MODULE_LEGACY_FILE_CSS);
    if (isset($codeArray['css'])) {
      FS::writeContentToFile($cssFile, $codeArray['css']);
    } elseif (file_exists($cssFile)) {
      FS::rmFile($cssFile);
    }

    $headerFile = FS::joinPath($dataPath, self::MODULE_LEGACY_FILE_HEADER);
    if (isset($codeArray['header'])) {
      FS::writeContentToFile($headerFile, $codeArray['header']);
    } elseif (file_exists($headerFile)) {
      FS::rmFile($headerFile);
    }
  }

  /**
   * @param  string $templateId
   * @param  string $websiteId
   * @param  string $templateJsonFilePath
   *
   * @return array
   */
  private function updateOrCreateTemplateFromTemplateJson(
      $templateId,
      $websiteId,
      $templateJsonFilePath
  ) {
    $templateJsonString = file_get_contents($templateJsonFilePath);
    $this->cleaningColorIds($websiteId, $templateJsonString);
    $templateJson = json_decode($templateJsonString);
    $templateJsonArrayObject = new \ArrayObject($templateJson);
    $templateJsonValues = $templateJsonArrayObject->getArrayCopy();

    $importElement = array(
      'id' => $templateId,
      'name' => $templateJsonValues['name']
    );

    $columnsValues = array(
      'id' => $templateJsonValues['id'],
      'name' => $templateJsonValues['name'],
      'content' => $templateJsonValues['content'],
    );

    if (version_compare($this->getImportVersion(), '1.7.0') < 0) {
      if (!empty($columnsValues['content'])) {
        $this->updateContentWithLegacyDefaultValues($websiteId, $columnsValues['content']);
      }
    }

    if (array_key_exists('pageType', $templateJsonValues) && !empty($templateJsonValues['pageType'])) {
      $columnsValues['pageType'] = $templateJsonValues['pageType'];
    } else {
      $columnsValues['pageType'] = $this->getDefaultPageTypeId();
    }

    $templateDao = DaoFactory::get('Template');

    if ($templateDao->existsTemplate($templateId, $websiteId)) {
      $template = $templateDao->update($templateId, $websiteId, $columnsValues);
    } else {
      $template = $templateDao->create($websiteId, $columnsValues, true);
    }

    return $importElement;
  }

  /**
   * @param  string $pageId
   * @param  string $websiteId
   * @param  string $pageJsonFilePath
   *
   * @return array
   */
  private function updateOrCreatePageFromPageJson(
      $pageId,
      $websiteId,
      $pageJsonFilePath
  ) {
    $pageJsonString = file_get_contents($pageJsonFilePath);
    $this->cleaningColorIds($websiteId, $pageJsonString);
    $pageJson = json_decode($pageJsonString);
    $pageJsonArrayObject = new \ArrayObject($pageJson);
    $pageJsonValues = $pageJsonArrayObject->getArrayCopy();

    $importElement = array(
      'id' => $pageId,
      'name' => $pageJsonValues['name']
    );

    $columnsValues = array(
      'id' => $pageJsonValues['id'],
      'templateid' => $pageJsonValues['templateId'],
      'name' => $pageJsonValues['name'],
      'description' => $pageJsonValues['description'],
      'innavigation' => $pageJsonValues['inNavigation'],
      'date' => $pageJsonValues['date'],
      'navigationtitle' => $pageJsonValues['navigationTitle'],
      'content' => $pageJsonValues['content'],
      'templatecontent' => isset($pageJsonValues['templateContent']) ? $pageJsonValues['templateContent'] : null,
      'pageAttributes' => isset($pageJsonValues['pageAttributes']) ? $pageJsonValues['pageAttributes'] : null,
    );

    if (isset($pageJsonValues['mediaId'])) {
      $columnsValues['mediaid'] = $pageJsonValues['mediaId'];
    }

    if (array_key_exists('pageType', $pageJsonValues) && !empty($pageJsonValues['pageType'])) {
      $columnsValues['pageType'] = $pageJsonValues['pageType'];
    } else {
      $columnsValues['pageType'] = $this->getDefaultPageTypeId();
    }

    $pageDao = DaoFactory::get('Page');

    if ($pageDao->existsPage($pageId, $websiteId)) {
      $page = $pageDao->update($pageId, $websiteId, $columnsValues);
    } else {
      $page = $pageDao->create($websiteId, $columnsValues, true);
    }

    return $importElement;
  }

  /**
   * @param  string $mediaId
   * @param  string $websiteId
   * @param  string $mediaJsonFilePath
   * @param  array  $previousMediaFile
   * @param  array  $importedAlbums
   *
   * @return array
   */
  private function updateOrCreateMediaFromMediaJson(
      $mediaId,
      $websiteId,
      $mediaJsonFilePath,
      array $previousMediaFile,
      &$importedAlbums
  ) {
    $mediaJson = json_decode(file_get_contents($mediaJsonFilePath));
    $mediaJsonArrayObject = new \ArrayObject($mediaJson);
    $mediaJsonValues = $mediaJsonArrayObject->getArrayCopy();

    $importElement = array(
      'id' => $mediaId,
      'name' => $mediaJsonValues['name']
    );

    $insertMediaIntoDefaultAlbum = true;
    if (isset($mediaJsonValues['albumId'])) {
      $albumDao = $this->getAlbumDao();
      if ($albumDao->existsAlbum($mediaJsonValues['albumId'], $websiteId)) {
        $insertMediaIntoDefaultAlbum = false;
      }
    }

    if ($insertMediaIntoDefaultAlbum) {
      $defaultAlbum = $this->createDefaultAlbum($websiteId, $importedAlbums);
      $mediaJsonValues['albumId'] = $defaultAlbum->getId();
    }

    $columnsValues = array(
      'id' => $mediaJsonValues['id'],
      'albumid' => $mediaJsonValues['albumId'],
      'name' => $mediaJsonValues['name'],
      'filename' => isset($mediaJsonValues['filename']) ? $mediaJsonValues['filename'] : null,
      'extension' => $mediaJsonValues['extension'],
      'size' => isset($mediaJsonValues['size']) ? $mediaJsonValues['size'] : null,
      'file' => isset($mediaJsonValues['file']) ? $mediaJsonValues['file'] : null,
      'type' => MediaType::getByExtension($mediaJsonValues['extension']),
      'mimetype' => isset($mediaJsonValues['mimetype']) ? $mediaJsonValues['mimetype'] : null,
    );

    $mediaService = $this->getMediaService();
    if ($mediaService->existsMedia($websiteId, $mediaId)) {
      $media = $mediaService->getById($mediaId, $websiteId);
      $this->deleteMediaFile($websiteId, $media->getFile());
      if (isset($previousMediaFile['source']) && isset($previousMediaFile['destination'])) {
        copy($previousMediaFile['source'], $previousMediaFile['destination']);
      }
      $mediaService->edit($mediaId, $websiteId, $columnsValues);
    } else {
      if (isset($previousMediaFile['source']) && isset($previousMediaFile['destination'])) {
        copy($previousMediaFile['source'], $previousMediaFile['destination']);
      }
      $media = $mediaService->create($websiteId, $columnsValues, true);
    }

    return $importElement;
  }

  /**
   * @param  string $websiteId
   * @param  string $albumId
   *
   * @return boolean
   */
  protected function getAlbumDao()
  {
    if (!isset($this->albumDao)) {
      $this->albumDao = DaoFactory::get('Album');
    }
    return $this->albumDao;
  }

  /**
   * @param  string $websiteId
   * @param  array  $importedAlbums
   *
   * @return \Orm\Entity\Album  default album
   */
  protected function createDefaultAlbum($websiteId, &$importedAlbums)
  {
    // already created default Album
    if ($this->defaultAlbum !== null
      && $this->defaultAlbum instanceof \Orm\Entity\Album
      && $this->defaultAlbum->getId() !== null
    ) {
      return $this->defaultAlbum;
    }

    $albumDao = $this->getAlbumDao();

    $foundAlbum = $albumDao->getByWebsiteIdAndName($websiteId, $this->defaultAlbumName);
    if (is_array($foundAlbum) && count($foundAlbum) > 0) {
      $this->defaultAlbum = array_shift($foundAlbum);
    } else {
      $columnValues = array('name' => $this->defaultAlbumName);
      $album = $albumDao->create($websiteId, $columnValues);
      $this->defaultAlbum = $album;

      $importedAlbums[$album->getId()] = array(
        'id' => $album->getId(),
        'name' => $album->getName()
      );
    }

    return $this->defaultAlbum;
  }


  /**
   * @param string $websiteId
   * @param string $filename
   */
  private function deleteMediaFile($websiteId, $filename)
  {
    $mediaFileToDelete = $this->mediaDirectory
      . DIRECTORY_SEPARATOR . $websiteId
      . DIRECTORY_SEPARATOR . $filename;
    if (file_exists($mediaFileToDelete)
      && is_file($mediaFileToDelete)
      && strstr($mediaFileToDelete, $this->mediaDirectory)
    ) {
      return unlink($mediaFileToDelete);
    }
    return false;
  }

  /**
   * @param  string $websiteId
   *
   * @return array
   */
  private function importUsergroups($websiteId)
  {
    $importedGroups = array();

    $usergroupJsonFile = $this->currentImportUnzipDirectory
      . DIRECTORY_SEPARATOR . 'usergroup.json';

    if (!file_exists($usergroupJsonFile)) {
      return $importedGroups;
    }

    $usergroupJsonContent = file_get_contents($usergroupJsonFile);
    $usergroupJsonContentAsArray = json_decode($usergroupJsonContent, true);

    if (is_array($usergroupJsonContentAsArray)
      && count($usergroupJsonContentAsArray) > 0
    ) {
      $groupDao = DaoFactory::get('Group');

      if (!$groupDao->existsGroupsForWebsite($websiteId)) {
        foreach ($usergroupJsonContentAsArray as $usergroup) {
          $columnValues = array(
            'id' => $usergroup['id'],
            'name' => $usergroup['name'],
            'rights' => $usergroup['rights']
          );
          $groupDao->create($websiteId, $columnValues, true);
          array_pop($columnValues);
          $importedGroups[] = $columnValues;
        }

      } else {
        foreach ($usergroupJsonContentAsArray as $usergroup) {
          $columnValues = array(
            'id' => $usergroup['id'],
            'name' => $usergroup['name'],
            'rights' => $usergroup['rights']
          );
          if ($groupDao->existsGroup($usergroup['id'], $websiteId)) {
            array_shift($columnValues);
            $columnValues['rights'] = json_decode($columnValues['rights'], true);
            $groupDao->update($usergroup['id'], $websiteId, $columnValues);
          } else {
            $groupDao->create($websiteId, $columnValues, true);
            array_pop($columnValues);
          }
          $importedGroups[] = $columnValues;
        }

      }
    }

    return $importedGroups;
  }

  /**
   * @param  string $websiteId
   *
   * @return array
   */
  private function importAlbums($websiteId)
  {
    $albumJsonFile = $this->currentImportUnzipDirectory
      . DIRECTORY_SEPARATOR . 'media'
      . DIRECTORY_SEPARATOR . 'album.json';
    $importMediaBaseDirectory = $this->currentImportUnzipDirectory
      . DIRECTORY_SEPARATOR . 'media';

    $importedAlbums = array();

    if (file_exists($albumJsonFile) && is_dir($importMediaBaseDirectory)) {
      $albumDao = $this->getAlbumDao();

      $albumJsonContent = file_get_contents($albumJsonFile);
      $albumJsonContentAsArray = json_decode($albumJsonContent, true);
      $albums = $albumJsonContentAsArray['albums'];

      if (count($albums) > 0) {
        foreach ($albums as $album) {
          $columnValues = array(
            'name' => $album['name']
          );
          if (!$albumDao->existsAlbum($album['id'], $websiteId)) {
            $columnValues['id'] = $album['id'];
            $touchedAlbum = $albumDao->create($websiteId, $columnValues, true);
          } else {
            $touchedAlbum = $albumDao->update($album['id'], $websiteId, $columnValues);
          }
          $importedAlbums[$touchedAlbum->getId()] = array(
            'id' => $touchedAlbum->getId(),
            'name' => $touchedAlbum->getName()
          );
        }
      }
    }

    return $importedAlbums;
  }

  /**
   * @param  sting   $websiteId
   * @param  boolean $checkConflict  Check conflict
   * @param  array   $overwriteIds   MediaIds to overwrite
   * @param  array   $importedAlbums imported album
   *
   * @return array
   */
  private function importMedia(
      $websiteId,
      $checkConflict = false,
      array $overwriteIds = array(),
      &$importedAlbums
  ) {
    $mediaUnzipDirectory = realpath($this->currentImportUnzipDirectory
      . DIRECTORY_SEPARATOR . 'media');
    $mediaBaseDirectory = $this->mediaDirectory
      . DIRECTORY_SEPARATOR . $websiteId;

    $importedMedias = array();
    $conflictingMediaIds = array();

    if ($checkConflict && isset($this->currentConflictItems['media'])) {
      $conflictingMediaIds = array_keys($this->currentConflictItems['media']);
    }

    if (!is_dir($mediaBaseDirectory)) {
      mkdir($mediaBaseDirectory);
    }

    // Alle Module durchlaufen
    foreach ($this->currentImportMediaInfo as $currentMediaId => $mediaInfo) {
      // check if import of the current media is allowed
      $importCurrentMedia = $this->itemImportAllowed(
          $currentMediaId,
          $conflictingMediaIds,
          $overwriteIds
      );
      if ($importCurrentMedia) {
        $importMediaPathName = $mediaUnzipDirectory . DIRECTORY_SEPARATOR . $currentMediaId;
        $importMediaInfoFile = $importMediaPathName . DIRECTORY_SEPARATOR . 'media.json';

        if (file_exists($importMediaInfoFile)) {
          $previousMediaFile = array();
          if (isset($mediaInfo['file'])) {
            $sourceMediaFile = realpath($importMediaPathName . DIRECTORY_SEPARATOR . $mediaInfo['file']);
            if (strpos($sourceMediaFile, $mediaUnzipDirectory) === 0 && file_exists($sourceMediaFile)) {
              $previousMediaFile['source'] = $sourceMediaFile;
              $previousMediaFile['destination'] = $mediaBaseDirectory . DIRECTORY_SEPARATOR . $mediaInfo['file'];
            }
          }

          $importedMedias[] = $this->updateOrCreateMediaFromMediaJson(
              $currentMediaId,
              $websiteId,
              $importMediaInfoFile,
              $previousMediaFile,
              $importedAlbums
          );
        }
      }
    }

    return $importedMedias;
  }

  /**
   * @param  string  $websiteId
   * @param  boolean $checkConflict Check conflict
   * @param  array   $overwriteIds  TemplateIds to overwrite
   *
   * @return array
   */
  private function importTemplateSnippets($websiteId, $checkConflict = false, array $overwriteIds = array())
  {
    $templateSnippetsUnzipDirectory = $this->currentImportUnzipDirectory
      . DIRECTORY_SEPARATOR . 'templatesnippets';

    $importedTemplateSnippets = array();
    $conflictingTemplateSnippetIds = array();

    if ($checkConflict && isset($this->currentConflictItems['templatesnippets'])) {
      $conflictingTemplateSnippetIds = array_keys($this->currentConflictItems['templatesnippets']);
    }

    // Alle TemplateSnippets durchlaufen
    foreach ($this->currentImportTemplateSnippetInfo as $currentTemplateSnippetId => $templateSnippetInfo) {
      $importTemplateSnippetInfoFile = $templateSnippetInfo['file'];

      if (file_exists($importTemplateSnippetInfoFile)) {
        // check if import of the current template snippet is allowed
        $importCurrentTemplateSnippet = $this->itemImportAllowed(
            $currentTemplateSnippetId,
            $conflictingTemplateSnippetIds,
            $overwriteIds
        );
        if ($importCurrentTemplateSnippet) {
          $importedTemplateSnippets[] = $this->updateOrCreateTemplateSnippetFromTemplateSnippetJson(
              $currentTemplateSnippetId,
              $websiteId,
              $importTemplateSnippetInfoFile
          );
        }
      }
    }

    return $importedTemplateSnippets;
  }

  /**
   * @param  string  $websiteId     Website to import into
   * @param  boolean $checkConflict Check conflict
   * @param  array   $overwriteIds  ModuleIds to overwrite
   *
   * @return array
   */
  private function importModule($websiteId, $checkConflict = false, array $overwriteIds = array())
  {
    $modulesUnzipDirectory = $this->currentImportUnzipDirectory
      . DIRECTORY_SEPARATOR . 'modules';

    $importedModules = array();
    $conflictingModuleIds = array();

    if ($checkConflict && isset($this->currentConflictItems['modules'])) {
      $conflictingModuleIds = array_keys($this->currentConflictItems['modules']);
    }

    $moduleService = $this->getModuleService();

    // Iterate over all Module
    foreach ($this->currentImportModuleInfo as $currentModuleId => $moduleInfo) {
      $importModulePathName = FS::joinPath($modulesUnzipDirectory, $currentModuleId);
      $importModuleAssetsPath = FS::joinPath($importModulePathName, ExportBusiness::MODULE_SUBDIR_ASSETS);
      $importModuleDataDirectoryPath = FS::joinPath($importModulePathName, ExportBusiness::MODULE_SUBDIR_DATA);

      // check if import of the current module is allowed
      $importCurrentModule = $this->itemImportAllowed(
          $currentModuleId,
          $conflictingModuleIds,
          $overwriteIds
      );
      if (!$importCurrentModule) {
        continue;
      }
      // ### Module till v1.4.0 [use module.json for versions < 1.5.0]
      if (version_compare('1.5.0', $this->getImportVersion()) > 0) {
        $importModuleInfoFile = FS::joinPath($importModulePathName, 'modul.json');
        if (!is_readable($importModuleInfoFile)) {
          continue;
        }
        $createdModule = $this->createOrOverwriteModuleFromModuleJson(
            $moduleService,
            $currentModuleId,
            $websiteId,
            $importModuleInfoFile
        );
        $this->replaceModuleAssetFiles($moduleService, $createdModule, $importModuleAssetsPath);
        $this->replaceTranslationFile($moduleService, $createdModule, $importModuleDataDirectoryPath);
      } else {
        // ### Module v1.5.0
        if (!is_dir($importModuleDataDirectoryPath)) {
          continue;
        }

        $newModule = new DataModule();
        $newModule->setId($currentModuleId);
        $createdModule = $this->createOrOverwriteModule($moduleService, $websiteId, $newModule);
        $this->replaceModuleDataFiles($moduleService, $createdModule, $importModuleDataDirectoryPath);
        $this->replaceModuleAssetFiles($moduleService, $createdModule, $importModuleAssetsPath);
      }
      // we have copy module files -> reset the module cache to ensure that we have the new module attributes
      $moduleService->resetCache();

      $importedModules[] = array(
        'id' => $createdModule->getId(),
        'name' => $createdModule->getName(),
      );
    }

    return $importedModules;
  }

  /**
   * @param \Cms\Service\Modul $moduleService
   * @param string             $websiteId
   * @param DataModule         $module
   *
   * @return DataModule
   */
  protected function createOrOverwriteModule($moduleService, $websiteId, DataModule $module)
  {
    if ($moduleService->existsModulAlready($module->getId(), $websiteId, true)) {
      $moduleService->delete($module->getId(), $websiteId);
    }
    return $moduleService->create($websiteId, $module);
  }

  /**
   * @param \Cms\Service\Modul $moduleService
   * @param DataModule         $module
   * @param string             $importModuleDataDirectoryPath
   */
  protected function replaceModuleDataFiles(
      $moduleService,
      DataModule $module,
      $importModuleDataDirectoryPath
  ) {
    $dataPath = $moduleService->getDataPath($module->getWebsiteid(), $module->getId());
    FS::rmdir($dataPath);
    FS::createDirIfNotExists($dataPath, true);
    if (is_dir($importModuleDataDirectoryPath)) {
      FS::copyDir($importModuleDataDirectoryPath, $dataPath);
    }
  }

  /**
   * @param \Cms\Service\Modul $moduleService
   * @param DataModule         $module
   * @param string             $importModuleAssetsPath
   */
  protected function replaceModuleAssetFiles(
      $moduleService,
      DataModule $module,
      $importModuleAssetsPath
  ) {
    $assetsPath = $moduleService->getAssetsPath($module->getWebsiteid(), $module->getId());
    FS::rmdir($assetsPath);
    FS::createDirIfNotExists($assetsPath, true);
    if (is_dir($importModuleAssetsPath)) {
      FS::copyDir($importModuleAssetsPath, $assetsPath);
    }
  }

  /**
   * @param \Cms\Service\Modul $moduleService
   * @param DataModule         $module
   * @param string             $importModuleDataDirectoryPath
   */
  protected function replaceTranslationFile(
      $moduleService,
      DataModule $module,
      $importModuleDataDirectoryPath
  ) {
    $importTranslationFile = FS::joinPath($importModuleDataDirectoryPath, 'moduleTranslation.php');
    if (is_readable($importTranslationFile)) {
      $dataPath = $moduleService->getDataPath($module->getWebsiteid(), $module->getId());
      $moduleTranslationFile = FS::joinPath($dataPath, 'moduleTranslation.php');
      FS::copyFile($importTranslationFile, $moduleTranslationFile);
    }
  }

  /**
   * @param  string  $websiteId
   * @param  boolean $checkConflict Check conflict
   * @param  array   $overwriteIds  TemplateIds to overwrite
   *
   * @return array
   */
  private function importTemplates($websiteId, $checkConflict = false, array $overwriteIds = array())
  {
    $templatesUnzipDirectory = $this->currentImportUnzipDirectory
      . DIRECTORY_SEPARATOR . 'templates';

    $importedTemplates = array();
    $conflictingTemplateIds = array();

    if ($checkConflict && isset($this->currentConflictItems['templates'])) {
      $conflictingTemplateIds = array_keys($this->currentConflictItems['templates']);
    }

    // Alle Templates durchlaufen
    foreach ($this->currentImportTemplateInfo as $currentTemplateId => $templateInfo) {
      $importTemplatePathName = $templatesUnzipDirectory . DIRECTORY_SEPARATOR . $currentTemplateId;
      $importTemplateInfoFile = $importTemplatePathName . DIRECTORY_SEPARATOR . 'template.json';

      if (file_exists($importTemplateInfoFile)) {
        // check if import of the current template is allowed
        $importCurrentTemplate = $this->itemImportAllowed(
            $currentTemplateId,
            $conflictingTemplateIds,
            $overwriteIds
        );
        if ($importCurrentTemplate) {
          $importedTemplates[] = $this->updateOrCreateTemplateFromTemplateJson(
              $currentTemplateId,
              $websiteId,
              $importTemplateInfoFile
          );
        }
      }
    }

    return $importedTemplates;
  }

  /**
   * @param  string $websiteId
   *
   * @return array
   */
  private function importPages($websiteId)
  {
    $pagesUnzipDirectory = $this->currentImportUnzipDirectory
      . DIRECTORY_SEPARATOR . 'pages';

    $importedPages = array();

    // Alle Pages durchlaufen
    foreach ($this->currentImportPageInfo as $currentPageId => $pageInfo) {
      $importPagePathName = $pagesUnzipDirectory . DIRECTORY_SEPARATOR . $currentPageId;
      $importPageInfoFile = $importPagePathName . DIRECTORY_SEPARATOR . 'page.json';

      if (file_exists($importPageInfoFile)) {
        $importedPages[] = $this->updateOrCreatePageFromPageJson(
            $currentPageId,
            $websiteId,
            $importPageInfoFile
        );
      }
    }

    return $importedPages;
  }

  /**
   * @param string $websiteId
   * @param string $unzipDirectory
   *
   * @return array
   */
  protected function importWebsiteSettingsFromJsonFile($websiteId, $unzipDirectory)
  {
    $importedItems = array();

    $websiteSettingsJsonFile = FS::joinPath($unzipDirectory, 'website', 'websitesettings.json');
    if (!is_readable($websiteSettingsJsonFile)) {
      return $importedItems;
    }

    $websiteSettingsData = json_decode(file_get_contents($websiteSettingsJsonFile));
    if (!is_object($websiteSettingsData)) {
      return $importedItems;
    }

    $websiteSettingsAsArray = get_object_vars($websiteSettingsData);
    foreach ($websiteSettingsAsArray as $settingsId => $attributes) {
      $websiteSettings = $this->importWebsiteSettings($websiteId, $settingsId, $attributes);
      if (!$websiteSettings) {
        continue;
      }
      $importedItems[$settingsId] = array(
        'id' => $settingsId,
        'name' => $websiteSettings->getName(),
      );
    }

    return $importedItems;
  }

  /**
   * @param string $websiteId
   * @param string $id
   * @param mixed  $attributes
   *
   * @return \Cms\Data\WebsiteSettings|null
   */
  protected function importWebsiteSettings($websiteId, $id, $attributes)
  {
    if (is_object($attributes)) {
      $attributes = get_object_vars($attributes);
    }
    if (!is_array($attributes)) {
      return null;
    }

    $service = $this->getWebsiteSettingsService();
    if (!$service->exists($websiteId, $id)) {
      Registry::getLogger()->log(
          __CLASS__,
          __METHOD__,
          'WebsiteSettings with id "'.$id.'" not exists',
          SbLog::WARN
      );
      return null;
    }
    return $service->update($websiteId, $id, $attributes);
  }

  /**
   * @param string $websiteId
   * @param bool   $checkConflict
   * @param array  $overwriteIds
   *
   * @return array
   */
  protected function importPackages($websiteId, $checkConflict = false, array $overwriteIds = array())
  {
    $importedPackages = array();
    $conflictingPackageIds = array();
    $packageService = $this->getPackageService();

    if ($checkConflict && isset($this->currentConflictItems['packages'])) {
      $conflictingPackageIds = array_keys($this->currentConflictItems['packages']);
    }

    foreach ($this->currentImportPackageInfo as $packageId => $packageInfo) {
      // check if import of the package is allowed
      if (!$this->itemImportAllowed($packageId, $conflictingPackageIds, $overwriteIds)) {
        continue;
      }
      $localDirectory = $packageService->getDirectoryFormImportingLocalPackages($websiteId);
      $localPackageDirectory = FS::joinPath($localDirectory, $packageId);
      if (file_exists($localPackageDirectory)) {
        FS::rmdir($localPackageDirectory);
      }
      FS::createDirIfNotExists($localPackageDirectory, true);
      FS::copyDir($packageInfo['directory'], $localPackageDirectory);

      $importedPackages[] = array(
        'id' => $packageId,
        'name' => $packageInfo['name'],
      );
    }

    // ensure that the imported packages will be (re)loaded
    $packageService->resetCache();

    return $importedPackages;
  }

  /**
   * @throws \Cms\Exception
   */
  private function validateJsonExportFileExistence()
  {
    $exportJsonFile = FS::joinPath(
        $this->currentImportUnzipDirectory,
        'export.json'
    );
    if (!is_readable($exportJsonFile)) {
      throw new CmsException(10, __METHOD__, __LINE__, array(
        'detail' => 'export.json doesn’t exist'));
    }
  }

  /**
   * @return array
   */
  private function getExportJson()
  {
    $exportJsonFile = $this->currentImportUnzipDirectory
      . DIRECTORY_SEPARATOR . 'export.json';

    if (!file_exists($exportJsonFile)) {
      return array();
    }

    $exportJson = json_decode(file_get_contents($exportJsonFile));
    $exportJsonArrayObject = new \ArrayObject($exportJson);
    return $exportJsonArrayObject->getArrayCopy();
  }

  /**
   * @throws \Cms\Exception
   */
  protected function validateJsonExportMode()
  {
    $exportMode = $this->getImportMode();
    if (!in_array($exportMode, $this->availableImportModes)) {
      throw new CmsException(25, __METHOD__, __LINE__, array(
        'type' => $exportMode));
    }
  }

  /**
   * @throws \Cms\Exception
   */
  protected function validateJsonExportFileVersion()
  {
    $importFileVersion = $this->getImportVersion();

    // no newer import newer version
    if (version_compare(Version::EXPORT, $importFileVersion) < 0) {
      throw new CmsException(
          '17',
          __METHOD__,
          __LINE__,
          array('importFileVersion' => $importFileVersion)
      );
    }

    // no older import
    if (version_compare($importFileVersion, '1.2.0') < 0) {
      throw new CmsException(
          '17',
          __METHOD__,
          __LINE__,
          array('importFileVersion' => $importFileVersion)
      );
    }
  }

  /**
   * @param null|string $allowedImportMode
   *
   * @throws \Cms\Exception
   */
  protected function validateImportMode($allowedImportMode)
  {
    $importMode = $this->getImportMode();

    if (!empty($allowedImportMode) && $importMode !== $allowedImportMode) {
      switch ($importMode) {
        case ExportBusiness::EXPORT_MODE_WEBSITE:
              throw new CmsException('31', __METHOD__, __LINE__);
          break;

        case ExportBusiness::EXPORT_MODE_MODULE:
              throw new CmsException('34', __METHOD__, __LINE__);
          break;

        case ExportBusiness::EXPORT_MODE_TEMPLATESNIPPET:
              throw new CmsException('35', __METHOD__, __LINE__);
          break;

        default:
              throw new CmsException('30', __METHOD__, __LINE__);
          break;
      }
    }
  }

  /**
   * @param string $id
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  private function validateMediaId($id)
  {
    $mediaIdValidator = new UniqueIdValidator(
        DataMedia::ID_PREFIX,
        DataMedia::ID_SUFFIX
    );

    $mediaIdValidator->setMessage(
        "Angegebene Media Id ist ungueltig",
        UniqueIdValidator::INVALID
    );

    if (!$mediaIdValidator->isValid($id)) {
      $messages = $mediaIdValidator->getMessages();
      throw new CmsException(10, __METHOD__, __LINE__, array(
        'detail' => reset($messages)));
      return false;
    }
    return true;
  }

  /**
   * @throws \Exception
   * @return boolean
   */
  private function unzipImport()
  {
    $this->removeImportFiles($this->currentImportFile, true);

    $zip = new \ZipArchive();
    $zipHandle = $zip->open($this->currentImportFile);

    if ($zipHandle === true) {
      $zip->extractTo($this->currentImportUnzipDirectory);
      $zip->close();
      return true;
    } else {
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
      throw new CmsException(
          10,
          __METHOD__,
          __LINE__,
          array('detail' => $errorMessage)
      );
    }
    return false;
  }

  /**
   * @param string  $importFile
   * @param boolean $onlyUnzipDirectory remove only the unzip directory
   */
  public function removeImportFiles($importFile, $onlyUnzipDirectory = false)
  {
    if (empty($importFile)) {
      return;
    }
    $importZipFile = $importFile;
    $importUnzipDirectory = dirname($importZipFile)
      . DIRECTORY_SEPARATOR . basename($importZipFile, '.zip');

    if ($onlyUnzipDirectory == false) {
      if (file_exists($importZipFile)) {
        unlink($importZipFile);
      }
    }

    if (is_dir($importUnzipDirectory)) {
      $this->removeUnzipDirectory($importUnzipDirectory);
    }
  }

  /**
   * @param  string $directory
   *
   * @return boolean
   */
  private function removeUnzipDirectory($directory)
  {
    $baseImportDirectory = realpath($this->importDirectory);
    $directory = realpath($directory);
    if (!empty($directory) && !empty($baseImportDirectory)
      && is_dir($directory) && strpos($directory, $baseImportDirectory) === 0
    ) {
      $iterator = new \RecursiveIteratorIterator(
          new \RecursiveDirectoryIterator($directory),
          \RecursiveIteratorIterator::CHILD_FIRST
      );

      foreach ($iterator as $path) {
        if (!$iterator->isDot()) {
          if ($path->isDir()) {
            rmdir($path->getPathname());
          } else {
            unlink($path->getPathname());
          }
        }
      }

      rmdir($directory);

      return true;
    }

    return false;
  }

  /**
   * prepare import
   *
   * @param string $websiteId
   * @param string $allowedImportMode
   *
   * @throws \Exception
   */
  protected function prepareImport($websiteId, $allowedImportMode)
  {
    try {
      $this->validateImport($allowedImportMode);
      $importMode = $this->getImportMode();
      $this->collectImportInfo();
      $this->collectConflicts($websiteId, $importMode);
      $this->checkQuota($websiteId, $importMode);
    } catch (\Exception $e) {
      // cleanup
      $this->removeImportFiles($this->currentImportFile);
      throw $e;
    }
  }

  /**
   * @param null|string $allowedImportMode
   */
  protected function validateImport($allowedImportMode)
  {
    $this->validateJsonExportFileExistence();
    $this->validateJsonExportFileVersion();
    $this->validateJsonExportMode();
    $this->validateImportMode($allowedImportMode);
  }

  /**
   * collect information about the import
   */
  protected function collectImportInfo()
  {
    $this->collectMediaInfo();
    $this->collectTempateSnippetInfo();
    $this->collectModuleInfo();
    $this->collectTemplateInfo();
    $this->collectPageInfo();
    $this->collectPackageInfo();
  }

  /**
   * collect the media info inside the unziped import
   */
  protected function collectMediaInfo()
  {
    $this->currentImportMediaInfo = array();

    $mediaSubPath = DIRECTORY_SEPARATOR . 'media';
    $mediaUnzipDirectory = $this->currentImportUnzipDirectory . $mediaSubPath;

    if (is_dir($mediaUnzipDirectory)) {
      $iterator = new \DirectoryIterator($mediaUnzipDirectory);
      while ($iterator->valid()) {
        if (!$iterator->isDot() && $this->isCurrentIteratorAMediaDirectory($iterator)) {
          $currentMediaId = $iterator->getFilename();
          $importMediaPathName = $iterator->getPathname() . DIRECTORY_SEPARATOR;
          $importMediaInfoFile = $importMediaPathName . 'media.json';

          if (file_exists($importMediaInfoFile)) {
            $mediaJson = json_decode(file_get_contents($importMediaInfoFile));
            $mediaJsonArrayObject = new \ArrayObject($mediaJson);
            $mediaJsonValues = $mediaJsonArrayObject->getArrayCopy();

            $this->currentImportMediaInfo[$currentMediaId] = array(
              'id' => $currentMediaId,
              'name' => $mediaJsonValues['name'],
              'file' => isset($mediaJsonValues['file']) ? $mediaJsonValues['file'] : null,
            );
          }
        }
        $iterator->next();
      }
    }
  }

  /**
   * collect the template snippet info inside the unziped import
   */
  protected function collectTempateSnippetInfo()
  {
    $this->currentImportTemplateSnippetInfo = array();

    if (version_compare('1.6.0', $this->getImportVersion()) > 0) {
      // version < v1.6.0:
      $infoFileName = 'templatesnippet.json';
    } else {
      // version >= v1.6.0
      $infoFileName = 'templateSnippet.json';
    }

    $templateSnippetSubPath = DIRECTORY_SEPARATOR . 'templatesnippets';
    $templateSnippetUnzipDirectory = $this->currentImportUnzipDirectory . $templateSnippetSubPath;

    if (is_dir($templateSnippetUnzipDirectory)) {
      $iterator = new \DirectoryIterator($templateSnippetUnzipDirectory);
      while ($iterator->valid()) {
        if (!$iterator->isDot() && $this->isCurrentIteratorATemplateSnippetDirectory($iterator)) {
          $currentTemplateSnippetId = $iterator->getFilename();
          $importTemplateSnippetPathName = $iterator->getPathname() . DIRECTORY_SEPARATOR;
          $importTemplateSnippetInfoFile = $importTemplateSnippetPathName . $infoFileName;

          if (file_exists($importTemplateSnippetInfoFile)) {
            $templateSnippetJson = json_decode(file_get_contents($importTemplateSnippetInfoFile));
            $templateSnippetJsonArrayObject = new \ArrayObject($templateSnippetJson);
            $templateSnippetJsonValues = $templateSnippetJsonArrayObject->getArrayCopy();

            $this->currentImportTemplateSnippetInfo[$currentTemplateSnippetId] = array(
              'id' => $currentTemplateSnippetId,
              'name' => $templateSnippetJsonValues['name'],
              'file' => $importTemplateSnippetInfoFile,
            );
          }
        }
        $iterator->next();
      }
    }
  }

  /**
   * collect the module item info inside the unziped import
   */
  protected function collectModuleInfo()
  {
    $this->currentImportModuleInfo = array();

    $moduleSubPath = DIRECTORY_SEPARATOR . 'modules';
    $modulesUnzipDirectory = $this->currentImportUnzipDirectory . $moduleSubPath;

    if (is_dir($modulesUnzipDirectory)) {
      $iterator = new \DirectoryIterator($modulesUnzipDirectory);
      while ($iterator->valid()) {
        if (!$iterator->isDot() && $this->isCurrentIteratorAModulDirectory($iterator)) {
          $currentModuleId = $iterator->getFilename();
          $importModulePathName = $iterator->getPathname();

          // v1.4.0: old format
          if (version_compare('1.5.0', $this->getImportVersion()) > 0) {
            // read name form module.json
            $importModuleInfoFile = FS::joinPath($importModulePathName, 'modul.json');
            if (file_exists($importModuleInfoFile)) {
              $moduleJson = json_decode(file_get_contents($importModuleInfoFile));
              $moduleJsonArrayObject = new \ArrayObject($moduleJson);
              $moduleJsonValues = $moduleJsonArrayObject->getArrayCopy();

              $this->currentImportModuleInfo[$currentModuleId] = array(
                'id' => $currentModuleId,
                'name' => $moduleJsonValues['name'],
              );
            }
          } // v1.5.0: new export format
          else {
            // read name from manifest.json or moduleManifest.json (depending on Module Version, NOT Export Version)
            $manifestFilePath = FS::joinPath($importModulePathName, ExportBusiness::MODULE_SUBDIR_DATA, ExportBusiness::MODULE_FILE_MANIFEST);

            // use legacy file path if manifest.json is not a file
            if (!file_exists($manifestFilePath)) {
              $manifestFilePath = FS::joinPath($importModulePathName, ExportBusiness::MODULE_SUBDIR_DATA, ExportBusiness::MODULE_FILE_LEGACY_MANIFEST);
            }

            // get name and add module to $this->currentImportModuleInfo
            if (file_exists($manifestFilePath)) {
              $manifest = json_decode(file_get_contents($manifestFilePath), true);
              if ($manifest) {
                $this->currentImportModuleInfo[$currentModuleId] = array(
                  'id' => $currentModuleId,
                  'name' => $manifest['name'],
                );
              }
            }
          }
        }
        $iterator->next();
      }
    }
  }

  /**
   * collect the template item info inside the unziped import
   */
  protected function collectTemplateInfo()
  {
    $this->currentImportTemplateInfo = array();

    $templateSubPath = DIRECTORY_SEPARATOR . 'templates';
    $templatesUnzipDirectory = $this->currentImportUnzipDirectory . $templateSubPath;

    if (is_dir($templatesUnzipDirectory)) {
      $iterator = new \DirectoryIterator($templatesUnzipDirectory);
      while ($iterator->valid()) {
        if (!$iterator->isDot() && $this->isCurrentIteratorATemplateDirectory($iterator)) {
          $currentTemplateId = $iterator->getFilename();
          $importTemplatePathName = $iterator->getPathname() . DIRECTORY_SEPARATOR;
          $importTemplateInfoFile = $importTemplatePathName . 'template.json';

          if (file_exists($importTemplateInfoFile)) {
            $templateJson = json_decode(file_get_contents($importTemplateInfoFile));
            $templateJsonArrayObject = new \ArrayObject($templateJson);
            $templateJsonValues = $templateJsonArrayObject->getArrayCopy();

            $this->currentImportTemplateInfo[$currentTemplateId] = array(
              'id' => $currentTemplateId,
              'name' => $templateJsonValues['name'],
            );
          }
        }
        $iterator->next();
      }
    }
  }

  /**
   * collect the page item info inside the unziped import
   */
  protected function collectPageInfo()
  {
    $this->currentImportPageInfo = array();

    $pageSubPath = DIRECTORY_SEPARATOR . 'pages';
    $templatesUnzipDirectory = $this->currentImportUnzipDirectory . $pageSubPath;

    if (is_dir($templatesUnzipDirectory)) {
      $iterator = new \DirectoryIterator($templatesUnzipDirectory);
      while ($iterator->valid()) {
        if (!$iterator->isDot() && $this->isCurrentIteratorAPageDirectory($iterator)) {
          $currentPageId = $iterator->getFilename();
          $importPagePathName = $iterator->getPathname() . DIRECTORY_SEPARATOR;
          $importPageInfoFile = $importPagePathName . 'page.json';

          if (file_exists($importPageInfoFile)) {
            $pageJson = json_decode(file_get_contents($importPageInfoFile));
            $pageJsonArrayObject = new \ArrayObject($pageJson);
            $pageJsonValues = $pageJsonArrayObject->getArrayCopy();

            $this->currentImportPageInfo[$currentPageId] = array(
              'id' => $currentPageId,
              'name' => $pageJsonValues['name'],
            );
          }
        }
        $iterator->next();
      }
    }
  }

  /**
   * collect the package info inside the unzipped import
   */
  protected function collectPackageInfo()
  {
    $this->currentImportPackageInfo = array();

    $packagesUnzipDirectory = FS::joinPath($this->currentImportUnzipDirectory, 'packages');
    if (!is_dir($packagesUnzipDirectory)) {
      return;
    }

    $iterator = new \DirectoryIterator($packagesUnzipDirectory);
    while ($iterator->valid()) {
      if (!$iterator->isDot() && $this->isCurrentIteratorAPackageDirectory($iterator)) {
        $packageId = $iterator->getFilename();
        $packageDirectory = $iterator->getPathname();

        $importPackageManifestFile = FS::joinPath($packageDirectory, 'pkg.json');
        if (!file_exists($importPackageManifestFile)) {
          continue;
        }
        $packageInfo = json_decode(FS::readContentFromFile($importPackageManifestFile), true);
        if (!is_array($packageInfo)) {
          continue;
        }

        $this->currentImportPackageInfo[$packageId] = array(
          'id' => $packageId,
          'name' => (isset($packageInfo['name']) ? $packageInfo['name'] : 'unknown'),
          'directory' => $packageDirectory,
        );
      }
      $iterator->next();
    }
  }

  /**
   * @param DirectoryIterator $iterator
   *
   * @return boolean
   */
  private function isCurrentIteratorAModulDirectory(\DirectoryIterator $iterator)
  {
    if (!$iterator->isDir()) {
      return false;
    }
    return $this->getModuleIdValidatorWithLegacySupport()->isValid($iterator->getFilename());
  }

  /**
   * @param DirectoryIterator $iterator
   *
   * @return boolean
   */
  private function isCurrentIteratorAPageDirectory(\DirectoryIterator $iterator)
  {
    $regexPage = '(' . preg_quote(DataPage::ID_PREFIX, '/')
      . '.*?' . preg_quote(DataPage::ID_SUFFIX, '/') . ')';

    if ($iterator->isDir() && preg_match($regexPage, $iterator->getFilename())) {
      return true;
    }
    return false;
  }

  /**
   * @param DirectoryIterator $iterator
   *
   * @return boolean
   */
  private function isCurrentIteratorATemplateDirectory(\DirectoryIterator $iterator)
  {
    $regexpTemplate = '(' . preg_quote(DataTemplate::ID_PREFIX, '/')
      . '.*?' . preg_quote(DataTemplate::ID_SUFFIX, '/') . ')';

    if ($iterator->isDir() && preg_match($regexpTemplate, $iterator->getFilename())) {
      return true;
    }
    return false;
  }

  /**
   * @param DirectoryIterator $iterator
   *
   * @return boolean
   */
  private function isCurrentIteratorAMediaDirectory(\DirectoryIterator $iterator)
  {
    $regexpMedia = '(' . preg_quote(DataMedia::ID_PREFIX, '/')
      . '.*?' . preg_quote(DataMedia::ID_SUFFIX, '/') . ')';

    if ($iterator->isDir() && preg_match($regexpMedia, $iterator->getFilename())) {
      return true;
    }
    return false;
  }

  /**
   * @param DirectoryIterator $iterator
   *
   * @return boolean
   */
  private function isCurrentIteratorATemplateSnippetDirectory(\DirectoryIterator $iterator)
  {
    $regexpTemplateSnippet = '(' . preg_quote(DataTemplateSnippet::ID_PREFIX, '/')
      . '.*?' . preg_quote(DataTemplateSnippet::ID_SUFFIX, '/') . ')';

    if ($iterator->isDir() && preg_match($regexpTemplateSnippet, $iterator->getFilename())) {
      return true;
    }
    return false;
  }

  /**
   * @param \DirectoryIterator $iterator
   *
   * @return boolean
   */
  protected function isCurrentIteratorAPackageDirectory(\DirectoryIterator $iterator)
  {
    if (!$iterator->isDir()) {
      return false;
    }
    return $this->getPackageIdValidator()->isValid($iterator->getFilename());
  }

  /**
   * check if import of item is allowed
   *
   * @return boolean
   */
  protected function itemImportAllowed($itemId, array $conflictingIds, array $overwriteIds)
  {
    if (!in_array($itemId, $conflictingIds)) {
      return true;
    } elseif (in_array($itemId, $overwriteIds)) {
      return true;
    }

    return false;
  }

  /**
   * remove item locks
   *
   * @param string  $importMode
   * @param string  $websiteId
   * @param boolean $overwrite
   * @param array   $overwriteTemplateIds
   * @param array   $overwriteModuleIds
   * @param array   $overwriteTemplateSnippetIds
   * @param array   $overwriteMediaIds
   */
  protected function removeLocks(
      $importMode,
      $websiteId,
      $overwrite = false,
      array $overwriteTemplateIds = array(),
      array $overwriteModuleIds = array(),
      array $overwriteTemplateSnippetIds = array(),
      array $overwriteMediaIds = array()
  ) {
    if (!$this->getWebsiteService()->existsWebsiteAlready($websiteId)) {
      return;
    }

    switch ($importMode) {
      case ExportBusiness::EXPORT_MODE_MODULE:
        $this->removeModuleLocks($websiteId, $overwrite, $overwriteModuleIds);
            break;

      case ExportBusiness::EXPORT_MODE_WEBSITE:
        // ToDo: get overwrite pages
        $this->removePageLocks($websiteId, $overwrite, array());
        $this->removeTemplateLocks($websiteId, $overwrite, $overwriteTemplateIds);
        $this->removeModuleLocks($websiteId, $overwrite, $overwriteModuleIds);
            break;

      default:
        /* NO LOCK TO REMOVE */
            break;
    }
  }

  /**
   * remove module locks
   *
   * @param string  $websiteId
   * @param boolean $overwrite
   * @param array   $overwriteModuleIds
   */
  protected function removeModuleLocks(
      $websiteId,
      $overwrite = false,
      array $overwriteModuleIds = array()
  ) {
    if (isset($this->currentImportModuleInfo)) {
      if ($overwrite) {
        $removeLocksFromModules = $overwriteModuleIds;
      } else {
        $removeLocksFromModules = array_keys($this->currentImportModuleInfo);
      }
      $this->lockBusiness->removeLocks(
          $websiteId,
          LockBusiness::LOCK_TYPE_MODULE,
          $removeLocksFromModules,
          true
      );
    }
  }

  /**
   * remove template locks
   *
   * @param string  $websiteId
   * @param boolean $overwrite
   * @param array   $overwriteTemplateIds
   */
  protected function removeTemplateLocks(
      $websiteId,
      $overwrite = false,
      array $overwriteTemplateIds = array()
  ) {
    if (isset($this->currentImportTemplateInfo)) {
      if ($overwrite) {
        $removeLocksFromTemplates = $overwriteTemplateIds;
      } else {
        $removeLocksFromTemplates = array_keys($this->currentImportTemplateInfo);
      }
      $this->lockBusiness->removeLocks(
          $websiteId,
          LockBusiness::LOCK_TYPE_TEMPLATE,
          $removeLocksFromTemplates,
          true
      );
    }
  }

  /**
   * remove page locks
   *
   * @param string  $websiteId
   * @param boolean $overwrite
   * @param array   $overwritePageIds
   */
  protected function removePageLocks(
      $websiteId,
      $overwrite = false,
      array $overwritePageIds = array()
  ) {
    if (isset($this->currentImportPageInfo)) {
      if ($overwrite) {
        $removeLocksFromTemplates = $overwritePageIds;
      } else {
        $removeLocksFromTemplates = array_keys($this->currentImportPageInfo);
      }
      $this->lockBusiness->removeLocks(
          $websiteId,
          LockBusiness::LOCK_TYPE_PAGE,
          $overwritePageIds,
          true
      );
    }
  }

  /**
   * remove page locks
   *
   * @param   string $websiteId
   * @param   string $itemId
   * @param   string $itemType module/template/page/website
   *
   * @return  array/null
   */
  protected function collectingLockInfo($websiteId, $itemId, $itemType)
  {
    $lockType = null;

    switch ($itemType) {
      case 'module':
        $lockType = LockBusiness::LOCK_TYPE_MODULE;
            break;

      case 'template':
        $lockType = LockBusiness::LOCK_TYPE_TEMPLATE;
            break;

      case 'page':
        $lockType = LockBusiness::LOCK_TYPE_PAGE;
            break;

      case 'website':
        $lockType = LockBusiness::LOCK_TYPE_WEBSITE;
            break;

      default:
            return;
        break;
    }

    $itemInfo = $lockInfo = null;
    $isLocked = $this->lockBusiness->isLocked($itemId, $websiteId, $lockType, $itemInfo, $lockInfo);
    if ($isLocked && is_array($lockInfo)) {
      $locks = array();
      foreach ($lockInfo as $nextLockInfo) {
        $locks[] = array(
          'websiteId' => $nextLockInfo['websiteId'],
          'type' => $nextLockInfo['type'],
          'id' => $nextLockInfo['itemId'],
          'name' => $nextLockInfo['itemname'],
          'user' => array(
            'id' => $nextLockInfo['user']['id'],
            'firstname' => $nextLockInfo['user']['firstname'],
            'lastname' => $nextLockInfo['user']['lastname'],
          ),
          'activity' => array(
            'start' => $nextLockInfo['starttime'],
            'last' => $nextLockInfo['lastactivity'],
          ),
        );
      }
      return $locks;
    }

    return;
  }

  /**
   * Cleaning the ColorIds
   * - remove the fallback color if the colorId exists
   *
   * @param  string $websiteId
   * @param         mixed ref  $mixedData
   */
  protected function resetColorIdsToBaseId(&$mixedData)
  {
    if (is_string($mixedData)) {
      if (!isset($this->resetColorIdCallback) || !is_callable($this->resetColorIdCallback)) {
        // create callback
        $this->resetColorIdCallback = function ($matches) {
          $baseColorId = WebsiteColor::getBaseIdFromColorId($matches[0]);
          return $baseColorId;
        };
      }

      $regexpColorId = '/((' . preg_quote(DataColorItem::ID_PREFIX, '/')
        . ')(.*?)(' . preg_quote(DataColorItem::ID_SUFFIX, '/') . '))/i';
      $mixedData = preg_replace_callback($regexpColorId, $this->resetColorIdCallback, $mixedData);
    } elseif (is_array($mixedData)) {
      foreach ($mixedData as $key => &$item) {
        // extending colorIds in item value
        $this->resetColorIdsToBaseId($item);

        // extending colorIds in key value
        $orgKey = $key;
        $this->resetColorIdsToBaseId($key);
        if ($orgKey != $key) {
          $mixedData[$key] = $mixedData[$orgKey];
          unset($mixedData[$orgKey]);
        }
      }
    }
  }

  /**
   * Cleaning the ColorIds
   * - remove the fallback color if the colorId exists
   *
   * @param  string $websiteId
   * @param         mixed ref  $mixedData
   */
  protected function cleaningColorIds($websiteId, &$mixedData)
  {
    if (is_string($mixedData)) {
      $replaceCallback = $this->getCleaningColorIdCallback($websiteId);
      $regexpColorId = '/((' . preg_quote(DataColorItem::ID_PREFIX, '/')
        . ')(.*?)(' . preg_quote(DataColorItem::ID_SUFFIX, '/') . '))/i';
      $mixedData = preg_replace_callback($regexpColorId, $replaceCallback, $mixedData);

    } elseif (is_array($mixedData)) {
      foreach ($mixedData as $key => &$item) {
        // extending colorIds in item value
        $this->cleaningColorIds($websiteId, $item);

        // extending colorIds in key value
        $orgKey = $key;
        $this->cleaningColorIds($websiteId, $key);
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
   * @param  string $websiteId
   *
   * @return callback
   */
  protected function getCleaningColorIdCallback($websiteId)
  {
    if (isset($this->cleaningColorIdCallbacks[$websiteId])
      && is_callable($this->cleaningColorIdCallbacks[$websiteId])
    ) {
      return $this->cleaningColorIdCallbacks[$websiteId];
    }

    $website = $this->getWebsiteService()->getById($websiteId);
    $colorscheme = $website->getColorscheme();
    $colors = array();

    // decode colorscheme
    if (is_string($colorscheme)) {
      try {
        // JSON decodieren und Array aufbereiten
        $colorscheme = \Zend_Json::decode($colorscheme);
      } catch (\Exception $e) {
        // Fehler
        Registry::getLogger()->log(__CLASS__, __METHOD__, 'Farbeschema konnte nicht erstellt werden: ' . $e->errorMessage, \Zend_Log::WARN);
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
    $this->cleaningColorIdCallbacks[$websiteId] = function ($matches) use ($colors) {
      $orgColorId = $matches[0];
      $baseColorId = WebsiteColor::getBaseIdFromColorId($matches[0]);
      if (isset($colors[$baseColorId])) {
        // return base colorId
        return $baseColorId;
      }

      // return original colorId
      return $orgColorId;
    };

    return $this->cleaningColorIdCallbacks[$websiteId];
  }

  /**
   * @return \Cms\Validator\ModuleId
   */
  protected function getModuleIdValidatorWithLegacySupport()
  {
    if (isset($this->moduleIdValidator)) {
      return $this->moduleIdValidator;
    }

    $this->moduleIdValidator = new ModuleIdValidator(true);
    return $this->moduleIdValidator;
  }

  /**
   * @return PackageIdValidator
   */
  protected function getPackageIdValidator()
  {
    if (isset($this->packageIdValidator)) {
      return $this->packageIdValidator;
    }

    $this->packageIdValidator = new PackageIdValidator();
    return $this->packageIdValidator;
  }

  /**
   * @return string
   */
  protected function getDefaultResolutionsJson()
  {
    return '{"enabled":false,"data":[]}';
  }

  /**
   * @param string $resolutionsJson
   *
   * @return string
   */
  protected function createResolutionsJson($resolutionsJson)
  {
    if (!is_string($resolutionsJson)) {
      return $this->getDefaultResolutionsJson();
    }

    $resolutions = json_decode($resolutionsJson);
    if (!is_object($resolutions)) {
      return $this->getDefaultResolutionsJson();
    }

    $existingIds = array();
    $newResolutions = json_decode($this->getDefaultResolutionsJson());

    if (property_exists($resolutions, 'enabled')) {
      $newResolutions->enabled = ($resolutions->enabled === true);
    }

    if (property_exists($resolutions, 'data')) {
      foreach ($resolutions->data as $resolution) {
        if (!is_object($resolution)) {
          continue;
        }
        if (property_exists($resolution, 'id')) {
          if (!is_string($resolution->id)) {
            unset($resolution->id);
          } else {
            $existingIds[] = $resolution->id;
          }
        }
        if (!property_exists($resolution, 'width')) {
          $resolution->width = 0;
        }
        $newResolutions->data[] = $resolution;
      }
    }

    usort($newResolutions->data, function ($a, $b) {
      if ($a->width == $b->width) {
        return 0;
      }
      // sort desc
      return ($a->width > $b->width) ? -1 : +1;
    });

    foreach ($newResolutions->data as &$resolution) {
      if (!property_exists($resolution, 'id')) {
        $resolution->id = $this->createNextResolutionId($existingIds);
        $existingIds[] = $resolution->id;
      }
      if (!property_exists($resolution, 'name')) {
        $resolution->name = $resolution->id;
      }
    }

    return json_encode($newResolutions);
  }

  protected function createNextResolutionId(array $existingIds)
  {
    $count = 0;
    do {
      if ($count > 100) {
        return uniqid('resSec');
      }
      $newResId = 'res' . ++$count;
    } while (in_array($newResId, $existingIds));
    return $newResId;
  }

  /**
   * @param array $attributes
   *
   * @return \Cms\Data\Website
   */
  protected function createWebsite($attributes)
  {
    return $this->getWebsiteService()->create($attributes);
  }

  /**
   * @param string|null $websiteName
   *
   * @return \Cms\Data\Website
   * @throws \Zend_Exception
   */
  protected function createNewWebsite($websiteName)
  {
    $translate = Registry::get('Zend_Translate');
    if (!is_string($websiteName)) {
      $websiteName = sprintf(
          $translate->_('import.file.new_website_name'),
          $this->getImportFormattedDateTime()
      );
    }
    $attributes = array('name' => $websiteName);
    return $this->createWebsite($attributes);
  }

  /**
   * @param string $websiteId
   * @param mixed  $orgContent
   */
  protected function updateContentWithLegacyDefaultValues($websiteId, &$orgContent)
  {
    $newContent = $orgContent;
    if (is_string($newContent)) {
      $newContent = json_decode($newContent);
    }
    if (!is_array($newContent)) {
      return;
    }
    try {
      $this->getLegacyDefaultFormValuesUpdater($websiteId)->updateDefaultFormValues($newContent);
    } catch (\Exception $doNothing) {
      return;
    }
    $orgContent = json_encode($newContent);
  }

  /**
   * check quota
   *
   * @param string $websiteId
   * @param string $importMode
   */
  protected function checkQuota($websiteId, $importMode)
  {
    // checks website quota
    $websiteExists = $this->getWebsiteService()->existsWebsiteAlready($websiteId);
    if (!$websiteExists || $importMode == ExportBusiness::EXPORT_MODE_WEBSITE) {
      $this->checkWebsiteQuota();
    }
    // checks module quota
    if (count($this->currentImportModuleInfo) > 0 || count($this->currentImportPackageInfo) > 0) {
      $this->checkModuleQuota();
    }
  }

  /**
   * Website Quota Check
   */
  protected function checkWebsiteQuota()
  {
    $this->getWebsiteService()->checkWebsiteMaxCountQuota();
  }

  /**
   * Module Quota Check
   */
  protected function checkModuleQuota()
  {
    $this->getModuleService()->checkModuleDevelopmentQuota();
  }

  /**
   * @return \Cms\Service\Website
   */
  protected function getWebsiteService()
  {
    if (is_null($this->websiteService)) {
      $this->websiteService = $this->getService('Website');
    }
    return $this->websiteService;
  }

  /**
   * @return \Cms\Service\TemplateSnippet
   */
  protected function getTemplateSnippetService()
  {
    if (is_null($this->templateSnippetService)) {
      $this->templateSnippetService = $this->getService('TemplateSnippet');
    }
    return $this->templateSnippetService;
  }

  /**
   * @return \Cms\Service\Modul
   */
  protected function getModuleService()
  {
    if (is_null($this->moduleService)) {
      $this->moduleService = $this->getService('Modul');
    }
    return $this->moduleService;
  }

  /**
   * @return \Cms\Service\Media
   */
  protected function getMediaService()
  {
    if (is_null($this->mediaService)) {
      $this->mediaService = $this->getService('Media');
    }
    return $this->mediaService;
  }

  /**
   * @return \Cms\Service\WebsiteSettings
   */
  protected function getWebsiteSettingsService()
  {
    if (isset($this->websiteSettingsService)) {
      return $this->websiteSettingsService;
    }
    $this->websiteSettingsService = $this->getService('WebsiteSettings');
    return $this->websiteSettingsService;
  }

  /**
   * @return \Cms\Service\PageType
   */
  protected function getPageTypeService()
  {
    if (isset($this->pageTypeService)) {
      return $this->pageTypeService;
    }
    $this->pageTypeService = $this->getService('PageType');
    return $this->pageTypeService;
  }

  /**
   * @return string|null
   */
  protected function getDefaultPageTypeId()
  {
    if (isset($this->defaultPageTypeId)) {
      return $this->defaultPageTypeId;
    }
    $this->defaultPageTypeId = $this->getPageTypeService()->getDefaultPageTypeId();
    return $this->defaultPageTypeId;
  }

  /**
   * @return \Cms\Service\Package
   */
  protected function getPackageService()
  {
    if (isset($this->packageService)) {
      return $this->packageService;
    }
    $this->packageService = $this->getService('Package');
    return $this->packageService;
  }

  /**
   * @param string $websiteId
   *
   * @return LegacyDefaultFormValuesUpdater
   */
  protected function getLegacyDefaultFormValuesUpdater($websiteId)
  {
    if (!isset($this->legacyDefaultFormValuesUpdater[$websiteId])) {
      $this->legacyDefaultFormValuesUpdater[$websiteId] = new LegacyDefaultFormValuesUpdater(
          $websiteId,
          $this->getModuleService()
      );
    }
    return $this->legacyDefaultFormValuesUpdater[$websiteId];
  }

  /**
   * @return LatchBusiness
   */
  private function getLatchBusiness()
  {
    return new LatchBusiness('Latch');
  }
}
