<?php
// init system and get config
$appConfig = require(realpath(dirname(__FILE__)).'/init.php');

// Create application, bootstrap, and run
$application = new \Zend_Application(APPLICATION_ENV, $appConfig);
try
{
  $application->bootstrap()
              ->run();
}
catch (\Exception $e)
{
  $exceptionMessage = 'Internal Server Error: '.$e->getMessage();
  throw new \Exception($exceptionMessage);
}