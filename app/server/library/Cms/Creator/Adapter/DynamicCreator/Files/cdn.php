<?php
/**
 * @copyright   Copyright &copy; 2014, rukzuk AG
 */
namespace Render;

if (isset($_GET['__debug'])) {
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
}

$installPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
require_once($installPath . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'server' . DIRECTORY_SEPARATOR . 'bootstrap.php');
$httpRequest = new \Render\RequestHelper\SimpleHttpRequest();
$mediaCdn = new \Render\LiveMediaCdn($httpRequest);
$mediaCdn->output();
