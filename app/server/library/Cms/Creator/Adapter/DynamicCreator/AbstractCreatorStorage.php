<?php


namespace Cms\Creator\Adapter\DynamicCreator;

use Cms\Creator\CreatorContext;
use Cms\Creator\CreatorStorageInterface;
use Seitenbau\FileSystem as FS;
use Cms\Data\Creator as CreatorData;

abstract class AbstractCreatorStorage implements CreatorStorageInterface
{
  /**
   * @var CreatorContext
   */
  private $creatorContext;
  /**
   * @var string
   */
  private $workingBaseDirectory;
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var SiteStructure
   */
  private $structure;
  /**
   * @var string
   */
  private $creatorFilesDirectory;
  /**
   * @var string
   */
  private $workingDirectory;
  /**
   * @var string
   */
  private $websiteDirectory;
  /**
   * @var string
   */
  private $filesDirectory;
  /**
   * @var array
   */
  private $cacheDirectories = array();
  /**
   * @var array
   */
  private $writableDirectories = array();
  /**
   * @var bool
   */
  private $legacySupport = false;
  /**
   * @var array
   */
  private $templates = array();
  /**
   * @var string
   */
  private $creatorName;
  /**
   * @var string
   */
  private $creatorVersion;

  /**
   * @param CreatorContext $creatorContext
   * @param SiteStructure  $structure
   * @param string         $workingBaseDirectory
   * @param string         $websiteId
   * @param string         $creatorName
   * @param string         $creatorVersion
   */
  public function __construct(
      CreatorContext $creatorContext,
      SiteStructure $structure,
      $workingBaseDirectory,
      $websiteId,
      $creatorName,
      $creatorVersion
  ) {
    $this->creatorContext = $creatorContext;
    $this->workingBaseDirectory = $workingBaseDirectory;
    $this->websiteId = $websiteId;
    $this->structure = $structure;
    $this->creatorFilesDirectory = FS::joinPath(__DIR__, 'Files');
    $this->creatorName = $creatorName;
    $this->creatorVersion = $creatorVersion;
    $this->init();
  }

  /**
   * @return string
   */
  abstract protected function getStorageName();

  /**
   * initializing storage
   */
  protected function init()
  {
    $this->initWorkingDirectory();
    $this->initSystemDirectory();
    $this->initMediaDirectory();
    $this->initOtherDirectory();
    $this->loadTemplates();
  }

  /**
   * Finalizing the creator storage
   */
  public function finalize()
  {
    $this->createMetaFiles();
    $this->createCreatorInfoFile();
  }

  /**
   * @return \Cms\Data\Creator
   */
  public function getCreatorData()
  {
    $creatorData = new CreatorData();
    $creatorData
      ->setName($this->creatorName)
      ->setVersion($this->creatorVersion)
      ->setBaseDirectory($this->getWorkingDirectory())
      ->setMetaSubDirectory($this->getMetaSubDirectory())
      ->setWebsiteSubDirectory($this->getWebsiteSubDirectory())
      ->setInfoFilesSubDirectory($this->getInfoFilesSubDirectory());
    return $creatorData;
  }

  /**
   * activates the legacy support
   */
  public function activateLegacySupport()
  {
    if ($this->legacySupport == false) {
      $this->copyLegacyClasses();
    }
    $this->legacySupport = true;
  }

  /**
   * @return bool
   */
  protected function legacySupportActivated()
  {
    return $this->legacySupport;
  }

  /**
   * @return \Cms\Creator\CreatorContext
   */
  protected function getCreatorContext()
  {
    return $this->creatorContext;
  }

  /**
   * @param $directory
   */
  protected function addCacheDirectory($directory)
  {
    $this->cacheDirectories[] = $directory;
  }

  /**
   * @param $directory
   */
  protected function addWritableDirectory($directory)
  {
    $this->writableDirectories[] = $directory;
  }

  /**
   * initialize and creates the working directory
   *
   * @throws \Exception
   */
  protected function initWorkingDirectory()
  {
    $tmpDirName = sprintf(
        '%s.%s.C%d_%d',
        $this->getWebsiteId(),
        $this->getStorageName(),
        time(),
        rand(100000, 999999)
    );
    $workingDirectory = FS::joinPath($this->workingBaseDirectory, $tmpDirName);
    if (file_exists($workingDirectory)) {
      throw new \Exception('working directory already exists ' . $tmpDirName);
    }
    FS::createDirIfNotExists($workingDirectory);

    $this->workingDirectory = $workingDirectory;
    $this->websiteDirectory = FS::joinPath(
        $this->workingDirectory,
        $this->getWebsiteSubDirectory()
    );
    $this->filesDirectory = FS::joinPath($this->websiteDirectory, 'files');
  }

