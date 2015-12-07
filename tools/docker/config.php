<?php
/** overwrites for deployed application */
return array(
  'db' => array(
    'dbname' => getenv('CMS_SQLITE_DB'),
    'adapter' => 'pdo_sqlite'
  ),
  'webhost' => getenv('CMS_URL') ? : 'http://localhost:8080',
  'internalWebhost' => getenv('CMS_URL_INTERNAL') ? : 'http://localhost',
);
