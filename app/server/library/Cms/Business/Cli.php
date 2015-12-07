<?php
namespace Cms\Business;

use Cms\Business\Cli\SassTheme;
use Cms\Business\Cli\SendStatisticToAnalytics;
use Cms\Business\Cli\SendStatisticToGraphite;
use Cms\Quota;
use Doctrine\DBAL\Migrations\Version;
use Doctrine\ORM\Tools\SchemaTool;
use Seitenbau\Registry;
use \Cms\Access\Manager as AccessManager;
use Seitenbau\FileSystem as FS;

/**
 * Stellt die Business-Logik fuer Cli zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */
class Cli extends Base\Service
{
  const SPACE_DISK_USAGE_ACTION = 'SPACE_DISK_USAGE_ACTION';
  const SPACE_SUBMITTED_LOG = 'SPACE_SUBMITTED_LOG';
  const SPACE_INIT_ACTION = 'SPACE_INIT_ACTION';
  const SPACE_MIGRATED_ACTION = 'SPACE_MIGRATED_ACTION';
  const SPACE_UPDATE_DATA_ACTION = 'SPACE_UPDATE_DATA_ACTION';

  /**
   * @param   string $username
   * @param   string $userpassword
   *
   * @return  boolean
   * @throws  \Cms\Exception (auch bei fehlerhafter Anmeldung)
   */
  public function checkFtpLogin($username, $userpassword)
  {
    $accessManager = AccessManager::singleton();

    $autResult = $accessManager->checkLogin($username, $userpassword);
    if (!$accessManager->isAuthResultValid($autResult)) {
      throw new \Cms\Exception(2006, __METHOD__, __LINE__);
    }

    // only superusers are allowed to login via FTP
    $identity = $autResult->getIdentity();
    if (!is_array($identity) || !isset($identity['superuser']) || $identity['superuser'] != true) {
      throw new \Cms\Exception(2007, __METHOD__, __LINE__);
    }

    // module development must be enabled to login via FTP
    $quota = new Quota();
    if (!$quota->getModuleQuota()->getEnableDev()) {
      throw new \Cms\Exception(2007, __METHOD__, __LINE__);
    }

    return true;
  }

  /**
   * @param   string $username
   * @param   string $userpassword
   *
   * @return  \Cms\Access\Auth\Result
   * @throws  \Cms\Exception (auch bei fehlerhafter Anmeldung)
   */
  public function checkLogin($username, $userpassword)
  {
    $accessManager = AccessManager::singleton();

    $autResult = $accessManager->checkLogin($username, $userpassword);
    if (!$accessManager->isAuthResultValid($autResult)) {
      // Login falsch
      throw new \Cms\Exception(2008, __METHOD__, __LINE__);
    }

    return $autResult->getIdentity();
  }

  /**
   * @return \Datetime
   */
  public function getLastLogin()
  {
    return $this->getUserStatusBusiness()->getLastLogin();
  }

  /**
   * @return array[] Cms\Data\User
   */
  public function getAllSuperusers()
  {
    return $this->getUserBusiness()->getAllSuperusers();
  }

  /**
   * initialize the cms system
   */
  public function initSystem()
  {
    // reset/clear opcode cache
    $this->resetOpCodeCache();

    // create folders
    $this->createDataFileFolders();

    // init DB
    $this->createDbSchema();

    // Log init event
    $weburl = parse_url(Registry::getBaseUrl());
    $space_host = preg_replace('/\.$/', '', $weburl['host']);
    Registry::getActionLogger()->logAction(self::SPACE_INIT_ACTION, array(
      'space' => $space_host,
    ));
  }

