<?php
/** overwrites for deployed application */
return array(
  'db' => array(
    // mysql
    'host'     => getenv('CMS_MYSQL_HOST')     ? : 'localhost',
    'username' => getenv('CMS_MYSQL_USER')     ? : 'rukzuk',
    'password' => getenv('CMS_MYSQL_PASSWORD') ? : 'rukzuk',
    'port'     => getenv('CMS_MYSQL_PORT')     ? : '3306',
    'dbname'   => getenv('CMS_MYSQL_DB')       ? : 'rukzuk',
    'adapter'  => 'pdo_mysql',
    'charset'  => 'UTF8',
  ),
  'screens' => array(
    'type' => 'phantomjs',
    'phantomjs' => array(
      'command' => '/usr/local/bin/phantomjs',
    ),
  ),
  'webhost'         => getenv('CMS_URL')          ? : 'http://localhost:8080',
  'internalWebhost' => getenv('CMS_URL_INTERNAL') ? : 'http://localhost',
);
