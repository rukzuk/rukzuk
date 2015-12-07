<?php
/**
 * @copyright   Copyright &copy; 2014, rukzuk AG
 */

/**
 * include global constants (paths and such)
 */
require(__DIR__ . '/constants.php');

/**
 * autoloader
 * NOTE: the default is not working because it is
 *        fundamentally broken, see: https://bugs.php.net/bug.php?id=53065
 */
spl_autoload_register(
    function ($pClassName) {
      if (0 === strpos($pClassName, 'Render\\') || 0 === strpos($pClassName, 'Seitenbau\\')
      || 0 === strpos($pClassName, 'Dual\\')) {
        $pClassName = str_replace("\\", DIRECTORY_SEPARATOR, $pClassName);
        include_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . $pClassName . '.php');
      }
    }
);

/**
 * including additional definitions
 */
$additionalDefinitionFile = SITE_PATH . DIRECTORY_SEPARATOR . '.cmsConfig.php';
if (\file_exists($additionalDefinitionFile) && is_readable($additionalDefinitionFile)) {
  require_once($additionalDefinitionFile);
}