  /**
   * Creates the writeable data folders
   */
  protected function createDataFileFolders()
  {
    // create dirs
    $fileDirs = array(
      'data',
      FS::joinPath('data', 'theme'),
      FS::joinPath('data', 'live'),
      'builds',
      'screens_cache',
      'screens',
      'publishing',
      'websites',
      'media',
      'media_cache',
      'var',
      FS::joinPath('var', 'logs'),
      FS::joinPath('var', 'import'),
      FS::joinPath('var', 'export'),
      FS::joinPath('var', 'creator'),
      FS::joinPath('var', 'fts_index'),
      FS::joinPath('var', 'import-latch'),
      FS::joinPath('var', 'session'),
      FS::joinPath('var', 'screenshot'),
      FS::joinPath('var', 'publish'),
    );

    foreach ($fileDirs as $d) {
      FS::createDirIfNotExists(FS::joinPath(CMS_PATH, $d), true, 0750);
    }
  }

  /**
   * Create DB Schema: Empties DB if tables are present
   */
  protected function createDbSchema()
  {
    // doctrine create schema
    $entityManager = Registry::getEntityManager();
    $schemaTool = new SchemaTool($entityManager);
    $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
    $schemaTool->dropSchema($metadata);
    $schemaTool->createSchema($metadata);

    // add migrations
    $migrationConfig = $this->getDoctrineMigrationConfig();
    $availableVersions = $migrationConfig->getAvailableVersions();
    foreach ($availableVersions as $version) {
      $versionObj = $migrationConfig->getVersion($version);
      if (!$versionObj->isMigrated()) {
        $versionObj->markMigrated();
      }
    }
  }

  /**
   * @param array   $userCreateValues
   * @param boolean $sendRegisterMail
   *
   * @return \Cms\Data\User
   */
  public function createUser(array $userCreateValues, $sendRegisterMail)
  {
    $userBusiness = $this->getUserBusiness();
    $user = $userBusiness->create($userCreateValues);
    if ($sendRegisterMail) {
      $userBusiness->register(array($user->getId()), true);
    }
    return $user;
  }

  /**
   * @param $userId
   *
   * @return array
   */
  public function registerUser($userId)
  {
    // init
    $data = array();
    $userBusiness = $this->getBusiness('User');

    // Benutzer ermitteln
    $data['user'] = $userBusiness->getById($userId);

    // Registrier-Token erzeugen
    $tokens = $userBusiness->register(array($userId), false);
    foreach ($tokens as $token) {
      if ($userId === $token->getUserid()) {
        $data['token'] = $token->getCode();
        break;
      }
    }

    // Registrier-Url ermitteln
    $config = Registry::getConfig();
    $data['tokenUrl'] = sprintf(
        "%s/t=%s&u=%s",
        Registry::getBaseUrl(),
        urlencode($data['token']),
        urlencode($data['user']->getEmail())
    );

    // Benutzer und Registrier-Token zurueckgeben
    return $data;
  }

  /**
   * @param string $code
   * @param string $password
   */
  public function optinUser($code, $password)
  {
    /** @var $userBusiness \Cms\Business\User */
    $userBusiness = $this->getBusiness('User');
    $userBusiness->optin($code, $password);
  }

  /**
   * @param string|null $toVersion
   *
   * @return array
   */
  public function updateSystem($toVersion = null)
  {
    // the order is important
    $updateInfo = array();

    // 1. reset/clear opcode cache
    $this->resetOpCodeCache();

    // 2. migrate database
    $updateInfo['db'] = $this->migrateDb($toVersion);

    return $updateInfo;
  }

  /**
   * @return array
   */
  public function updateData()
  {
    // 1. reset/clear opcode cache
    $this->resetOpCodeCache();

    // 2. update all contents (eg. update default formValues)
    $this->updateAllContents();

    // log update data
    Registry::getActionLogger()->logAction(self::SPACE_UPDATE_DATA_ACTION, array());
  }