  /**
   * initialize server directory
   */
  protected function initSystemDirectory()
  {
    // create protected system directory
    $systemDirectory = $this->getSystemDirectory();
    FS::createDirIfNotExists($systemDirectory, true);
    $this->copyDenyFromAllHtaccess($systemDirectory);
    // create page base directory
    $pageDirectory = $this->getPageDataBaseDirectory();
    FS::createDirIfNotExists($pageDirectory, true);
    // create module base directory
    $pageDirectory = $this->getModuleBaseDirectory();
    FS::createDirIfNotExists($pageDirectory, true);
    // create info files directory
    $infoFilesDirectory = $this->getInfoFilesDirectory();
    FS::createDirIfNotExists($infoFilesDirectory, true);
    // initialize server directory
    $this->initServerDirectory();
  }


  /**
   * initialize server directory
   */
  protected function initServerDirectory()
  {
    // create server directory
    $serverDirectory = $this->getServerDirectory();
    FS::createDirIfNotExists($serverDirectory, true);
    // copy Render files
    $srcRenderDirectory = realpath(FS::joinPath(
        APPLICATION_PATH,
        '..',
        'library',
        'Render'
    ));
    $destRenderDirector = FS::joinPath($serverDirectory, 'library', 'Render');
    FS::createDirIfNotExists($destRenderDirector, true);
    FS::copyDir($srcRenderDirectory, $destRenderDirector);
    // copy image manipulation tool
    $this->copyImageTool();
    $this->copyBootstrapFiles();
  }

  protected function copyBootstrapFiles()
  {
    $serverDirectory = $this->getServerDirectory();
    $this->copyCreatorFile($serverDirectory, 'bootstrap.php', 'bootstrap.php');
    $this->copyCreatorFile($serverDirectory, 'constants.php', 'constants.php');
    // TODO: move other Live* classes out of \Render to DynamicCreator\Files and copy them ???!!
  }

  /**
   * copy image manipulation classes
   */
  protected function copyImageTool()
  {
    $serverDirectory = $this->getServerDirectory();
    $srcImageToolDirectory = realpath(FS::joinPath(
        APPLICATION_PATH,
        '..',
        'library',
        'Seitenbau',
        'Image'
    ));
    $destImageToolDirectory = FS::joinPath(
        $serverDirectory,
        'library',
        'Seitenbau',
        'Image'
    );
    FS::createDirIfNotExists($destImageToolDirectory, true);
    FS::copyDir($srcImageToolDirectory, $destImageToolDirectory);

    $srcMimeTypeClassFilePath = realpath(FS::joinPath(
        APPLICATION_PATH,
        '..',
        'library',
        'Seitenbau',
        'Mimetype.php'
    ));
    $destMimeTypeClassFilePath = FS::joinPath(
        $serverDirectory,
        'library',
        'Seitenbau',
        'Mimetype.php'
    );
    FS::copyFile($srcMimeTypeClassFilePath, $destMimeTypeClassFilePath);
  }

  /**
   * initialize media directory
   */
  protected function initMediaDirectory()
  {
    // create media base files directory
    $mediaDirectory = $this->getMediaBaseDirectory();
    FS::createDirIfNotExists($mediaDirectory, true);
    $this->copyCreatorFile($mediaDirectory, 'cdn.php', 'cdn.php');
    // create protected media files directory
    $mediaFilesDirectory = $this->getMediaFilesDirectory();
    FS::createDirIfNotExists($mediaFilesDirectory, true);
    $this->copyDenyFromAllHtaccess($mediaFilesDirectory);
    // create writable media cache directory
    $mediaCacheDirectory = $this->getMediaCacheDirectory();
    FS::createDirIfNotExists($mediaCacheDirectory, true);
    $this->addCacheDirectory($mediaCacheDirectory);
    $this->addWritableDirectory($mediaCacheDirectory);
    // copy icon files
    $this->copyIconFiles();
  }

  /**
   * copy icon files to media directory
   */
  protected function copyIconFiles()
  {
    $mediaDirectory = $this->getMediaBaseDirectory();
    $iconDirectory = FS::joinPath($mediaDirectory, 'icons');
    FS::createDirIfNotExists($iconDirectory, true);
    $this->copyDenyFromAllHtaccess($iconDirectory);
    $srcIconFilesDirectory = $this->getCreatorContext()->getIconFilePath();
    FS::copyDir($srcIconFilesDirectory, $iconDirectory);
  }

  /**
   * initialize other directory (asset, css, ...)
   */
  protected function initOtherDirectory()
  {
    $assetsDirectory = $this->getModuleAssetsBaseDirectory();
    FS::createDirIfNotExists($assetsDirectory, true);
    $cssDirectory = $this->getCssBaseDirectory();
    FS::createDirIfNotExists($cssDirectory, true);
  }

