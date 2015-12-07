<?php
/**
 * @copyright   Copyright &copy; 2014, rukzuk AG
 */

// timezone
date_default_timezone_set('Europe/Berlin');

// CMS stated
define('CMS_STARTED', true);

// set static
define('IS_STATIC_SITE', true);

// set environment
define('APPLICATION_ENV', 'live');

// get document root
if (substr($_SERVER['DOCUMENT_ROOT'], -1, 1) == DIRECTORY_SEPARATOR) {
  $docRoot = substr($_SERVER['DOCUMENT_ROOT'], 0, -1);
} else {
  $docRoot = $_SERVER['DOCUMENT_ROOT'];
}
define('DOCROOT_PATH', realpath($docRoot));

// get website path
define('SITE_PATH', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'));

// get website subpath (url)
if (!defined('SITE_WEBPATH')) {
  $subPathPart = substr(realpath(dirname($_SERVER['SCRIPT_FILENAME'])), strlen(SITE_PATH));
  if ($subPathPart) {
    $installationPath = substr(dirname($_SERVER['SCRIPT_NAME']), 0, -strlen($subPathPart));
  } else {
    $installationPath = dirname($_SERVER['SCRIPT_NAME']);
  }
  $installationPath = str_replace(DIRECTORY_SEPARATOR, '/', $installationPath);
  $installationPath = str_replace('//', '/', $installationPath);
  if ($installationPath == '/') {
    $installationPath = '';
  }
  define('SITE_WEBPATH', $installationPath);
}

// get files path
define('FILES_PATH', SITE_PATH . DIRECTORY_SEPARATOR . 'files');

// get data path
define('SYSTEM_PATH', FILES_PATH . DIRECTORY_SEPARATOR . 'system');

// get server path
define('SERVER_PATH', SYSTEM_PATH . DIRECTORY_SEPARATOR . 'server');

// get data path
define('DATA_PATH', SYSTEM_PATH . DIRECTORY_SEPARATOR . 'data');

// get module data path
define('MODULES_DATA_PATH', DATA_PATH . DIRECTORY_SEPARATOR . 'modules');

// get page data path
define('PAGES_DATA_PATH', DATA_PATH . DIRECTORY_SEPARATOR . 'pages');

// get application path
define('APPLICATION_PATH', SERVER_PATH . DIRECTORY_SEPARATOR . 'application');

// get library path
define('LIBRARY_PATH', SERVER_PATH . DIRECTORY_SEPARATOR . 'library');

// get media path
define('MEDIA_PATH', FILES_PATH . DIRECTORY_SEPARATOR . 'media');

// get media items path
define('MEDIA_FILES_PATH', MEDIA_PATH . DIRECTORY_SEPARATOR . 'files');

// get media cache path (this directory must have write permission)
define('MEDIA_CACHE_PATH', MEDIA_FILES_PATH . DIRECTORY_SEPARATOR . 'cache');

// get media items path
define('ICON_FILES_PATH', MEDIA_PATH . DIRECTORY_SEPARATOR . 'icons');

// get asset path
define('ASSET_PATH', FILES_PATH . DIRECTORY_SEPARATOR . 'assets');

// get asset subpath (url)
define('ASSET_WEBPATH', SITE_WEBPATH . '/files/assets');

// css path (url)
define('CSS_WEBPATH', SITE_WEBPATH . '/files/css');

// media path (url)
define('MEDIA_WEBPATH', SITE_WEBPATH . '/files/media');
