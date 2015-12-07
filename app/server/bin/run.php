<?php

if (PHP_SAPI !== 'cli')
{
  die("Please only via console!");
}

$cliOptions = getopt("", array("docroot:", "action:", "params::"));

if (isset($cliOptions['docroot']) && $cliOptions['docroot'] !== false) {
  $docroot = realpath($cliOptions['docroot']);
  if (empty($docroot)) {
    die("parameter '--docroot' error: empty value");
  }
  if (!file_exists($docroot)) {
    die("parameter '--docroot=".$docroot."' error: path not exist");
  }
  define('DOCUMENT_ROOT', $docroot);
}else {
  die("missing parameter '--docroot'");
}

if (isset($cliOptions['action']) && $cliOptions['action'] !== false) {
  if (empty($cliOptions['action'])) {
    die("parameter '--action' error: empty value");
  }
} else {
  die("missing parameter '--action'");
}


/* run application */
define('CMS_ISCLI', true);
include __DIR__."/../../service/index.php";
