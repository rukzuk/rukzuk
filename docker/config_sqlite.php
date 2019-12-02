<?php
/** overwrites for deployed application */
return array(
  'db' => array(
    'dbname' => getenv('CMS_SQLITE_DB'),
    'adapter' => 'pdo_sqlite'
  ),
  'screens' => array(
    'type' => 'phantomjs',
    'phantomjs' => array(
      'command' => '/usr/local/bin/phantomjs',
    ),
  ),
  'webhost' => getenv('CMS_URL') ? : 'http://localhost:8080',
  'internalWebhost' => getenv('CMS_URL_INTERNAL') ? : 'http://localhost',
);

