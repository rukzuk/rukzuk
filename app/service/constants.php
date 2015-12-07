<?php
// define mode
defined('CMS_MODE')
    || define('CMS_MODE', 'full');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?
                                  getenv('APPLICATION_ENV') :
                                  'staging'));

// Define path to webroot directory
defined('DOCUMENT_ROOT')
  || define('DOCUMENT_ROOT', realpath($_SERVER['DOCUMENT_ROOT']));

// Define path to application directory
defined('APPLICATION_PATH')
  || define('APPLICATION_PATH', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'/server/application'));

// Define web-path to cms directory
defined('CMS_WEBPATH')
  || define('CMS_WEBPATH', '/cms');

// Define path to cms directory
defined('CMS_PATH')
  || define('CMS_PATH', DOCUMENT_ROOT.str_replace('/', DIRECTORY_SEPARATOR, CMS_WEBPATH));

// Define path to app directory
$pos = stripos($_SERVER['SCRIPT_NAME'], '/app/service/');
$sCurWebRootPath = ( $pos !== false
                      ? substr($_SERVER['SCRIPT_NAME'], 0, ($pos+4))
                      : '/app' );
defined('APP_WEBPATH')
    || define('APP_WEBPATH', ($sCurWebRootPath == '/' ? '' : $sCurWebRootPath) );

// Define path to app directory
defined('APP_PATH')
  || define('APP_PATH', DOCUMENT_ROOT.str_replace('/', DIRECTORY_SEPARATOR,$sCurWebRootPath));

// VARDIR moved from old config.ini
defined('VARDIR')
  || define('VARDIR', CMS_PATH.'/var');
