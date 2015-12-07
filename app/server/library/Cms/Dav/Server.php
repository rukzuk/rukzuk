<?php
namespace Cms\Dav;

use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Seitenbau\Log as SbLog;
use Cms\Access\Manager as AccessManager;

/**
 * @package Cms\Dav
 */
class Server
{
  /**
   * @var null|string
   */
  private $baseUri;

  /**
   * @var string
   */
  private $rootDirectory;

  /**
   * @var string
   */
  private $lockFilePath;

  /**
   * @var \Sabre\DAV\Server
   */
  private $davServer;

  /**
   * @param string|null $baseUri
   */
  public function __construct($baseUri = null)
  {
    $davConfig = Registry::getConfig()->dav;
    $this->baseUri = $baseUri;
    $this->rootDirectory = $davConfig->directory;

    $tmpDirectory = $davConfig->temp_dir;
    FS::createDirIfNotExists($tmpDirectory);
    $this->lockFilePath = FS::joinPath($tmpDirectory, 'davlocks');

    $this->initServer();
  }

  /**
   *  Starts the DAV Server
   */
  public function exec()
  {
    $this->davServer->exec();
  }

  /**
   * @param string $username
   * @param string $password
   *
   * @return bool
   */
  public function authCallback($username, $password)
  {
    try {
      $accessManager = AccessManager::singleton();
      $authResult = $accessManager->checkLogin($username, $password);

      // module development must be enabled to login via WebDav
      $quota = new \Cms\Quota();
      if (!$quota->getModuleQuota()->getEnableDev()) {
        Registry::getLogger()->log(
            __METHOD__,
            __LINE__,
            sprintf('DAV access denied: module development is disabled (%s)', $username),
            SbLog::ERR
        );
        return false;
      }

      // login success?
      if (!$accessManager->isAuthResultValid($authResult)) {
        Registry::getLogger()->log(
            __METHOD__,
            __LINE__,
            sprintf('DAV access denied: incorrect user credentials (%s)', $username),
            SbLog::NOTICE
        );
        return false;
      }

      // only superusers are allowed to login via webdav
      $identity = $authResult->getIdentity();
      if (!is_array($identity) || !isset($identity['superuser']) || $identity['superuser'] != true) {
        Registry::getLogger()->log(
            __METHOD__,
            __LINE__,
            sprintf('DAV access denied: user is not a superuser (%s)', $username),
            SbLog::ERR
        );
        return false;
      }
    } catch (\Exception $e) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $e, SbLog::ERR);
      return false;
    }

    // authentication successful
    return true;
  }

  /**
   * init the dav server
   */
  protected function initServer()
  {
    // create server
    $root = new \Sabre\DAV\FS\Directory($this->rootDirectory);
    $this->davServer = new \Sabre\DAV\Server($root);
    if (isset($this->baseUri)) {
      $this->davServer->setBaseUri($this->baseUri);
    }

    // Authentication backend
    $authBackend = new \Sabre\DAV\Auth\Backend\BasicCallBack(array($this, 'authCallback'));
    $auth = new \Sabre\DAV\Auth\Plugin($authBackend, 'Development Area');
    $this->davServer->addPlugin($auth);

    // Support for LOCK and UNLOCK
    $lockBackend = new \Sabre\DAV\Locks\Backend\File($this->lockFilePath);
    $lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);
    $this->davServer->addPlugin($lockPlugin);

    // Support for html frontend
    $browser = new \Sabre\DAV\Browser\Plugin();
    $this->davServer->addPlugin($browser);

    // Automatically guess (some) contenttypes, based on extension
    $this->davServer->addPlugin(new \Sabre\DAV\Browser\GuessContentType());

    // Support for mounting
    $this->davServer->addPlugin(new \Sabre\DAV\Mount\Plugin());
  }
}
