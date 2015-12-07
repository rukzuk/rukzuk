<?php
namespace Test\Seitenbau\Directory;

use Seitenbau\Registry as Registry;

class Helper {

  public static function clearLatchDirectory()
  {
    $config = Registry::getConfig();
    $importLatchDirectory = $config->import->latch->directory;
    
    self::removeRecursiv($importLatchDirectory, TEST_PATH);

    $latchedStorageJsonFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . \Cms\Business\Import\Latch::IMPORT_LATCH_STORAGE_FILE;
    if (file_exists($latchedStorageJsonFile)) {
      unlink($latchedStorageJsonFile);
    }    
  }
  /**
   * @param string $directory 
   */
  public static function getRecursiveTree($directory) {    
    $treeIterator = new \RecursiveTreeIterator(
      new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
    );
    $treeIterator->setPrefixPart(
      \RecursiveTreeIterator::PREFIX_MID_HAS_NEXT, '|  '
    );
    $treeIterator->setPrefixPart(
      \RecursiveTreeIterator::PREFIX_END_HAS_NEXT, '|--'
    );
    $treeIterator->setPrefixPart(
      \RecursiveTreeIterator::PREFIX_END_LAST, '`--'
    );
    $files = array();
    foreach ($treeIterator as $file) {              
      $foo = explode(DIRECTORY_SEPARATOR, $file);
      $file = array_shift($foo) . ' ' . array_pop($foo);
      $files[] = $file;
    }
    
    return \implode(PHP_EOL, $files);
  }
  /**
   * @param string $directory 
   */
  public static function getRecursive($directory) {    
    $recursiveIterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );
    $r = array();
    foreach ($recursiveIterator as $splFileInfo) {
      $path = array(
        'children' => array(
          $splFileInfo->getFilename() => array('type' => $splFileInfo->getType())
      ));
      for ($depth = $recursiveIterator->getDepth() - 1; $depth >= 0; $depth--) {
        $path = array(
          'children' => array(
            $recursiveIterator->getSubIterator($depth)->current()->getFilename() => $path
        ));
      }
      $r = array_merge_recursive($r, $path);
    }
    
    self::deep_ksort($r);
    
    return $r;
  }
  /**
   * @param string $directory 
   * @param boolean $prettyPrint
   */
  public static function getRecursiveAsJson($directory, $prettyPrint=false) {
    $json = \Zend_Json::encode(self::getRecursive($directory));
    if ($prettyPrint) {
      return \Zend_Json::prettyPrint($json, array("indent" => "  "));
    }
    return $json;
  }
  /**
   * Entfernt den Pfad von tree (man tree) Aufruf Ergebnissen
   *
   * @param  string
   * @return string
   */
  public static function removePathPartFromTreeResult($result)
  {
    $tree = explode("\n", $result);
    array_shift($tree);
    return implode("\n", $tree);
  }
  
  public static function deep_ksort(&$arr) {
    if (is_array($arr)) {
      ksort($arr);
      array_walk($arr, 'self::deep_ksort');
    }
  }
  
  /**
   * Loescht ein Verzeichnis (Dateien und Unterordner)
   *
   * @param string $dir         Zu loeschendes Verzeichnis
   * @param string $checkDir    Zu loeschendes Verzeichnis muss in diesem Verzeichnis liegen
   */
  public static function removeRecursiv($dir, $checkDir = TEST_PATH)
  {
    $realPath = realpath($dir);
    if (empty($realPath) && !is_link($dir)) {
      return;
    }
    if (strpos($dir, TEST_PATH) !== 0) {
      throw new \Exception('directory ('.$dir.') to remove not within the TEST_PATH ('.TEST_PATH.')');
    }
    if (strpos($dir, $checkDir) !== 0) {
      throw new \Exception('directory ('.$dir.') to remove not within the checkDir ('.$checkDir.')');
    }

    if (is_file($dir) || is_link($dir)) {
      unlink($dir);
      return;
    }

    if (is_dir($dir)) {
      $iterator = new \DirectoryIterator($dir);
      foreach ($iterator as $entry) {
        if (!$entry->isDot() && strpos($entry->getPathname(), $dir) === 0) {
          self::removeRecursiv($entry->getPathname());
        }
      }
      rmdir($dir);
    }
  }

  /**
   * Remove a file
   *
   * @param string $filePath file to remove
   * @param string $checkDir check if file is in this directory
   *
   * @throws \Exception
   */
  public static function removeFile($filePath, $checkDir = TEST_PATH)
  {
    $filePath = realpath($filePath);
    if (!empty($filePath) && !empty($checkDir)) {
      if (!strstr($filePath, $checkDir)) {
        throw new \Exception('file ('.$filePath.') to remove not within the \$checkDir ('.$checkDir.')');
      }
      if (!strstr($filePath, TEST_PATH)) {
        throw new \Exception('file ('.$checkDir.') to remove not within the TEST_PATH ('.TEST_PATH.')');
      }
      unlink($filePath);
    }
  }

  /**
   * Removes all files and directories within the given directory
   *
   * @param string  $directory
   * @param string  $checkDir
   *
   * @throws \Exception
   */
  public static function clearDirectory($directory, $checkDir = TEST_PATH)
  {
    if (empty($directory)) {
      throw new \Exception('no \$directory is given');
    }
    if (empty($checkDir)) {
      throw new \Exception('no \$checkDir is given');
    }

    if (!strstr($directory, $checkDir)) {
      throw new \Exception('directory ('.$directory.') to clear not within the \$checkDir ('.$checkDir.')');
    }
    if (!strstr($directory, TEST_PATH)) {
      throw new \Exception('directory ('.$directory.') to clear not within the TEST_PATH ('.TEST_PATH.')');
    }

    if (is_dir($directory)) {
      foreach (new \DirectoryIterator($directory) as $fileInfo) {
        if($fileInfo->isDot()) continue;
        self::removeRecursiv($fileInfo->getPathname(), $checkDir);
      }
    }
  }


}