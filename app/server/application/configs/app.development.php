<?php
/** overwrites for APPLICATION_ENV development */
return array_replace_recursive(include __DIR__ . '/app.staging.php', array(
  'phpSettings' => array(
    'display_startup_errors' => 1,
    'display_errors' => 1,
    'xdebug' => array(
      'max_nesting_level' => 200,
    ),
  ),
  'resources' => array(
    'frontController' => array(
      'params' => array(
        'displayExceptions' => 1,
      ),
    ),
  ),
  'logging' => array(
    'file' => array(
      'active' => 1,
      'level' => 7,
    ),
    'syslog' => array(
      'active' => 0,
    ),
  ),
  'stats' => array(
    'segmentio' => array(
      'enabled' => 0,
    ),
    'graphite' => array(
      'enabled' => 0,
      'host' => 'localhost',
      'port' => 2003,
      'prefix' => 'space',
      'bucket' => 'actionlog',
    ),
  ),
));