  protected function copyLegacyClasses()
  {
    // copy legacy render files
    $serverDirectory = $this->getServerDirectory();
    FS::createDirIfNotExists($serverDirectory, true);
    $srcRenderDirectory = realpath(FS::joinPath(
        APPLICATION_PATH,
        '..',
        'library',
        'Dual'
    ));
    $destRenderDirector = FS::joinPath($serverDirectory, 'library', 'Dual');
    try {
      FS::createDirIfNotExists($destRenderDirector, true);
      FS::copyDir($srcRenderDirectory, $destRenderDirector);
    } catch (\Exception $e) {
      $this->addError('Error at copy legacy render classes', $e);
    }
  }

  /**
   * create meta information for publisher
   */
  protected function createMetaFiles()
  {
    $this->createWritableListFile();
    $this->createCacheListFile();
  }

  /**
   * create writable list meta file
   */
  protected function createWritableListFile()
  {
    $websiteDirectoryLength = strlen($this->getWebsiteDirectory()) + 1;
    $content = array();
    foreach ($this->writableDirectories as $writableDirectory) {
      $content[] = substr($writableDirectory, $websiteDirectoryLength);
    }
    $filePath = FS::joinPath($this->getMetaDirectory(), 'writeable.txt');
    FS::writeContentToFile(
        $filePath,
        implode("\n", $content),
        "Error at creating file '%s' (%s): %s"
    );
  }

  /**
   * create cache list meta file
   */
  protected function createCacheListFile()
  {
    $websiteDirectoryLength = strlen($this->getWebsiteDirectory()) + 1;
    $content = array();
    foreach ($this->cacheDirectories as $cacheDirectory) {
      $content[] = substr($cacheDirectory, $websiteDirectoryLength);
    }
    $filePath = FS::joinPath($this->getMetaDirectory(), 'cache.txt');
    FS::writeContentToFile(
        $filePath,
        implode("\n", $content),
        "Error at creating file '%s' (%s): %s"
    );
  }

  /**
   * create creator info file
   */
  protected function createCreatorInfoFile()
  {
    $creatorInfo = array(
      'timestamp' => time(),
      'creator' => array(
        'name' => $this->creatorName,
        'version' => $this->creatorVersion,
      ),
      'website' => array(
        'id'  => $this->getWebsiteId(),
      ),
    );
    $filePath = FS::joinPath(
        $this->getInfoFilesDirectory(),
        'creator.json'
    );
    FS::writeContentToFile(
        $filePath,
        json_encode($creatorInfo),
        "Error at creating file '%s' (%s): %s"
    );
  }

  /**
   * @param string $filePath
   * @param array  $data
   * @param array  $comments
   */
  protected function exportDataToFile(
      $filePath,
      array $data,
      array $comments = array()
  ) {
    $content = array("<?php",
      "/**", " * (c) 2014 rukzuk AG",
      " * " . implode("\n * ", $comments), " */",
      "return", $this->exportVar($data), ";"
    );
    FS::writeContentToFile(
        $filePath,
        implode("\n", $content),
        "Error at creating file '%s' (%s): %s"
    );
  }

  /**
   * @param $data
   *
   * @return string
   */
  protected static function exportVar($data)
  {
    return var_export($data, true);
  }

  /**
   * @param $directory
   */
  protected function copyDenyFromAllHtaccess($directory)
  {
    $this->copyCreatorFile($directory, '.htaccess', 'deny_from_all.htaccess');
  }

  /**
   * @param $destDirectory
   * @param $destFileName
   * @param $srcFileName
   */
  protected function copyCreatorFile(
      $destDirectory,
      $destFileName,
      $srcFileName
  ) {
    $srcDirectory = $this->getCreatorFilesDirectory();
    $srcFilePath = FS::joinPath($srcDirectory, $srcFileName);
    $destFilePath = FS::joinPath($destDirectory, $destFileName);
    FS::copyFile($srcFilePath, $destFilePath);
  }

  /**
   * load the template files
   */
  protected function loadTemplates()
  {
    $templateDirectory = $this->getCreatorFilesDirectory();
    $this->templates['page.index.php.tpl'] = file_get_contents(FS::joinPath(
        $templateDirectory,
        'page.index.php.tpl'
    ));
    $this->templates['page.css.php.tpl'] = file_get_contents(FS::joinPath(
        $templateDirectory,
        'page.css.php.tpl'
    ));
  }

  /**
   * @param string $destFilePath
   * @param string $templateName
   * @param array  $data
   */
  protected function createFileFromTemplate(
      $destFilePath,
      $templateName,
      array $data
  ) {
    if (isset($this->templates[$templateName])) {
      $fileContent = $this->templates[$templateName];
    } else {
      $fileContent = '';
    }
    foreach ($data as $dataKey => $dataValue) {
      $fileContent = str_replace(
          '{{' . strtoupper($dataKey) . '}}',
          $dataValue,
          $fileContent
      );
    }
    FS::writeContentToFile($destFilePath, $fileContent);
  }

