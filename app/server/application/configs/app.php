<?php
/** base for all other configs */
return array(
  'phpSettings' => array(
    'display_startup_errors' => 0,
    'display_errors' => 0,
  ),
  'includePaths' => array(
    'library' => APPLICATION_PATH . '/../library',
  ),
  'bootstrap' => array(
    'path' => APPLICATION_PATH . '/Bootstrap.php',
    'class' => 'Bootstrap',
  ),
  'appnamespace' => 'Application',
  'autoloaderNamespaces' => array(
    'Doctrine',
    'Seitenbau',
    'Cms',
    'Orm',
    'Dual',
    'Render',
    'Base',
    'Test',
    'Symfony',
  ),
  'resources' => array(
    'frontController' => array(
      'controllerDirectory' => APPLICATION_PATH . '/controllers',
      'params' => array(
        'displayExceptions' => 0,
      ),
    ),
    'router' => array(
      'routes' => array(
        'cms' => array(
          'route' => '/:controller/:action/*',
          'defaults' => array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'index',
          ),
        ),
        'shortener' => array(
          'route' => '/s/:ticket/*',
          'defaults' => array(
            'module' => 'default',
            'controller' => 'shortener',
            'action' => 'ticket',
          ),
        ),
        'index' => array(
          'route' => 'index.html',
          'defaults' => array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'index',
          ),
        ),
        'browsercache' => array(
          'type' => 'Zend_Controller_Router_Route',
          'route' => '/:browsercache/app/service',
          'reqs' => array(
            'browsercache' => '^(v-[a-zA-Z0-9]+)?$',
          ),
        ),
        'cacheCms' => array(
          'type' => 'Zend_Controller_Router_Route_Chain',
          'chain' => 'browsercache, cms',
        ),
        'cacheShortener' => array(
          'type' => 'Zend_Controller_Router_Route_Chain',
          'chain' => 'browsercache, shortener',
        ),
      ),
    ),
    'session' => array(
      'save_path' => VARDIR . '/session',
      'use_only_cookies' => 1,
      'cookie_lifetime' => 0,
      'gc_maxlifetime' => 43200,
      'cookie_httponly' => 1,
    ),
  ),
  'client' => array(
    'path' => APPLICATION_PATH . '/../../../app/index.html',
    'login' => array(
      'path' => APPLICATION_PATH . '/../../../app/login.html',
    ),
    'template' => array(
      'login' => APPLICATION_PATH . '/../../../app/backend_tpl.php',
      'error' => APPLICATION_PATH . '/../../../app/backend_tpl.php',
    ),
    'api' => array(
      'file' => APPLICATION_PATH . '/../../../app/js/CMS/api/ApiAdapter.js',
      'url' => APP_WEBPATH . '/js/CMS/api/ApiAdapter.js',
    ),
  ),
  'theme' => array(
    'sass' => array(
      'source_path' => APPLICATION_PATH . '/../../../app/sass',
      'target_path' => CMS_PATH . '/data/theme',
    )
  ),
  'webhost' => '',
  'server' => array(
    'webpath' => APP_WEBPATH,
    'url' => APP_WEBPATH . '/service',
  ),
  'translation' => array(
    'directory' => APPLICATION_PATH . '/locale/translations',
    'default' => 'en',
    'route' => array(
      'de' => 'en',
    ),
    'languages' => array('en', 'de'),
  ),
  'quota' => array(
    'website' => array(
      'maxCount' => 1000,
    ),
    'webhosting' => array(
      'maxCount' => 1000,
    ),
    'exportAllowed' => true,
    'module' => array(
      'enableDev' => true,
    ),
    'media' => array(
      'maxFileSize' => 52428800,
      'maxSizePerWebsite' => 7516192768,
    ),
    'expired' => false,
  ),
  'uuid' => array(
    'limit' => 100000,
  ),
  'logging' => array(
    'level' => 7,
    'file' => array(
      'active' => 1,
      'level' => 4,
      'format' => '%timestamp%|%sessionId%|%logid%|%priorityName%|%method%.%line%|%message%|%data%',
      'logfile' => VARDIR . '/logs/cms.log',
    ),
    'syslog' => array(
      'active' => 1,
      'level' => 6,
      'format' => 'cms|%sessionId%|%logid%|%method%.%line%|%message%|%data%',
    ),
  ),
  'indexing' => array(
    'indexer' => 'Lucene',
    'basedir' => VARDIR . '/fts_index',
    'enabled' => '',
    'store' => array(
      'content' => 1,
    ),
    'accessticket' => array(
      'ticketLifetime' => 5,
      'sessionLifetime' => 10,
      'remainingCalls' => 1,
      'authentication' => 1,
    ),
  ),
  'action' => array(
    'logging' => array(
      'db' => array(
        'active' => 1,
        'level' => 6,
        'lifetime' => 180,
        'table_name' => 'action_log',
      ),
      'syslog' => array(
        'active' => 1,
        'level' => 6,
        'format' => 'action|%action%|%userlogin%|%websiteid%|%id%|%name%|%additionalinfo%',
      ),
    ),
  ),
  'stats' => array(
    'segmentio' => array(
      'enabled' => 0,
      'api_secret' => null, // see app.APPLICATION_ENV.php
      'api_options' => array(
        'consumer' => 'socket',
        'debug' => true,
        'ssl' => true,
        'timeout' => 1,
      ),
      'action_blacklist_regex' => '/(.+_EDIT_.*)|SPACE_DISK_USAGE_ACTION/',
      'info_white_list' =>
        array('id', 'email', 'name', 'language', 'localId'),
    ),
  ),
  'service' => array(
    'Modul' => array(
      'dao' => 'Module',
    ),
  ),
  'dao' => array(
    'connection' => 'Doctrine',
    'Module' => array(
      'connection' => 'Filesystem',
    ),
    'User' => array(
      'connection' => 'All',
    ),
    'TemplateSnippet' => array(
      'connection' => 'All',
    ),
    'Package' => array(
      'connection' => 'Filesystem',
    ),
    'WebsiteSettings' => array(
      'connection' => 'All',
    ),
    'PageType' => array(
      'connection' => 'Filesystem',
    ),
  ),
  'db' => array(
    'dbname' => 'rukzuk',
    'charset' => 'UTF8',
  ),
  'doctrine' => array(
    'proxyDir' => APPLICATION_PATH . '/../library/Orm/Proxies',
    'mapping' => array(
      'driver' => 'Php',
    ),
  ),
  'migration' => array(
    'doctrine' => array(
      'name' => 'Doctrine Migrations',
      'migrations_namespace' => 'Orm\\Migrations',
      'table_name' => 'migrations',
      'migrations_directory' => APPLICATION_PATH . '/../library/Orm/Migrations/',
    ),
  ),
  'request' => array(
    'parameter' => 'params',
  ),
  'lock' => array(
    'check' => array(
      'activ' => 1,
    ),
    'lifetime' => 300,
    'gc_maxlifetime' => 86400,
  ),
  'group' => array(
    'check' => array(
      'activ' => 1,
    ),
    'rights' => array(
      'modules' => array('all', 'none'),
      'templates' => array('all', 'none'),
      'pages' => array('edit', 'subAll', 'subEdit', 'none'),
      'website' => array('publish', 'none'),
      'readlog' => array('all', 'none'),
      'colorscheme' => array('all', 'none'),
    ),
  ),
  'item' => array(
    'data' => array(
      'directory' => CMS_PATH . '/data',
      'webpath' => CMS_WEBPATH . '/data',
    ),
    'repository' => array(
      'module' => array(
        'enabled' => true,
        'directory' => CMS_PATH . '/../../modules',
        'url'  => '/modules',
        'new_website_repo' => 'rukzuk'
      )
    ),
    'sets' => array(
      'enabled' => true,
      'directory' => APPLICATION_PATH . '/../../../app/sets',
      'url'  => '/app/sets',
      'default_set_id' => 'rukzuk'
    ),
  ),
  'pageType' => array(
    'defaultPageType' => array(
      'id' => 'page',
      'directory' => APPLICATION_PATH . '/default/pageType',
      'url' => '/default/pageType',
    )
  ),
  'media' => array(
    'files' => array(
      'directory' => CMS_PATH . '/media',
      'webpath' => CMS_WEBPATH . '/media',
    ),
    'cache' => array(
      'directory' => CMS_PATH . '/media_cache',
    ),
    'icon' => array(
      'maxWidth' => 100,
      'maxHeight' => 100,
    ),
  ),
  'export' => array(
    'directory' => VARDIR . '/export',
  ),
  'images' => array(
    'directory' => APPLICATION_PATH . '/../images',
  ),
  'import' => array(
    'directory' => VARDIR . '/import',
    'latch' => array(
      'directory' => VARDIR . '/import-latch',
      'gc_maxlifetime' => 1800,
    ),
    'default' => array(
      'album' => array(
        'name' => 'rukzuk Import',
      ),
    ),
    'delete' => array(
      'after' => array(
        'import' => 1,
      ),
    ),
    'allowed' => array(
      'types' => 'zip,rukzuk',
    ),
    'url' => array(
      'enabled' => 1,
      'request' => array(
        'timeout' => 10,
        'max_redirects' => 30,
      ),
      'allowed_urls' => array(
        '/https?:\\/\\/(?:[^\\/]+\\.)?rukzuk\\.com[\\/?]/i',
        '/https?:\\/\\/(?:[^\\/]+\\.)?rukzuk\\.io[\\/?]/i',
        '/https?:\\/\\/(?:[^\\/]+\\.)?rukzuk\\.net[\\/?]/i',
        '/https?:\\/\\/(?:[^\\/]+\\.)?rukzuk\\.intern[\\/?]/i',
        '/https?:\\/\\/(?:[^\\/]+\\.)?seitenbau\\.net[\\/?]/i',
      ),
    ),
    'local_files' => array(
      'directory' => APPLICATION_PATH . '/../../../app/exports',
    )
  ),
  'file' => array(
    'types' => array(
      'image' => 'gif,png,jpg,svg',
      'download' => 'pdf,xls,doc,ppt,css',
      'multimedia' => 'mp3,wav,mov,mp4,flv',
      'icon' => array(
        'directory' => APPLICATION_PATH . '/../images/icons/filetypes',
      ),
    ),
  ),
  'screens' => array(
    'activ' => 1,
    'url' => '/cdn/getscreen',
    'type' => 'externalrukzukservice',
    'filetype' => 'jpg',
    'directory' => CMS_PATH . '/screens',
    'cache' => array(
      'directory' => CMS_PATH . '/screens_cache',
    ),
    'thumbnail' => array(
      'width' => 256,
      'height' => 192,
    ),
    'screenWidth' => 1024,
    'screenHeight' => 768,
    'accessticket' => array(
      'ticketLifetime' => 960,
      'sessionLifetime' => 10,
      'remainingCalls' => 1,
      'authentication' => 1,
    ),
    'externalrukzukservice' => array(
      'hosts' => array(), // see app.APPLICATION_ENV.php
      'maxHosts' => 2,
      'endpoint' => array(
        'status' => array(
          'url' => '/pageshooter/status/',
          'timeout' => 5,
          'maxRedirects' => 2,
        ),
        'trigger' => array(
          'url' => '/pageshooter/shoot/',
          'timeout' => 10,
          'maxRedirects' => 2,
        ),
      ),
      'options' => array(
        'trigger' => array(
          'expires' => 900,
          'screenwidth' => 1024,
          'screenheight' => 768,
          'wait' => 1,
        ),
      ),
    ),
    'phantomjs' => array(
      'command' => '/usr/local/bin/phantomjs',
      'output' => VARDIR . '/logs/phantomjs.log',
      'params' => array(
        'ignore-ssl-errors' => 'yes',
        'ssl-protocol' => 'any',
      ),
    ),
  ),
  'accessticket' => array(
    'activ' => 1,
    'url' => '/s/:ticket/:params',
    'ticketLifetime' => 5,
    'remainingCalls' => 1,
  ),
  'builds' => array(
    'directory' => CMS_PATH . '/builds',
    'threshold' => 5,
    'tmp' => array(
      'directory' => CMS_PATH . '/builds/tmp',
    ),
  ),
  'creator' => array(
    'defaultCreator' => 'dynamic',
    'directory' => VARDIR . '/creator',
    'accessticket' => array(
      'ticketLifetime' => 60,
      'sessionLifetime' => 60,
      'remainingCalls' => 1,
      'authentication' => 1,
    ),
    'dynamic' => array(
      'pageCreator' => array(
        'timeout' => 30,
        'maxRetryLimit' => 3,
      )
    )
  ),
  'publisher' => array(
    'type' => 'externalrukzukservice',
    'data' => array(
      'directory' => CMS_PATH . '/publishing',
      'webpath' => CMS_WEBPATH . '/publishing',
    ),
    'defaultPublish' => array(
      'type' => 'internal',
      'config' => array(
        'internal' => array(
          'cname' => '',
        ),
        'external' => array(
          'chmod' => array(
            'default' => '',
            'writeable' => '0777',
          ),
        ),
      ),
    ),
    'standalone' => array(
      'liveHostingDirectory' =>  CMS_PATH . '/data/live',
      'liveHostingWebPath' => CMS_WEBPATH . '/data/live',
      'tempDirectory' => VARDIR . '/publish',
    ),
    'externalrukzukservice' => array(
      'hosts' => array(), // see app.APPLICATION_ENV.php
      'maxHosts' => 2,
      'endpoint' => array(
        'publish' => array(
          'url' => '/publisher/add/',
          'timeout' => 60,
          'maxRedirects' => 2,
        ),
        'status' => array(
          'url' => '/publisher/status/',
          'timeout' => 20,
          'maxRedirects' => 2,
        ),
        'delete' => array(
          'url' => '/publisher/delete/',
          'timeout' => 20,
          'maxRedirects' => 2,
        ),
      ),
      'tokens' => array(),
      'liveHostingDomain' => '{{id}}.example.com',
      'liveHostingDomainProtocol' => 'http://',
    ),
  ),
  'mail' => array(
    'transport' => 'sendmail',
    'template' => array(
      'directory' => APPLICATION_PATH . '/locale/emails',
    ),
  ),
  'feedback' => array(
    'activ' => 1,
    'adapter' => 'mail',
    'file' => array(
      'path' => VARDIR . '/feedback',
    ),
    'mail' => array(
      'subject' => '[SBCMS] Feedback:',
      'adress' => 'help@rukzuk.com',
    ),
  ),
  'optin' => array(
    'code' => array(
      'length' => 11,
    ),
    'lifetime' => array(
      'register' => 14,
      'password' => 2,
    ),
  ),
  'user' => array(
    'password' => array(
      'min' => 6,
      'max' => 255,
    ),
    'mail' => array(
      'activ' => 1,
      'optin' => array(
        'from' => array(
          'address' => 'help@rukzuk.com',
          'name' => 'rukzuk Web Design',
        ),
        'uri' => '/',
      ),
      'renew' => array(
        'password' => array(
          'from' => array(
            'address' => 'help@rukzuk.com',
            'name' => 'rukzuk Web Design',
          ),
          'uri' => '/',
        ),
      ),
    ),
  ),
  'services' => array(
    'linkResolver' => null,
    'dashboardUrl' => null,
  ),
  'acl' => array(
    'render_as_guest' => false,
  ),
  'dav' => array(
    'directory' => CMS_PATH . '/data',
    'temp_dir' => VARDIR . '/tmp',
  ),
);
