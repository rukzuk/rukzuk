<?php


namespace Cms\Dao\Module;

use Cms\Dao\Base\AbstractSource;
use Cms\Dao\Base\Filesystem as BaseFilesystemDao;
use Cms\Dao\Base\DynamicSourceItem;
use Cms\Dao\Base\SourceItem;
use Cms\Dao\Module as ModuleDaoInterface;
use Cms\Dao\Module\Source as ModuleSource;
use Cms\Data\Modul as DataModule;
use Orm\Data\Modul as OrmDataModule;
use Cms\Validator\ModuleId as ModuleIdValidator;
use Cms\Exception as CmsException;
use Cms\ExceptionStack as CmsExceptionStack;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as Log;
use Seitenbau\FileSystem as FS;
use Seitenbau\Json as SbJson;
use Seitenbau\UniqueIdGenerator;

/**
 * Filesystem dao for modules
 *
 * @package Cms\Dao\Module
 */
class Filesystem extends BaseFilesystemDao implements ModuleDaoInterface
{
  const SUBDIR_MODULES = 'modules';
  const SUBDIR_DATA = 'module';
  const SUBDIR_ASSETS = 'assets';
  const FILE_MANIFEST = 'manifest.json';
  const FILE_FORM = 'form.json';
  const FILE_CUSTOM_DATA = 'custom.json';
  const LEGACY_FILE_MANIFEST = 'moduleManifest.json';
  const LEGACY_FILE_DATA = 'moduleData.json';

  protected $dataDirectory;
  protected $dataWebPath;
  protected $legacyModuleMatch;
  protected $moduleIdValidator;

  public function __construct($resetCache = false)
  {
    $moduleConfig = Registry::getConfig()->item->data;
    $this->dataDirectory = $moduleConfig->directory;
    $this->dataWebPath = $moduleConfig->webpath;

    $this->legacyModuleMatch = sprintf(
        '/%s[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}%s$/',
        preg_quote(OrmDataModule::ID_PREFIX, '/'),
        preg_quote(OrmDataModule::ID_SUFFIX, '/')
    );

    if ($resetCache) {
      $this->resetCache();
    }
  }

  /**
   * @param ModuleSource $source
   * @param string       $id
   *
   * @throws \Cms\Exception
   * @return DataModule
   */
  public function getById(ModuleSource $source, $id)
  {
    return $this->internalGetById($source, $id);
  }

  /**
   * @param ModuleSource $source
   * @param string[]     $ids
   *
   * @return array
   */
  public function getByIds(ModuleSource $source, array $ids)
  {
    $modules = array();
    foreach ($ids as $id) {
      $modules[] = $this->getById($source, $id);
    }
    $this->sortModulesByName($modules);
    return $modules;
  }

  /**
   * @param ModuleSource $source
   *
   * @return DataModule[]
   */
  public function getAll(ModuleSource $source)
  {
    $modules = $this->internalGetAll($source);
    $this->sortModulesByName($modules);
    return $modules;
  }

  /**
   * @param ModuleSource $source
   * @param string       $id
   * @param bool         $onlyLocal
   *
   * @return bool
   */
  public function existsModule(ModuleSource $source, $id, $onlyLocal = false)
  {
    if (!$this->internalExists($source, $id)) {
      return false;
    }

    // no check of source type
    if (!$onlyLocal) {
      return true;
    }

    $allBaseInfo = $this->getAllBaseInfo($source);
    return $allBaseInfo[$id]->getType() === SourceItem::SOURCE_LOCAL;
  }

  /**
   * @param ModuleSource $moduleSource
   * @param string       $id
   *
   * @return string
   */
  public function getDataPath(ModuleSource $moduleSource, $id)
  {
    return FS::joinPath($this->getModulePath($moduleSource, $id), self::SUBDIR_DATA);
  }

  /**
   * @param ModuleSource $moduleSource
   * @param string       $id
   *
   * @return string
   */
  public function getAssetsPath(ModuleSource $moduleSource, $id)
  {
    return FS::joinPath($this->getModulePath($moduleSource, $id), self::SUBDIR_ASSETS);
  }

