<?php
/** overwrites for APPLICATION_ENV testing */
return array(
  'bootstrap' => array(
    'path' => TEST_PATH . '/BootstrapTest.php',
    'class' => 'BootstrapTest',
  ),
  'phpSettings' => array(
    'display_startup_errors' => 1,
    'display_errors' => 1,
    'xdebug' => array(
      'max_nesting_level' => 200,
    ),
  ),
  'db' => array(
    'dbname' => (@getenv('CMS_TEST_DB_DBNAME')) ?: ':memory:',
    'host' => (@getenv('CMS_TEST_DB_HOST')) ?: '',
    'username' => (@getenv('CMS_TEST_DB_USER')) ?: '',
    'password' => (@getenv('CMS_TEST_DB_PW')) ?: '',
    'port' => (@getenv('CMS_TEST_DB_PORT')) ?: '',
    'adapter' => (@getenv('CMS_TEST_DB_ADAPTER')) ?: 'pdo_sqlite',
  ),
  'webhost' => (@getenv('CMS_TEST_WEBHOST')) ?: 'http://localhost',
  'theme' => array(
    'sass' => array(
      'target_path' => TEST_PATH . '/_output/theme',
    )
  ),
  'test' => array(
    'cms_webpath' => CMS_WEBPATH,
    'directory' => TEST_PATH,
    'files' => array(
      'directory' => TEST_PATH . '/_files',
    ),
    'fixtures' => array(
      'directory' => TEST_PATH . '/_fixtures',
    ),
    'response' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_files/responses',
      ),
      'render' => array(
        'directory' => TEST_PATH . '/_files/render',
      ),
      'creator' => array(
        'directory' => TEST_PATH . '/_files/creator',
      ),
    ),
    'request' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_files/requests',
      ),
    ),
    'sql' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_sqls',
      ),
    ),
    'json' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_files/json',
      ),
    ),
    'builds' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_files/builds',
      ),
    ),
    'publisher' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_files/publisher',
      ),
    ),
    'configs' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_files/configs',
      ),
    ),
    'output' => array(
      'response' => array(
        'render' => array(
          'directory' => TEST_PATH . '/_output/render',
        ),
        'creator' => array(
          'directory' => TEST_PATH . '/_output/creator',
        ),
      ),
      'feedback' => array(
        'directory' => TEST_PATH . '/_files/feedback',
      ),
      'screenshot' => array(
        'directory' => TEST_PATH . '/_output/screenshot',
      ),
      'imageprocessing' => array(
        'directory' => TEST_PATH . '/_output/imageprocessing',
      ),
    ),
    'item' => array(
      'data' => array(
        'restore' => array(
          'file' => TEST_PATH . '/_files/data/data_restore.tar.gz',
        ),
      ),
    ),
    'templatesnippet' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_files/json/TemplateSnippets',
      ),
    ),
    'package' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_repository/sets',
      ),
    ),
    'renderer' => array(
      'websiteinfostorage' => array(
        'directory' => TEST_PATH . '/_files/infoStorage/websiteInfoStorage',
      ),
    ),
    'reparser' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_files/json/Reparser',
      ),
    ),
    'contentupdater' => array(
      'storage' => array(
        'directory' => TEST_PATH . '/_files/json/ContentUpdater',
      ),
    ),
  ),
  'resources' => array(
    'router' => array(
      'routes' => array(
        'default' => array(
          'route' => '/:controller/:action/:params/:params/*',
          'defaults' => array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'index',
            'params' => '',
          ),
        ),
      ),
    ),
  ),
  'publisher' => array(
    'externalrukzukservice' => array(
      'hosts' => array('https://services.rukzuk.net'),
      'tokens' => array(
        'internal' => 'THIS_IS_THE_PUBLISHER_TEST_TOKEN_FOR_TYPE_INTERNAL',
        'external' => 'THIS_IS_THE_PUBLISHER_TEST_TOKEN_FOR_TYPE_EXTERNAL',
      )
    ),
    'standalone' => array(
      'liveHostingDirectory' =>  TEST_PATH . '/_output/publisher/standalone/live',
      'liveHostingWebPath' => '/WEB/PATH/TO/PUBLISHED/SITE',
      'tempDirectory' => TEST_PATH . '/_output/publisher/standalone/temp',
    ),
  ),
  'builds' => array(
    'directory' => TEST_PATH . '/_builds',
    'threshold' => 3,
  ),
  'creator' => array(
    'directory' => TEST_PATH . '/_creator/websites',
  ),
  'mail' => array(
    'transport' => 'file',
  ),
  'user' => array(
    'mail' => array(
      'optin' => array(
        'from' => array(
          'address' => 'foo@seitenbau.com',
          'name' => 'foo',
        ),
        'subject' => 'optin-subject',
        'uri' => '/',
      ),
      'renew' => array(
        'password' => array(
          'from' => array(
            'address' => 'foo@seitenbau.com',
            'name' => 'foo',
          ),
          'subject' => 'password-retrieval-subject',
          'uri' => '/',
        ),
      ),
    ),
  ),
  'logging' => array(
    'file' => array(
      'active' => 1,
      'level' => 7,
      'logfile' => CMS_PATH . '/var/logs/cms-test.log',
    ),
    'syslog' => array(
      'active' => 0,
    ),
  ),
  'indexing' => array(
    'basedir' => TEST_PATH . '/_files/fts_index',
    'enabled' => '',
    'testing' => array(
      'basedir' => TEST_PATH . '/_files/index',
    ),
    'store' => array(
      'content' => 1,
    ),
  ),
  'action' => array(
    'logging' => array(
      'db' => array(
        'active' => 1,
        'lifetime' => 1,
        'level' => 6,
      ),
      'syslog' => array(
        'active' => 0,
      ),
    ),
  ),
  'uuid' => array(
    'limit' => 50,
  ),
  'group' => array(
    'check' => array(
      'activ' => 0,
    ),
  ),
  'item' => array(
    'data' => array(
      'directory' => TEST_PATH . '/_data',
    ),
    'repository' => array(
      'module' => array(
        'enabled' => false,
        'directory' => TEST_PATH . '/_repository/modules',
        'url'  => '/URL/TO/MODULE/REPOSITORY',
      )
    ),
    'sets' => array(
      'enabled' => true,
      'directory' => TEST_PATH . '/_sets',
      'url'  => '/URL/TO/SETS',
      'default_set_id' => 'rukzuk_test',
    ),
  ),
  'export' => array(
    'directory' => TEST_PATH . '/_exports',
  ),
  'import' => array(
    'directory' => TEST_PATH . '/_imports',
    'latch' => array(
      'files' => array(
        'directory' => TEST_PATH . '/_files/import_latches',
      ),
      'directory' => TEST_PATH . '/_imports-latch',
    ),
    'local_files' => array(
      'directory' => TEST_PATH . '/_files/test_imports',
    )
  ),
  'media' => array(
    'files' => array(
      'directory' => TEST_PATH . '/_files/media',
    ),
    'cache' => array(
      'directory' => TEST_PATH . '/_files/media_cache',
    ),
  ),
  'screens' => array(
    'activ' => 'no',
    'type' => 'Systemcallwkhtmltoimage',
    'directory' => TEST_PATH . '/_files/screens',
    'cache' => array(
      'directory' => TEST_PATH . '/_files/screens_cache',
    ),
    'thumbnail' => array(
      'width' => 256,
      'height' => 192,
    ),
    'screenWidth' => 1024,
    'screenHeight' => 768,
    'externalrukzukservice' => array(
      'hosts' => array(
        0 => 'https://services.rukzuk.net',
      ),
    ),
    'systemcallwkhtmltoimage' => array(
      'command' => '/usr/local/bin/wkhtmltoimage',
      'output' => VARDIR . '/screenshot/output.log',
      'wait' => array(
        'response' => 1,
      ),
      'options' => array(
        'quality' => 100,
        'load-error-handling' => 'ignore',
      ),
      'check' => array(
        'files' => array(
          'equal' => 0,
        ),
      ),
      'wait' => array(
        'response' => 1,
      ),
    ),
    'wkhtmltoimage' => array(
      'options' => array(
        'screenWidth' => 1024,
        'screenHeight' => 768,
        'quality' => 100,
      ),
    ),
    'phpimagegrabwindow' => array(
      'application' => 'InternetExplorer.Application',
    ),

  ),
  'feedback' => array(
    'file' => array(
      'path' => TEST_PATH . '/_output/feedback',
    ),
    'date' => '01.01.2011',
  ),
  'stats' => array(
    'segmentio' => array(
      'enabled' => '',
      'api_secret' => 'api.secret4segment.io',
      'api_options' => array(
        'consumer' => 'file',
        'filename' => TEST_PATH . '/_output/segmentio/analytics.log',
      ),
    ),
  ),
  // Quota Settings for testing
  'quota' => array(
    'website' => array(
      'maxCount' => 99999999,
    ),
    'webhosting' => array(
      'maxCount' => 99999999,
    ),
    'exportAllowed' => true,
    'module' => array(
      'enableDev' => true,
    ),
  ),
);