  /**
   * @param string|null $toVersion
   *
   * @return array
   * @throws \Doctrine\DBAL\Migrations\MigrationException
   */
  protected function migrateDb($toVersion = null)
  {
    // the order is important
    $updateInfo = array();

    // 1. reset/clear opcode cache
    $this->resetOpCodeCache();

    // 2. migrate database
    $configuration = $this->getDoctrineMigrationConfig();
    // Information ermitteln
    $currentVersion = $configuration->getCurrentVersion();
    $latestVersion = $configuration->getLatestVersion();
    if ($toVersion === null) {
      $toVersion = $latestVersion;
    }

    // DB-Migration durchfuehren
    $migration = new \Doctrine\DBAL\Migrations\Migration($configuration);

    $migration->migrate($toVersion, false);

    $versionInfo = array(
      'current' => (string)$currentVersion,
      'latest' => (string)$latestVersion,
      'migrateto' => (string)$toVersion,
    );

    // log migrate
    Registry::getActionLogger()->logAction(self::SPACE_MIGRATED_ACTION, $versionInfo);

    return array(
      'version' => $versionInfo,
    );
  }

  protected function getDoctrineMigrationConfig()
  {
    $config = Registry::getConfig();

    // Doctrine Migration
    $entityManager = Registry::getEntityManager();
    $configuration = new \Doctrine\DBAL\Migrations\Configuration\Configuration($entityManager->getConnection());
    $configuration->setName($config->migration->doctrine->name);
    $configuration->setMigrationsNamespace($config->migration->doctrine->migrations_namespace);
    $configuration->setMigrationsTableName($config->migration->doctrine->table_name);
    $configuration->setMigrationsDirectory($config->migration->doctrine->migrations_directory);
    $configuration->registerMigrationsFromDirectory($configuration->getMigrationsDirectory());

    return $configuration;
  }

  /**
   * Updates the contents of all snippets, templates and pages
   */
  protected function updateAllContents()
  {
    $this->getContentUpdaterBusiness()->updateAllContents();
  }

  /**
   * @return array
   */
  public function garbageCollection()
  {
    return array(
      'deletedWebsites' => array(),
    );
  }

  /**
   * Send Stats (ActionLog and some ad hoc stats) to Segment.io (which calls other APIs)
   */
  public function sendStatisticToAnalyticsServices()
  {
    $sendToAnalytics = new SendStatisticToAnalytics(
        $this->getBusiness('User'),
        $this->getBusiness('ActionLog'),
        $this->getBusiness('Website'),
        $this->getBusiness('Builder'),
        $this->getBusiness('Template')
    );
    $sendToAnalytics->send();
  }

  /**
   * Send Stats (ActionLog) to Graphite
   */
  public function sendStatisticToGraphite()
  {
    $sendToGraphite = new SendStatisticToGraphite(
        $this->getBusiness('ActionLog'),
        $this->getBusiness('Website')
    );
    $sendToGraphite->send();
  }

  /**
   * Removes all Live Websites
   */
  public function removeAllLiveWebsites()
  {
    /**
     * @var \Cms\Business\Website $websiteBusiness
     */
    $websiteBusiness = $this->getBusiness('Website');
    $websites = $websiteBusiness->getAll();

    foreach ($websites as $w) {
      $websiteBusiness->deletePublishedWebsite($w->getId());
    }
  }

  /**
   * clear/reset the opt code cache
   */
  public function resetOpCodeCache()
  {
    if (function_exists('opcache_reset')) {
      opcache_reset();
    }
  }

  /**
   * Build Theme
   * @param array $rawThemeVars
   */
  public function buildTheme($rawThemeVars)
  {
    $this->getSassThemeBuilder()->buildTheme($rawThemeVars);
  }

  /**
   * @return \Cms\Business\User
   */
  protected function getUserBusiness()
  {
    return $this->getBusiness('User');
  }

  /**
   * @return \Cms\Business\UserStatus
   */
  protected function getUserStatusBusiness()
  {
    return $this->getBusiness('UserStatus');
  }

  /**
   * @return \Cms\Business\Group
   */
  protected function getGroupBusiness()
  {
    return $this->getBusiness('Group');
  }

  /**
   * @return \Cms\Business\ContentUpdater
   */
  protected function getContentUpdaterBusiness()
  {
    return $this->getBusiness('ContentUpdater');
  }

  /**
   * @return SassTheme
   */
  protected function getSassThemeBuilder()
  {
    $sassConfig = Registry::getConfig()->theme->sass;
    return new SassTheme($sassConfig->source_path, $sassConfig->target_path);
  }
}