  /**
   * @param ModuleSource $moduleSource
   * @param string       $id
   *
   * @return string
   */
  public function getAssetsUrl(ModuleSource $moduleSource, $id)
  {
    return $this->getModuleUrl($moduleSource, $id) . '/' . self::SUBDIR_ASSETS;
  }

  /**
   * @param ModuleSource $moduleSource
   * @param string       $id
   *
   * @return string
   */
  protected function getModulePath(ModuleSource $moduleSource, $id)
  {
    $sourceItem = $this->getBaseInfo($moduleSource, $id);
    return $sourceItem->getDirectory();
  }

  /**
   * @param ModuleSource $moduleSource
   * @param string       $id
   *
   * @return string
   */
  protected function getModuleUrl(ModuleSource $moduleSource, $id)
  {
    $sourceItem = $this->getBaseInfo($moduleSource, $id);
    return $sourceItem->getUrl();
  }

  /**
   * @param string $websiteId
   *
   * @return string
   */
  protected function getWebsiteModulesDataDirectory($websiteId)
  {
    return FS::joinPath($this->dataDirectory, $websiteId, self::SUBDIR_MODULES);
  }

  /**
   * @param string $websiteId
   *
   * @return string
   */
  protected function getWebsiteModulesUrl($websiteId)
  {
    return $this->dataWebPath . '/' . $websiteId . '/' . self::SUBDIR_MODULES;
  }

  /**
   * create a new module
   *
   * @param ModuleSource $moduleSource
   * @param DataModule   $module
   * @param SourceItem   $sourceItem
   *
   * @return DataModule
   * @throws CmsException
   */
  public function create(ModuleSource $moduleSource, DataModule $module, SourceItem $sourceItem = null)
  {
    $websiteId = $moduleSource->getWebsiteId();
    if (is_null($sourceItem)) {
      $sourceItem = $this->createSourceItemForNewModule($websiteId, $module);
    }

    if (is_dir($sourceItem->getDirectory())) {
      $errorData = array('id' => $module->getId(), 'websiteid' => $websiteId);
      throw new CmsException(113, __METHOD__, __LINE__, $errorData);
    }
    $this->createModuleDirectoriesIfNotExits($sourceItem->getDirectory());

    $module->setWebsiteid($websiteId);
    $module->setId($sourceItem->getId());
    $this->save($sourceItem->getDirectory(), $module);
    $this->resetCache();
    return $this->loadCachedEntity($websiteId, $module->getId(), $sourceItem, array());
  }

  /**
   * deletes the modules given by $ids
   *
   * @param ModuleSource $moduleSource
   * @param string[]     $ids
   */
  public function deleteByIds(ModuleSource $moduleSource, array $ids)
  {
    $allModuleInfo = $this->getAllBaseInfo($moduleSource);

    foreach ($ids as $id) {
      if (!isset($allModuleInfo[$id]) || empty($allModuleInfo[$id])) {
        continue;
      }
      try {
        $this->checkIfModuleIsWritable($allModuleInfo[$id]);
        FS::rmdir($allModuleInfo[$id]->getDirectory());
      } catch (\Exception $e) {
        CmsExceptionStack::addException($e);
      }
    }
    $this->resetCache();

    if (CmsExceptionStack::hasErrors()) {
      CmsExceptionStack::throwErrors();
    }
  }

  /**
   * @param string $websiteId
   */
  public function deleteByWebsiteId($websiteId)
  {
    $websiteModulesDataDir = $this->getWebsiteModulesDataDirectory($websiteId);
    if (!empty($websiteModulesDataDir) && is_dir($websiteModulesDataDir)
      && strpos($websiteModulesDataDir, $this->dataDirectory) === 0
    ) {
      FS::rmdir($websiteModulesDataDir);
    }
    $this->resetCache();
  }

  /**
   * creates the storage for the local website modules given by websiteId
   *
   * @param string $websiteId
   */
  public function createStorageForWebsite($websiteId)
  {
    FS::createDirIfNotExists($this->getWebsiteModulesDataDirectory($websiteId), true);
  }

