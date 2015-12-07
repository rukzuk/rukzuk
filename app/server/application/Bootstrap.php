<?php
use Seitenbau\Registry as Registry;
use Seitenbau\Logger as Logger;
use Seitenbau\Logger\Action as ActionLogger;
use Seitenbau\Locale as SbLocale;
use Doctrine\Common\ClassLoader as DoctrineClassLoader;
use Doctrine\ORM\Configuration as DoctrineConfiguration;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\Common\Cache\ArrayCache as DoctrineArrayCache;
use Doctrine\Common\Cache\ApcCache as DoctrineApcCache;
use Cms\Controller\Response\Http as HttpResponse;
use Cms\Controller\Request\HttpCompressed as HttpRequestCompressed;
use Cms\Access\Manager as AccessManager;

/**
 * Klasse zum bootstrapen der Anwendung
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
  public function __construct($application)
  {
    $this->setPluginLoader(new Zend_Loader_PluginLoader(array(
      'Zend_Application_Resource'  => 'Zend/Application/Resource')));

    parent::__construct($application);
  }

  protected function _bootstrap($resource = null)
  {
    try {
      $this->original_bootstrap($resource);
    } catch (Exception $e) {
      parent::_bootstrap('frontController');
      $front = $this->getResource('frontController');
      $front->registerPlugin(new \Cms\Controller\Plugin\BootstrapError($e));
    }
  }

  protected function _initAutoloader()
  {
    if (APPLICATION_ENV === 'testing') {
      $autoloader = Zend_Loader_Autoloader::getInstance();
      $autoloader->suppressNotFoundWarnings(true);
    }
  }

  protected function _initConfig()
  {
    $configOptions = $this->getOptions();

    $config = new Zend_Config($configOptions, true);

    Registry::setConfig($config);

    return $config;
  }

  protected function _initLogger()
  {
    $this->bootstrap('config');
    $config = Registry::getConfig();

    $zendLogger = new Zend_Log();
    $this->addFileWriterLogger($zendLogger, $config->logging->file);
    $this->addSyslogWriterLogger($zendLogger, $config->logging->syslog);

    $logger = new Logger($zendLogger);
    $logger->setLevel((int) $config->logging->level);
    Registry::setLogger($logger);

    return $logger;
  }

  protected function _initActionLogger()
  {
    $this->bootstrap('config');
    $config = Registry::getConfig();

    $zendLogger = new Zend_Log();
    $this->addDbWriterToLogger($zendLogger, $config->action->logging->db);
    $this->addSyslogWriterLogger($zendLogger, $config->action->logging->syslog);
    
    $actionLogger = $this->getNewActionLogger($zendLogger);
    $actionLogger->setLevel(Zend_Log::DEBUG);
    Registry::setActionLogger($actionLogger);

    return $actionLogger;
  }

  protected function _initDb()
  {
    $dbConfig = $this->getOption('db');

    $registeredDbAdapter = Registry::getDbAdapter();

    if ($registeredDbAdapter === null) {
      $db = $this->createDbAdapter($dbConfig);
      Registry::setDbAdapter($db);
    } else {
      $db = $registeredDbAdapter;
    }
    return $db;
  }

  protected function _initDoctrine()
  {
    $appDoctrineConfig = $this->getOption('doctrine');

    $classLoader = new DoctrineClassLoader('Doctrine');
    $classLoader->setIncludePath(realpath(APPLICATION_PATH . '/../library'));
    $classLoader->register();

    $config = new DoctrineConfiguration();

    if (isset($appDoctrineConfig['cache'])) {
      switch ($appDoctrineConfig['cache'])
      {
        case 'array':
            $cache = new DoctrineArrayCache();
              break;

        case 'apc':
            $cache = new DoctrineApcCache();
              break;
      }
      $config->setQueryCacheImpl($cache);
      $config->setMetadataCacheImpl($cache);
    }

    $proxyDir = ( empty($appDoctrineConfig['proxyDir'])
                    ? APPLICATION_PATH . '/../library/Orm/Proxies'
                    : $appDoctrineConfig['proxyDir'] );

    $config->setProxyDir(realpath($proxyDir));
    $config->setProxyNamespace('Orm\Proxies');
    $config->setAutoGenerateProxyClasses(false);
    $config->setEntityNamespaces(array('Orm\Entity'));

    $config->setMetadataDriverImpl(
        new \Doctrine\ORM\Mapping\Driver\StaticPHPDriver(
            realpath(APPLICATION_PATH . '/../library/Orm/Entity')
        )
    );

    //$config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
    $config->setSQLLogger(null);

    $db = $this->getResource('db');
    $dbConfig = $db->getConfig();
    $connectionOptions = array(
      'pdo' => $db->getConnection(),
      'dbname' => $dbConfig['dbname'],
    );

    $entityManager = DoctrineEntityManager::create($connectionOptions, $config);
    $entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

    Registry::setEntityManager($entityManager);
    return $entityManager;
  }

  protected function _initDao()
  {
    return $this->config->dao->connection;
  }

  protected function _initAuth()
  {
    $this->bootstrap('router');
    $this->bootstrap('frontController');

    $router = $this->getResource('router');
    $controller = Zend_Controller_Front::getInstance();
    $controller->setRouter($router);
    
    $access = AccessManager::singleton();
    $access->init($this->getResource('frontController'), \Zend_Auth::getInstance());
  }
  
  protected function _initDefaultTimezone()
  {
    $this->bootstrap('config');
    date_default_timezone_set('Europe/Berlin');
  }

  protected function _initLocale()
  {
    $this->bootstrap('config');
    $this->bootstrap('frontController');

    $locale = new SbLocale(Registry::getConfig()->translation->default);
    Registry::setLocale($locale);

    $this->getResource('frontController')
         ->registerPlugin(
             new \Cms\Controller\Plugin\LocaleSetup()
         );

    return $locale;
  }

  protected function _initTranslator()
  {
    $this->bootstrap('locale');
    $locale = $this->getResource('locale');
    $translationConfig = Registry::getConfig()->translation;
    
    if (isset($translationConfig->route)) {
      $route = $translationConfig->route->toArray();
    } else {
      $route = array();
    }
    $defaultLocale = Registry::getLocale();
    foreach (array_keys($route) as $routeLangFrom) {
      if (strtolower($routeLangFrom) == strtolower($defaultLocale->toString())
          || strtolower($routeLangFrom) == strtolower($defaultLocale->getLanguage())
      ) {
        unset($route[$routeLangFrom]);
      }
    }

    $translate = new \Zend_Translate(array(
      'adapter'         => 'Zend_Translate_Adapter_Array',
      'content'         => $translationConfig->directory,
      'scan'            => Zend_Translate::LOCALE_DIRECTORY,
      'locale'          => $locale,
      'disableNotices'  => (APPLICATION_ENV === 'testing' ? false : true),
      'route'           => $route,
    ));
    
    Registry::set('Zend_Translate', $translate);
    
  }

  protected function _initModifiedFrontController()
  {
    $this->bootstrap('frontController');
    $front = $this->getResource('FrontController');

    // Http-Response-Objekt mit Datei-Streaming setzen
    $response = new HttpResponse();
    $front->setResponse($response);
    
    // Http-Request-Objekt mit Client zu Server Komprimierung
    $request = new HttpRequestCompressed();
    $request->decompressRequest();
    $front->setRequest($request);
  }
  
  protected function _initCliRouter()
  {
    $this->bootstrap('frontcontroller');
    $front = $this->getResource('frontcontroller');
    $router = $front->getRouter();
    
    // Speziellen Router fuer die Console setzen
    if (defined('CMS_ISCLI') && CMS_ISCLI === true) {
      $front->setRouter(new Cms\Controller\Router\Cli());
      $front->setRequest(new Zend_Controller_Request_Simple());
      $router = $front->getRouter();
    }

    return $router;
  }
  
  protected function getNewActionLogger(\Zend_Log $logger)
  {
    return new ActionLogger($logger);
  }

  protected function addFileWriterLogger(Zend_Log $logger, $config)
  {
    if (!(isset($config->active) && $config->active == 1)) {
      return;
    }

    if (!@touch($config->logfile)) {
      // do not log if we can't write the file
      return;
    }

    $fileLogWriter = new Zend_Log_Writer_Stream($config->logfile);
    if (isset($config->format)) {
      $fileLogWriter->setFormatter(new Zend_Log_Formatter_Simple($config->format . PHP_EOL));
    }
    if (isset($config->level)) {
      $fileLogWriter->addFilter((int) $config->level);
    }
    $logger->addWriter($fileLogWriter);
  }
  
  protected function addSyslogWriterLogger(Zend_Log $logger, $config)
  {
    if (!(isset($config->active) && $config->active == 1)) {
      return;
    }
    // use always same application and facility, because php error will be written with same application and facility
    $syslogWriter = new \Zend_Log_Writer_Syslog(array(
      'application' => 'cms@'.$this->getDomain(),
      'facility'    => LOG_LOCAL6,
    ));
    if (isset($config->format)) {
      $syslogWriter->setFormatter(new Zend_Log_Formatter_Simple($config->format));
    }
    if (isset($config->level)) {
      $syslogWriter->addFilter((int) $config->level);
    }
    $logger->addWriter($syslogWriter);
  }

  protected function addDbWriterToLogger(Zend_Log $logger, $config)
  {
    if (!(isset($config->active) && $config->active == 1)) {
      return;
    }

    $this->bootstrap('db');
    $dbLogWriter = new Zend_Log_Writer_Db(
        Registry::getDbAdapter(),
        $config->table_name,
        array(
        'websiteid' => 'websiteid',
        'id' => 'id',
        'name' => 'name',
        'additionalinfo' => 'additionalinfo',
        'timestamp' => 'timestamp',
        'userlogin' => 'userlogin',
        'action' => 'message',
        )
    );
    if (isset($config->level)) {
      $dbLogWriter->addFilter((int) $config->level);
    }
    $logger->addWriter($dbLogWriter);
  }

  protected function getDomain()
  {
    $config = $this->bootstrap('config');
    $webUrl = parse_url(Registry::getBaseUrl());
    return $webUrl['host'];
  }

  /**
   * @param $resource
   */
  protected function original_bootstrap($resource)
  {
    parent::_bootstrap($resource);
  }

  /**
   * @param array $dbConfig
   *
   * @return Zend_Db_Adapter_Abstract
   */
  protected function createDbAdapter(array $dbConfig)
  {
    return Zend_Db::factory($dbConfig['adapter'], $dbConfig);
  }
}
