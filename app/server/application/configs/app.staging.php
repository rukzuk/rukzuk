<?php
/** overwrites for APPLICATION_ENV staging */
return array(
  'phpSettings' => array(
    'display_startup_errors' => 1,
    'display_errors' => 1,
  ),
  'screens' => array(
    'externalrukzukservice' => array(
      'hosts' => array('https://services.staging.rukzuk.net'),
    ),
  ),
  'publisher' => array(
    'externalrukzukservice' => array(
      'hosts' => array('https://services.staging.rukzuk.net'),
    ),
  ),
  'stats' => array(
    'segmentio' => array(
      'enabled' => 1,
      'api_secret' => '3i6vaxpu4l9l62xy365v',
    ),
  ),
);