  /**
   * @param ModuleSource $moduleSourceFrom
   * @param ModuleSource $moduleSourceTo
   */
  public function copyToNewWebsite(ModuleSource $moduleSourceFrom, ModuleSource $moduleSourceTo)
  {
    $sourceDir = $this->getWebsiteModulesDataDirectory($moduleSourceFrom->getWebsiteId());
    $destinationDir = $this->getWebsiteModulesDataDirectory($moduleSourceTo->getWebsiteId());
    FS::createDirIfNotExists($destinationDir, true);
    if (is_dir($sourceDir)) {
      FS::copyDir($sourceDir, $destinationDir);
    }
    $this->resetCache();
  }

  /**
   * @param string     $websiteId
   * @param string     $id
   * @param SourceItem $sourceItem
   * @param array      $additionalData
   *
   * @return object
   * @throws \Exception
   */
  protected function loadEntity($websiteId, $id, SourceItem $sourceItem, array $additionalData)
  {
    if ($this->isLegacyModule($id)) {
      return $this->getLegacyModule($websiteId, $id, $sourceItem);
    } else {
      return $this->getRegularModule($websiteId, $id, $sourceItem);
    }
  }

  /**
   * @param string     $websiteId
   * @param string     $moduleId
   * @param SourceItem $sourceItem
   *
   * @return DataModule
   */
  protected function getRegularModule($websiteId, $moduleId, SourceItem $sourceItem)
  {
    $moduleDataDirectory = FS::joinPath($sourceItem->getDirectory(), self::SUBDIR_DATA);

    $moduleManifest = $this->loadJsonFile($moduleDataDirectory, self::FILE_MANIFEST, new \stdClass());
    $moduleForm = $this->loadJsonFile($moduleDataDirectory, self::FILE_FORM, new \stdClass());
    $moduleCustomData = $this->loadJsonFile($moduleDataDirectory, self::FILE_CUSTOM_DATA, new \stdClass());
    $moduleLastUpdate = $this->getLastUpdateFromRegularFiles($moduleDataDirectory);

    return $this->loadDataObject(
        $websiteId,
        $moduleId,
        $sourceItem,
        $moduleManifest,
        $moduleForm,
        $moduleCustomData,
        $moduleLastUpdate
    );
  }

  /**
   * @param string     $websiteId
   * @param string     $moduleId
   * @param SourceItem $sourceItem
   *
   * @return DataModule
   */
  protected function getLegacyModule($websiteId, $moduleId, SourceItem $sourceItem)
  {
    $moduleDataDirectory = FS::joinPath($sourceItem->getDirectory(), self::SUBDIR_DATA);

    $moduleManifest = $this->loadJsonFile($moduleDataDirectory, self::LEGACY_FILE_MANIFEST, new \stdClass());
    $moduleData = $this->loadJsonFile($moduleDataDirectory, self::LEGACY_FILE_DATA, new \stdClass());
    $moduleLastUpdate = $this->getLastUpdateFromLegacyFiles($moduleDataDirectory);

    return $this->loadDataObject(
        $websiteId,
        $moduleId,
        $sourceItem,
        $moduleManifest,
        $moduleData,
        null,
        $moduleLastUpdate
    );
  }

