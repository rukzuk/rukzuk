<?php
/**
 * Config Loader
 * Set all required constants and use this file like:
 * $appConfig = require path/to/config.php;
 */


/**
 * Load App Config (should be used only in the file!)
 *
 * @param string  $configPath
 * @param string  $deployConfigFile
 * @param string  $instanceMetaConfigFile
 * @param string  $applicationEnv
 * @param bool    $loadInstanceConfig
 *
 * @throws Exception
 *
 * @return Closure
 */
return function ($applicationEnv, $configPath, $deployConfigFile, $instanceMetaConfigFile) {
  // create config objects
  $appConfigFile = $configPath . '/app.php';
  if (!file_exists($appConfigFile)) {
    throw new \Exception('No config file set');
  }
  /** @noinspection PhpIncludeInspection */
  $appConfig = require $appConfigFile;

  /** env overrides */
  $envConfigFile = $configPath . '/app.' . $applicationEnv . '.php';
  if (file_exists($envConfigFile)) {
    /** @noinspection PhpIncludeInspection */
    $envConfig = require $envConfigFile;
    $appConfig = array_replace_recursive($appConfig, $envConfig);
  }

  /** local overrides */
  $localConfigFile = $configPath . '/local-application.ini';
  if (file_exists($localConfigFile)) {
    $localConfig = new \Zend_Config_Ini($localConfigFile, $applicationEnv);
    $appConfig = array_replace_recursive($appConfig, $localConfig->toArray());
  }

  /** deployed overrides */
  if (!empty($deployConfigFile) && file_exists($deployConfigFile)) {
    /** @noinspection PhpIncludeInspection */
    $deployConfig = require $deployConfigFile;
    $appConfig = array_replace_recursive($appConfig, $deployConfig);
  }

  /** instance config (ordered plan) */
  if (!empty($instanceMetaConfigFile) && file_exists($instanceMetaConfigFile)) {
    $instanceMetaArray = json_decode(file_get_contents($instanceMetaConfigFile), true);

    if (json_last_error() != JSON_ERROR_NONE) {
      error_log(__FILE__ . ':' . __LINE__ . ': failed to decode instance meta file, Error: ' . json_last_error());
    }

    $instanceConfig = array();

    if (isset($instanceMetaArray['quota'])) {
      $instanceConfig['quota'] = $instanceMetaArray['quota'];
    }

    if (isset($instanceMetaArray['owner'])) {
      $instanceConfig['owner'] = $instanceMetaArray['owner'];
    }

    if (isset($instanceMetaArray['users'])) {
      $instanceConfig['users'] = $instanceMetaArray['users'];
    }

    if (isset($instanceMetaArray['services'])) {
      $instanceConfig['services'] = $instanceMetaArray['services'];
    }

    // Publisher
    if (isset($instanceMetaArray['publisher'])) {
      $instanceConfig['publisher']['externalrukzukservice']['tokens'] = $instanceMetaArray['publisher']['tokens'];
      $instanceConfig['publisher']['externalrukzukservice']['liveHostingDomain'] = $instanceMetaArray['publisher']['liveHostingDomain'];
    }

    $appConfig = array_replace_recursive($appConfig, $instanceConfig);
  }

  // remove the config key from the array as it is used
  // as Zend Application options (which tries to load the values as files)
  if (isset($appConfig['config'])) {
    unset($appConfig['config']);
  }
  return $appConfig;
};
