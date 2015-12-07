<?php
/** overwrites for APPLICATION_ENV development */
return array_replace_recursive(include __DIR__ . '/app.staging.php', array(
  'configLoaded' => array(
    'app_development_php' => true,
  ),
  'valueFrom' => 'app_development_php',
  'overwritten' => array('from' =>'app_development_php'),
));
