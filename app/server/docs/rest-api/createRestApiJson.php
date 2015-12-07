<?php

try {
  $project_root = realpath(__DIR__.'/../../');
  require_once $project_root . '/vendor/autoload.php';

  $outputPath = realpath(__DIR__.'/json/');

  $projectPaths = array(
    $project_root.'/server/library/Cms/Request/',
    $project_root.'/server/library/Cms/Response/',
    $project_root.'/server/application/controllers/',
  );

  $excludePaths = array();

  // Possible options and their default values.
  $options = array(
    'url' => null,
    'default-base-path' => null,
    'default-api-version' => null,
    'default-swagger-version' => '1.2',
    'version' => false,
    'suffix' => '.{format}',
    'help' => false,
    'debug' => false,
  );

  \Swagger\Logger::getInstance()->log = function ($entry, $type) {
    $type = $type === E_USER_NOTICE ? 'INFO' : 'WARN';
    if ($entry instanceof Exception) {
      $entry = $entry->getMessage();
    }
    echo '[', $type, '] ', $entry, PHP_EOL;
  };
  $swagger = new \Swagger\Swagger($projectPaths, $excludePaths);


  $resourceListOptions = array(
    'output' => 'json',
    'suffix' => $options['suffix'],
    'basePath' => $options['url'],
    'apiVersion' => $options['default-api-version'],
    'swaggerVersion' => $options['default-swagger-version'],
  );
  $resourceOptions = array(
    'output' => 'json',
    'defaultBasePath' => $options['default-base-path'],
    'defaultApiVersion' => $options['default-api-version'],
    'defaultSwaggerVersion' => $options['default-swagger-version'],
  );

  $resourceName = false;
  $output = array();
  foreach ($swagger->getResourceNames() as $resourceName) {
    $json = $swagger->getResource($resourceName, $resourceOptions);
    $resourceName = str_replace(DIRECTORY_SEPARATOR, '-', ltrim($resourceName, DIRECTORY_SEPARATOR));
    $output[$resourceName] = $json;
  }
  if ($output) {
    if (file_exists($outputPath) && !is_dir($outputPath)) {
      throw new RuntimeException(
        sprintf('[%s] is not a directory', $outputPath)
      );
    } else {
      if (!file_exists($outputPath) && !mkdir($outputPath, 0755, true)) {
        throw new RuntimeException(
          sprintf('[%s] is not writeable', $outputPath)
        );
      }
    }

    $filename = $outputPath.'api-docs.json';
    if (file_put_contents($filename, $swagger->getResourceList($resourceListOptions))) {
      echo 'Created ', $filename, PHP_EOL;
    }
    if ($options['url'] == false) {
      $filename = $outputPath.'index.php';
      if (file_exists($filename)) {
        echo 'Skipped ', $filename, PHP_EOL;
      } else {
        file_put_contents($filename, "<?php\nheader('Content-Type: application/json');\nreadfile(__DIR__.'/api-docs.json');");
        echo 'Created ', $filename, PHP_EOL;
      }
    }
    foreach ($output as $name => $json) {
      $name = str_replace(DIRECTORY_SEPARATOR, '-', ltrim($name, DIRECTORY_SEPARATOR));
      $filename = $outputPath.$name.'.json';
      echo 'Created ', $filename, PHP_EOL;
      file_put_contents($filename, $json);
    }
    echo PHP_EOL;
  } else {
    throw new RuntimeException('no valid resources found');
  }
} catch (Exception $e) {
  echo '[ERROR] ', $e->getMessage();
  if ($options['debug']) {
    echo ' in ', $e->getFile(), ' on line ', $e->getLine();
  }
  echo PHP_EOL, PHP_EOL;
  exit(1);
}