  /**
   * @param string     $websiteId
   * @param string     $moduleId
   * @param SourceItem $sourceItem
   * @param \stdClass  $moduleManifest
   * @param \stdClass  $moduleForm
   * @param \stdClass  $moduleCustomData
   * @param integer    $moduleLastUpdate
   *
   * @return DataModule
   */
  protected function loadDataObject(
      $websiteId,
      $moduleId,
      SourceItem $sourceItem,
      $moduleManifest,
      $moduleForm,
      $moduleCustomData,
      $moduleLastUpdate
  ) {
    $module = new DataModule();

    $module->setId($moduleId);
    $module->setWebsiteid($websiteId);
    $module->setCustomData($moduleCustomData);
    $module->setLastUpdate($moduleLastUpdate);
    $module->setSource($sourceItem);
    $module->setSourceType($sourceItem->getType());
    $module->setOverwritten($sourceItem->isOverwritten());

    if (property_exists($moduleManifest, 'name')) {
      $module->setName($moduleManifest->name);
    }
    if (property_exists($moduleManifest, 'description')) {
      $module->setDescription($moduleManifest->description);
    }
    if (property_exists($moduleManifest, 'version')) {
      $module->setVersion($moduleManifest->version);
    }
    if (property_exists($moduleManifest, 'icon')) {
      $module->setIcon($moduleManifest->icon);
    }
    if (property_exists($moduleManifest, 'category')) {
      $module->setCategory($moduleManifest->category);
    }
    if (property_exists($moduleManifest, 'moduleType')) {
      $module->setModuletype($moduleManifest->moduleType);
    }
    if (property_exists($moduleManifest, 'allowedChildModuleType')) {
      $module->setAllowedchildmoduletype($moduleManifest->allowedChildModuleType);
    }
    if (property_exists($moduleManifest, 'reRenderRequired')) {
      $module->setRerenderrequired($moduleManifest->reRenderRequired);
    }
    if (property_exists($moduleManifest, 'ghostContainerMode')) {
      $module->setGhostContainerMode($moduleManifest->ghostContainerMode);
    }
    if (property_exists($moduleForm, 'form')) {
      $module->setForm($moduleForm->form);
    }
    if (property_exists($moduleForm, 'formValues')) {
      $module->setFormvalues($moduleForm->formValues);
    }
    if (property_exists($moduleManifest, 'apiType')) {
      $module->setApiType($moduleManifest->apiType);
    }
    if (property_exists($moduleManifest, 'sessionRequired')) {
      $module->setSessionRequired($moduleManifest->{'sessionRequired'});
    }
    if (property_exists($moduleManifest, 'config')) {
      $module->setConfig($moduleManifest->{'config'});
    }

    return $module;
  }

  /**
   * @param string $moduleDataDirectory
   *
   * @return int
   */
  protected function getLastUpdateFromRegularFiles($moduleDataDirectory)
  {
    $lastUpdate = 0;

    $manifestFileTime = $this->getFileModifyTime($moduleDataDirectory, self::FILE_MANIFEST);
    if ($manifestFileTime > $lastUpdate) {
      $lastUpdate = $manifestFileTime;
    }
    $dataFileTime = $this->getFileModifyTime($moduleDataDirectory, self::FILE_FORM);
    if ($dataFileTime > $lastUpdate) {
      $lastUpdate = $dataFileTime;
    }
    $customDataFileTime = $this->getFileModifyTime($moduleDataDirectory, self::FILE_CUSTOM_DATA);
    if ($customDataFileTime > $lastUpdate) {
      $lastUpdate = $customDataFileTime;
    }

    return $lastUpdate;
  }

  /**
   * @param string $moduleDataDirectory
   *
   * @return int
   */
  protected function getLastUpdateFromLegacyFiles($moduleDataDirectory)
  {
    $lastUpdate = 0;

    $manifestFileTime = $this->getFileModifyTime($moduleDataDirectory, self::LEGACY_FILE_MANIFEST);
    if ($manifestFileTime > $lastUpdate) {
      $lastUpdate = $manifestFileTime;
    }
    $dataFileTime = $this->getFileModifyTime($moduleDataDirectory, self::LEGACY_FILE_DATA);
    if ($dataFileTime > $lastUpdate) {
      $lastUpdate = $dataFileTime;
    }

    return $lastUpdate;
  }

  /**
   * set the new module attributes
   *
   * @param string     $moduleDirectory
   * @param DataModule $module
   */
  protected function save($moduleDirectory, DataModule $module)
  {
    if ($this->isLegacyModule($module->getId())) {
      $this->saveLegacyModule($moduleDirectory, $module);
    } else {
      $this->saveRegularModule($moduleDirectory, $module);
    }
  }

  /**
   * @param string     $moduleDirectory
   * @param DataModule $module
   */
  protected function saveRegularModule($moduleDirectory, DataModule $module)
  {
    $this->createModuleDirectoriesIfNotExits($moduleDirectory);
    $moduleDataDirectory = FS::joinPath($moduleDirectory, self::SUBDIR_DATA);

    $this->writeModuleManifestFile($moduleDataDirectory, self::FILE_MANIFEST, $module);
    $this->writeModuleDataFile($moduleDataDirectory, self::FILE_FORM, $module);
    $this->storeJsonFile($moduleDataDirectory, self::FILE_CUSTOM_DATA, $module->getCustomData());
  }

