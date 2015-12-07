<?php

// Define application environment
if (!defined('APPLICATION_ENV')) {
  if (getenv('APPLICATION_ENV')) {
    define('APPLICATION_ENV', getenv('APPLICATION_ENV'));
  } else {
    define('APPLICATION_ENV', 'production');
  }
}

// Do some defines
defined('CMS_PATH')
  || define('CMS_PATH', realpath(dirname(__FILE__) . '/../../../../cms/'));

defined('CMS_WEBPATH')
  || define('CMS_WEBPATH', '/cms');

defined('APP_PATH')
|| define('APP_PATH', realpath(dirname(__FILE__) . '/../../../'));

defined('APP_WEBPATH')
|| define('APP_WEBPATH', '/app');

defined('VARDIR')
  || define('VARDIR', CMS_PATH . '/var');

defined('BASE_PATH')
  || define('BASE_PATH', APP_PATH . '/server');

defined('APPLICATION_PATH')
  || define('APPLICATION_PATH', BASE_PATH . '/application');
defined('TEST_PATH')
  || define('TEST_PATH', BASE_PATH . '/tests');
defined('CMS_MODE')
  || define('CMS_MODE', 'full');

defined('DOCUMENT_ROOT')
  || define('DOCUMENT_ROOT', realpath(CMS_PATH . '/../'));

// add needed include paths
set_include_path(implode(PATH_SEPARATOR, array(
  realpath(BASE_PATH . '/library'),
  realpath(TEST_PATH . '/helper'),
  realpath(BASE_PATH . '/application'),
  get_include_path(),
)));

/** Files we need */
require_once(BASE_PATH . '/library/vendor/autoload.php');

/** load config (same as real app does) */
if (getenv('CMS_CONFIGFILE')) {
  // use deployed config file from environment
  $localTestingConfigFile = getenv('CMS_CONFIGFILE');
} else {
  // use deployed testing config file
  $deployConfigFile = DOCUMENT_ROOT . '/../config.'.APPLICATION_ENV.'.php';
  if (!file_exists($deployConfigFile)) {
    // use deployed config file
    $deployConfigFile = DOCUMENT_ROOT . '/../config.php';
  }
}
/** @var Closure $configLoader */
$configLoader = require APPLICATION_PATH . '/configs/config.php';
$appConfig = $configLoader(APPLICATION_ENV, APPLICATION_PATH . '/configs', $deployConfigFile, null);

// return config
return $appConfig;