  /**
   * @param $pageId
   *
   * @throws \Exception
   * @return string
   */
  protected function getPageStructureFilePath($pageId)
  {
    $pageUrl = $this->structure->getPageUrl($pageId);
    if (is_null($pageUrl)) {
      throw new \Exception('Page structure file path for "'.$pageId.'" not found');
    }
    $pageUrl = str_replace('/', DIRECTORY_SEPARATOR, $pageUrl);
    return FS::joinPath($this->getWebsiteDirectory(), $pageUrl);
  }

  /**
   * @param $pageId
   *
   * @return string
   */
  protected function getInstallationPathForPage($pageId)
  {
    $depth = $this->structure->getPageDepth($pageId);
    if ($depth > 0) {
      $relDir = str_repeat(' . \'..\' . DIRECTORY_SEPARATOR', $depth);
    } else {
      $relDir = '';
    }
    return '__DIR__ . DIRECTORY_SEPARATOR' . $relDir;
  }

  /**
   * @param      $pageId
   * @param bool $forceFileName
   *
   * @return null|string
   */
  protected function getPageUrl($pageId, $forceFileName = true)
  {
    return $this->structure->getPageUrl($pageId, $forceFileName);
  }

  /**
   * @return string
   */
  protected function getWorkingDirectory()
  {
    return $this->workingDirectory;
  }

  /**
   * @return string
   */
  protected function getWebsiteDirectory()
  {
    return $this->websiteDirectory;
  }

  /**
   * @return string
   */
  protected function getMetaDirectory()
  {
    return FS::joinPath(
        $this->getWorkingDirectory(),
        $this->getMetaSubDirectory()
    );
  }

  /**
   * @return string
   */
  public function getFilesDirectory()
  {
    return $this->filesDirectory;
  }

  /**
   * @return string
   */
  protected function getSystemDirectory()
  {
    return FS::joinPath($this->getFilesDirectory(), 'system');
  }

  /**
   * @return string
   */
  protected function getServerDirectory()
  {
    return FS::joinPath($this->getSystemDirectory(), 'server');
  }

  /**
   * @return string
   */
  protected function getDataDirectory()
  {
    return FS::joinPath($this->getSystemDirectory(), 'data');
  }

  /**
   * @return string
   */
  protected function getModuleBaseDirectory()
  {
    return FS::joinPath($this->getDataDirectory(), 'modules');
  }

  /**
   * @return string
   */
  protected function getPageDataBaseDirectory()
  {
    return FS::joinPath($this->getDataDirectory(), 'pages');
  }

  /**
   * @param $pageId
   *
   * @return string
   */
  protected function getPageDataDirectory($pageId)
  {
    return FS::joinPath($this->getPageDataBaseDirectory(), $pageId);
  }

  /**
   * @return string
   */
  protected function getCssBaseDirectory()
  {
    return FS::joinPath($this->getFilesDirectory(), 'css');
  }

  /**
   * @param $pageId
   *
   * @return string
   */
  protected function getPageCssFileName($pageId)
  {
    return md5($pageId) . '.css';
  }

  /**
   * @return string
   */
  protected function getModuleAssetsBaseDirectory()
  {
    return FS::joinPath($this->getFilesDirectory(), 'assets', 'modules');
  }

  /**
   * @return string
   */
  protected function getMediaBaseDirectory()
  {
    return FS::joinPath($this->getFilesDirectory(), 'media');
  }

  /**
   * @return string
   */
  protected function getMediaFilesDirectory()
  {
    return FS::joinPath($this->getMediaBaseDirectory(), 'files');
  }

  /**
   * @return string
   */
  protected function getMediaCacheDirectory()
  {
    return FS::joinPath($this->getMediaFilesDirectory(), 'cache');
  }

  /**
   * @return string
   */
  protected function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @return string
   */
  protected function getCreatorFilesDirectory()
  {
    return $this->creatorFilesDirectory;
  }

  /**
   * @param string          $error
   * @param null|\Exception $e
   */
  protected function addError($error, $e = null)
  {
    // TODO: error handling
  }

  /**
   * @return string
   */
  private function getWebsiteSubDirectory()
  {
    return 'website';
  }

  /**
   * @return string
   */
  private function getMetaSubDirectory()
  {
    return '';
  }

  /**
   * @return string
   */
  private function getInfoFilesSubDirectory()
  {
    return FS::joinPath(
        $this->getWebsiteSubDirectory(),
        'files',
        'system',
        'info'
    );
  }

  /**
   * @return string
   */
  protected function getInfoFilesDirectory()
  {
    return FS::joinPath(
        $this->getWorkingDirectory(),
        $this->getInfoFilesSubDirectory()
    );
  }
}
