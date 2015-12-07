<?php
/**
 * API Stubs Generator
 * Generates Interfaces from API Classes
 *
 * To use call before: php composer.phar install
 */
//
// config
//

// folder config
$project_root = __DIR__.'/../';
$lib_path = $project_root.'server/library/';
$stubs_path = isset($argv[1]) ? $argv[1] : $project_root.'docs/stubs/';

// Full class names (including namespaces)
$api_classes = array(
  // API Classes
  'Render\APIs\APIv1\HeadAPI',
  'Render\APIs\APIv1\CSSAPI',
  'Render\APIs\APIv1\RenderAPI',

  // Root API Classes
  'Render\APIs\RootAPIv1\RootCssAPI',
  'Render\APIs\RootAPIv1\RootRenderAPI',

  // API subtypes
  'Render\APIs\APIv1\MediaIcon',
  'Render\APIs\APIv1\MediaImage',
  'Render\APIs\APIv1\MediaItem',
  'Render\APIs\APIv1\Navigation',
  'Render\APIs\APIv1\Page',

  // base types
  'Render\ModuleInterface',

  // parameter types
  'Render\Unit',
  'Render\ModuleInfo',

  // Exceptions
  'Render\APIs\APIv1\MediaException',
  'Render\APIs\APIv1\MediaImageInvalidException',
  'Render\APIs\APIv1\MediaItemNotFoundException',
);

// define interface inheritance (could be done via reflection + convention (each class will become a interface))
$api_inheritance = array();
/*
$api_inheritance = array(
  'Render\APIs\APIv1\CSSAPI' => '\Render\APIs\APIv1\HeadAPI',
  'Render\APIs\APIv1\RenderAPI' => '\Render\APIs\APIv1\CSSAPI',
  'Render\APIs\RootAPIv1\RootRenderAPI' => '\Render\APIs\APIv1\RenderAPI',
  'Render\APIs\RootAPIv1\RootCssAPI' => '\Render\APIs\APIv1\CSSAPI',
);
*/

//
// setup rukzuk backend auto loader
//

set_include_path(implode(PATH_SEPARATOR, array(
  realpath($lib_path),
  get_include_path(),
)));

require_once $lib_path . 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace(array("Doctrine", "Seitenbau", "Cms", "Orm", "Dual", "Render", "Base", "Test", "Symfony"));

//
// load InterfaceDistiller (installed via composer)
//

require_once $project_root . 'vendor/autoload.php';
$distiller = new \com\github\gooh\InterfaceDistiller\InterfaceDistiller();

function createStub($full_class_name, $path, $extends_full_name = null) {
  global $distiller;

  $class_name = end(explode("\\", $full_class_name));
  $file_path = $path . '/' . $class_name . '.php';

  $distiller->reset();

  if ($extends_full_name) {
    /**
     * TODO: convert full name to: 'use <NS>;'
     * @see \com\github\gooh\InterfaceDistiller\Distillate\Writer::writeInterfaceSignature
     */
    $distiller->extendInterfaceFrom($extends_full_name);
  }

  $distiller
    ->methodsWithModifiers(\ReflectionMethod::IS_PUBLIC)
//    ->excludeInheritedMethods()
    ->excludeMagicMethods()
    ->excludeOldStyleConstructors()
    ->saveAs(new SplFileObject($file_path, 'w+'))
    ->distill($full_class_name, $full_class_name);
}

// main
if(!is_dir($stubs_path)) {
  mkdir($stubs_path, 0777, true);
}

foreach($api_classes as $api_class) {
  $extends = null;
  if (isset($api_inheritance[$api_class])) {
    $extends = $api_inheritance[$api_class];
  }
  createStub($api_class, $stubs_path, $extends);
}
