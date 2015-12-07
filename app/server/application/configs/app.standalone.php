<?php
/** overwrites for APPLICATION_ENV standalone */
return array(
  'screens' => array(
    'type' => 'phantomjs',
    'phantomjs' => array(
      'command' => '/usr/bin/phantomjs',
    ),
  ),
  'publisher' => array(
    'type' => 'standalone',
  ),
  'stats' => array(
    'segmentio' => array(
      'enabled' => 0,
      'api_secret' => '',
    ),
  ),
);