  /**
   * @param string     $moduleDirectory
   * @param DataModule $module
   */
  protected function saveLegacyModule($moduleDirectory, DataModule $module)
  {
    $this->createModuleDirectoriesIfNotExits($moduleDirectory);
    $moduleDataDirectory = FS::joinPath($moduleDirectory, self::SUBDIR_DATA);

    $this->writeModuleManifestFile($moduleDataDirectory, self::LEGACY_FILE_MANIFEST, $module);
    $this->writeModuleDataFile($moduleDataDirectory, self::LEGACY_FILE_DATA, $module);
  }

  /**
   * @param string     $moduleDataDirectory
   * @param string     $fileName
   * @param DataModule $module
   */
  protected function writeModuleManifestFile($moduleDataDirectory, $fileName, DataModule $module)
  {
    $moduleManifest = new \stdClass();
    $moduleManifest->name = $module->getName();
    $moduleManifest->description = $module->getDescription();
    $moduleManifest->version = $module->getVersion();
    $moduleManifest->icon = $module->getIcon();
    $moduleManifest->category = $module->getCategory();
    $moduleManifest->moduleType = $module->getModuletype();
    $moduleManifest->allowedChildModuleType = $module->getAllowedchildmoduletype();
    $moduleManifest->reRenderRequired = $module->getRerenderrequired();
    $moduleManifest->ghostContainerMode = $module->getGhostContainerMode();
    $moduleManifest->apiType = $module->getApiType();
    $moduleManifest->sessionRequired = $module->getSessionRequired();
    $moduleManifest->config = $module->getConfig();
    $this->storeJsonFile($moduleDataDirectory, $fileName, $moduleManifest);
  }

  /**
   * @param string     $moduleDataDirectory
   * @param string     $fileName
   * @param DataModule $module
   */
  protected function writeModuleDataFile($moduleDataDirectory, $fileName, DataModule $module)
  {
    $moduleData = new \stdClass();
    $moduleData->form = $module->getForm();
    $moduleData->formValues = $module->getFormvalues();
    $this->storeJsonFile($moduleDataDirectory, $fileName, $moduleData);
  }

  /**
   * @param string $moduleDataDirectory
   * @param string $filename
   * @param mixed  $data
   *
   * @throws \Exception
   */
  protected function storeJsonFile($moduleDataDirectory, $filename, $data)
  {
    $dataJson = SbJson::prettyPrint(SbJson::encode($data));
    $this->setFileContent($moduleDataDirectory, $filename, $dataJson);
  }

  /**
   * @param string $directory
   * @param string $filename
   * @param string $content
   *
   * @throws \Exception
   */
  protected function setFileContent($directory, $filename, $content)
  {
    $filepath = FS::joinPath($directory, $filename);
    if (@file_put_contents($filepath, $content) === false) {
      $errors = error_get_last();
      $exceptionMessage = sprintf(
          "couldn't save content to file '%s' (%s): %s",
          $filepath,
          $errors['type'],
          $errors['message']
      );
      throw new \Exception($exceptionMessage);
    }
  }

  /**
   * @param string $moduleDir
   */
  protected function createModuleDirectoriesIfNotExits($moduleDir)
  {
    FS::createDirIfNotExists($moduleDir, true);
    FS::createDirIfNotExists(FS::joinPath($moduleDir, self::SUBDIR_DATA));
    FS::createDirIfNotExists(FS::joinPath($moduleDir, self::SUBDIR_ASSETS));
  }

  /**
   * @param SourceItem $sourceItem
   *
   * @throws ModuleNotWritable
   */
  protected function checkIfModuleIsWritable(SourceItem $sourceItem)
  {
    if ($sourceItem->isReadonly()) {
      throw new ModuleNotWritable(114, __METHOD__, __LINE__, array(
        'id' => $sourceItem->getId(),
        'type' => $sourceItem->getType(),
        'directory' => $sourceItem->getDirectory(),
      ));
    }
  }

