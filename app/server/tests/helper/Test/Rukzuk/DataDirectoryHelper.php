<?php


namespace Test\Rukzuk;

use Seitenbau\Registry as Registry;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;
use Test\Seitenbau\System\Helper as SystemHelper;


class DataDirectoryHelper
{
  public static function resetDataDirectory()
  {
    // create item data structure
    $itemDataDir = Registry::getConfig()->item->data->directory;
    $itemDataRestoreFilepath = Registry::getConfig()->test->item->data->restore->file;

    if (!file_exists($itemDataRestoreFilepath)) {
      throw new \Exception('data restore file not found: '.$itemDataRestoreFilepath);
    }

    DirectoryHelper::removeRecursiv($itemDataDir);
    mkdir($itemDataDir, 0777, true);

    $cmd = sprintf(
      'tar xf %s -C %s',
      $itemDataRestoreFilepath,
      $itemDataDir
    );
    list($error, $output, $exitCode) = SystemHelper::user_proc_exec($cmd);
    if ($exitCode != 0) {
      die("couldn't restore item data (".$itemDataRestoreFilepath.") into folder ".$itemDataDir." (exit code: ".$exitCode.")");
    }
  }
}
