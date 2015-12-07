<?php

function shutdown() {
  $error = error_get_last();
    if ($error['type'] != 0) {
      var_dump($error);
    }
}
register_shutdown_function('shutdown');

// Define application environment
define('APPLICATION_ENV', 'testing');

// Define path to cms directory
$appConfig = require(realpath(dirname(__FILE__) . '/../application/configs/cli-config.php'));

// increase momory limit
ini_set('memory_limit', '512M');

// Create application, bootstrap, and run
$application = new \Zend_Application(APPLICATION_ENV, $appConfig);
$application->bootstrap();