  /**
   * @param DataModule[] $modules
   */
  protected function sortModulesByName(&$modules)
  {
    usort($modules, function ($a, $b) {
      return strnatcasecmp($a->getName(), $b->getName());
    });
  }

  /**
   * @param string $moduleId
   *
   * @return int
   */
  protected function isLegacyModule($moduleId)
  {
    // is legacy module id
    return (preg_match($this->legacyModuleMatch, $moduleId, $matches));
  }

  /**
   * @param AbstractSource $source
   *
   * @return SourceItem[]
   */
  protected function getAllBaseInfo(AbstractSource $source)
  {
    $websiteId = $source->getWebsiteId();
    $newSources = $source->getSources();
    $newSources[] = $this->getSourceForWebsiteId($websiteId);
    $newModuleSource = new ModuleSource($websiteId, $newSources);
    return parent::getAllBaseInfo($newModuleSource);
  }

  /**
   * @param string $websiteId
   *
   * @return DynamicSourceItem
   */
  protected function getSourceForWebsiteId($websiteId)
  {
    $baseDirectory = $this->getWebsiteModulesDataDirectory($websiteId);
    $baseUrl = $this->getWebsiteModulesUrl($websiteId);
    return new DynamicSourceItem(
        $baseDirectory,
        $baseUrl,
        DynamicSourceItem::SOURCE_LOCAL,
        false,
        true
    );
  }

  /**
   * validate module directory and return module id
   *
   * @param string $directoryName
   * @param string $pathName
   *
   * @return  string|null
   */
  protected function validateDirectoryAndReturnId($directoryName, $pathName)
  {
    $moduleId = $this->validateLegacyDirectoryAndReturnModuleId($directoryName, $pathName);
    if (!empty($moduleId)) {
      return $moduleId;
    }
    return parent::validateDirectoryAndReturnId($directoryName, $pathName);
  }

  /**
   * validate legacy module directory and return module id
   *
   * @param string $directoryName
   * @param string $pathName
   *
   * @return  string|null
   */
  protected function validateLegacyDirectoryAndReturnModuleId($directoryName, $pathName)
  {
    if (!preg_match($this->legacyModuleMatch, $directoryName, $matches)) {
      return null;
    }
    $moduleId = $matches[0];

    $manifestFile = FS::joinPath($pathName, self::SUBDIR_DATA, self::LEGACY_FILE_MANIFEST);
    if (!file_exists($manifestFile)) {
      return null;
    }

    return $moduleId;
  }

  /**
   * @return string
   */
  protected function getManifestFileName()
  {
    return FS::joinPath(self::SUBDIR_DATA, self::FILE_MANIFEST);
  }

  /**
   * @return \Zend_Validate_Abstract|null
   */
  protected function createIdValidator()
  {
    return new ModuleIdValidator(false);
  }

  /**
   * @param string $method
   * @param string $line
   * @param array  $data
   *
   * @throws CmsException
   */
  protected function throwNotExistsException($method, $line, $data)
  {
    throw new ModuleDoesNotExistsException(102, $method, $line, $data);
  }

  /**
   * @param string $method
   * @param string $line
   * @param array  $data
   *
   * @throws CmsException
   */
  protected function throwGetByIdErrorException($method, $line, $data)
  {
    throw new CmsException(103, $method, $line, $data);
  }

  /**
   * @param string     $websiteId
   * @param DataModule $module
   *
   * @return SourceItem
   */
  protected function createSourceItemForNewModule($websiteId, DataModule $module)
  {
    $moduleId = $module->getId();
    if (empty($moduleId)) {
      $module->setNewGeneratedId();
      $moduleId = $module->getId();
    }
    $baseDirectory = $this->getWebsiteModulesDataDirectory($websiteId);
    $baseUrl = $this->getWebsiteModulesUrl($websiteId);
    return new SourceItem(
        $moduleId,
        FS::joinPath($baseDirectory, $moduleId),
        $baseUrl . '/' . $moduleId,
        SourceItem::SOURCE_LOCAL,
        false,
        true
    );
  }

  /**
   * @return string
   */
  protected static function getCacheSectionName()
  {
    return __CLASS__;
  }
}
