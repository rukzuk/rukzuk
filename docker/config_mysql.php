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
  'publisher' => array(
    'type' => 'hybrid', // externalrukzukservice
    'externalrukzukservice' => array(
      'hosts'     => array('http://localhost:8000'),
      'tokens'    => array(
        'internal' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiaW50ZXJuYWwiLCJpbnN0YW5jZSI6Ii4qIiwiZG9tYWluIjoiLioifQ.xVa3b9NRFr2SV2x-nbA1oBwggakP2HWIgVmOcI9idSg',
        'external' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiZXh0ZXJuYWwiLCJpbnN0YW5jZSI6Ii4qIn0.noiBbuBTKS1DxtKbfw4cYJrMdJ7TzCnqCyTxh-sxwBc'
      )
    ),
  ),
  'webhost'         => getenv('CMS_URL')          ? : 'http://localhost:8080',
  'internalWebhost' => getenv('CMS_URL_INTERNAL') ? : 'http://localhost',

  // external links
  'services' => array(
    'linkResolver' => 'https://github.com/rukzuk/rukzuk/wiki',
    'dashboardUrl' => 'https://github.com/rukzuk/rukzuk/wiki',
  ),

  // email settings
  'user' => array(
    'mail' => array(
      'activ' => 1,
      'optin' => array(
        'from' => array(
          'address' => getenv('SMTP_USER') ? : 'noreply@example.com',
          'name' => 'Web Design Platform',
        ),
        'uri' => '/',
      ),
      'renew' => array(
        'password' => array(
          'from' => array(
            'address' => getenv('SMTP_USER') ? : 'noreply@example.com',
            'name' => 'Web Design Platform',
          ),
          'uri' => '/',
        ),
      ),
    ),
  ),


);
