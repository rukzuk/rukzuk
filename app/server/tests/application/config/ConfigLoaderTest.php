<?php


namespace application\config;


use Test\Rukzuk\AbstractTestCase;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;

class ConfigLoaderTest extends AbstractTestCase
{
  /**
   * @test
   * @group config
   * @group small
   *
   * @dataProvider test_configLoadReturnAllMergedConfigAsExpectedProvider
   */
  public function test_configLoadReturnAllMergedConfigAsExpected($applicationEnv, $configPath,
                                                                 $deployConfigFile, $instanceMetaConfigFile,
                                                                 $expectedConfig)
  {
    // ARRANGE
    /** @var $appConfigLoader \Closure */
    $appConfigLoader = require APPLICATION_PATH . '/configs/config.php';

    // ACT
    $actualConfig = $appConfigLoader($applicationEnv, $configPath, $deployConfigFile, $instanceMetaConfigFile);

    // ASSERT
    $this->assertEquals($expectedConfig, $actualConfig);
  }

  /**
   * @return array
   */
  public function test_configLoadReturnAllMergedConfigAsExpectedProvider()
  {
    $testConfigPath = Registry::getConfig()->test->configs->storage->directory;
    $configPath_allConfigs = FS::joinPath($testConfigPath, 'all_configs');
    $configPath_withLocalConfig = FS::joinPath($testConfigPath, 'with_local-application-ini');
    return array(
      // only base configs (app.php)
      array(
        'not_exists_env', $configPath_allConfigs, null, null,
        array(
          'configLoaded' => array(
            'app_php' => true,
          ),
          'valueFrom' => 'app_php',
          'overwritten' => array(
            'from' => 'app_php',
          ),
        ),
      ),
      array(
        // production config overwrite base configs (app.php)
        'production',
        $configPath_allConfigs,
        null,
        null,
        array(
          'configLoaded' => array(
            'app_php' => true,
            'app_production_php' => true,
          ),
          'valueFrom' => 'app_production_php',
          'overwritten' => array(
            'from' => 'app_production_php',
          ),
        ),
      ),
      array(
        // production config overwrite base configs (app.php)
        // deploy config overwrite production configs
        'production',
        $configPath_allConfigs,
        FS::joinPath($configPath_allConfigs, 'config.php'),
        null,
        array(
          'configLoaded' => array(
            'app_php' => true,
            'app_production_php' => true,
            'config_php' => true,
          ),
          'valueFrom' => 'config_php',
          'overwritten' => array(
            'from' => 'config_php',
          ),
        ),
      ),
      array(
        // production config overwrite base configs (app.php)
        // deploy config overwrite production configs
        // meta json put some other values into config
        'production',
        $configPath_allConfigs,
        FS::joinPath($configPath_allConfigs, 'config.php'),
        FS::joinPath($configPath_allConfigs, 'meta.json'),
        array(
          'configLoaded' => array(
            'app_php' => true,
            'app_production_php' => true,
            'config_php' => true,
          ),
          'valueFrom' => 'config_php',
          'overwritten' => array(
            'from' => 'config_php',
          ),
          // set from meta.json
          'quota' => 'quota from meta.json',
          'owner' => 'owner from meta.json',
          'services' => array(
            'someConfig' => 'services.someConfig from meta.json',
          ),
          'publisher' => array(
            'externalrukzukservice' => array(
              'tokens' => 'publisher.tokens from meta.json',
              'liveHostingDomain' => 'publisher.liveHostingDomain from meta.json',
            )
          ),
        ),
      ),
      // staging config overwrite base configs (app.php)
      array(
        'staging',
        $configPath_allConfigs,
        null,
        null,
        array(
          'configLoaded' => array(
            'app_php' => true,
            'app_staging_php' => true,
          ),
          'valueFrom' => 'app_staging_php',
          'overwritten' => array(
            'from' => 'app_staging_php',
          ),
        ),
      ),
      // env "development" loading also "staging" config
      // development config overwrite all configs
      array(
        'development',
        $configPath_allConfigs,
        null,
        null,
        array(
          'configLoaded' => array(
            'app_php' => true,
            'app_staging_php' => true,
            'app_development_php' => true,
          ),
          'valueFrom' => 'app_development_php',
          'overwritten' => array(
            'from' => 'app_development_php',
          ),
        ),
      ),
      // env "development" loading also "staging" config
      // deploy config overwrite all configs
      array(
        'development',
        $configPath_allConfigs,
        FS::joinPath($configPath_allConfigs, 'config.php'),
        null,
        array(
          'configLoaded' => array(
            'app_php' => true,
            'app_staging_php' => true,
            'app_development_php' => true,
            'config_php' => true,
          ),
          'valueFrom' => 'config_php',
          'overwritten' => array(
            'from' => 'config_php',
          ),
        )
      ),
      // env "development" loading also "staging" config
      // local config overwrite all configs
      array(
        'development',
        $configPath_withLocalConfig,
        null,
        null,
        array(
          'configLoaded' => array(
            'app_php' => true,
            'app_staging_php' => true,
            'app_development_php' => true,
            'local-application_ini-production' => 1,
            'local-application_ini-staging' => 2,
            'local-application_ini-development' => 3,
          ),
          'valueFrom' => 'local-application_ini:development',
          'overwritten' => array(
            'from' => 'local-application_ini:development',
          ),
        ),
      ),
    );
  }
}
 