<?php
// include global constants
require(realpath(dirname(__FILE__)).'/constants.php');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
  realpath(APPLICATION_PATH . '/../library'),
  realpath(APPLICATION_PATH . '/models'),
  realpath(APPLICATION_PATH . '/models/generated'),
  get_include_path(),
)));

/** Files we need */
require_once(APPLICATION_PATH . '/../library/vendor/autoload.php');

/** get config object */
/** @var Closure $appConfigLoader */
$appConfigLoader = require(APPLICATION_PATH . '/configs/config.php');
$appConfig = $appConfigLoader(APPLICATION_ENV, APPLICATION_PATH . '/configs',
  DOCUMENT_ROOT . '/../config.php', DOCUMENT_ROOT . '/../meta.json');

// return config
return $appConfig;
