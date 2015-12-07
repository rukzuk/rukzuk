<?php
namespace Seitenbau;

use Seitenbau\FileSystem\FileSystemException as FileSystemException;

/**
 * class to handel filesystem calls
 *
 * @package      Seitenbau
 * @subpackage   FileSystem
 */

class FileSystem
{
  /**
   * @throws  \Seitenbau\FileSystem\FileSystemException
   */
  public static function copyDir($source, $destination)
  {
    if (!is_dir($source)) {
      throw new FileSystemException('Sourcedir "' . $source . '" does not exists');
    }

    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($source),
        \RecursiveIteratorIterator::SELF_FIRST
    );

    if (!self::createDirIfNotExists($destination)) {
      return false;
    }

    // Verzeichnis rekursiv durchlaufen
    while ($iterator->valid()) {
      if (!$iterator->isDot()) {
        if ($iterator->current()->isDir()) {
          // relativen Teil des Pfad auslesen
          $relDir = str_replace($source, '', $iterator->key());

          // Ziel-Verzeichnis erstellen
          if (!self::createDirIfNotExists($destination . $relDir)) {
            return false;
          }
        } elseif ($iterator->current()->isFile()) {
          $destinationFile = $destination . str_replace($source, '', $iterator->key());

          if (!copy($iterator->key(), $destinationFile)) {
            return false;
          }
        }
      }

      $iterator->next();
    }

    return true;
  }

  /**
   * @throws  \Seitenbau\FileSystem\FileSystemException
   */
  public static function copyFile($source, $destination, $errorMessage = null)
  {
    if (!is_file($source)) {
      throw new FileSystemException('Sourcefile "' . $source . '" does not exists');
    }
    if (!copy($source, $destination)) {
      if (empty($errorMessage)) {
        $errorMessage = "error copy file from '%s' to '%s' (%s): %s";
      }
      $errors = error_get_last();
      throw new FileSystemException(sprintf($errorMessage, $source, $destination, $errors['type'], $errors['message']));
    }
  }

  /**
   * @throws  \Seitenbau\FileSystem\FileSystemException
   */
  public static function createDirIfNotExists($directory, $recursive = false, $mode = 0777)
  {
    if (!is_dir($directory)) {
      return mkdir($directory, $mode, $recursive);
    }
    return true;
  }

  /**
   * @throws  \Seitenbau\FileSystem\FileSystemException
   */
  public static function rmdir($dir)
  {
    $realPath = realpath($dir);
    if (empty($realPath)) {
      return;
    }

    if (is_file($dir) || is_link($dir)) {
      unlink($dir);
      return;
    }

    if (is_dir($dir)) {
      $iterator = new \DirectoryIterator($dir);
      foreach ($iterator as $entry) {
        if (!$entry->isDot() && strpos($entry->getPathname(), $dir) === 0) {
          self::rmdir($entry->getPathname());
        }
      }
      rmdir($dir);
    }
  }

  /**
   * @throws  \Seitenbau\FileSystem\FileSystemException
   */
  public static function rmFile($file, $errorMessage = null)
  {
    $file = realpath($file);
    if (!empty($file) && is_file($file)) {
      if (!@unlink($file)) {
        if (empty($errorMessage)) {
          $errorMessage = "error removing file '%s' (%s): %s";
        }
        $errors= error_get_last();
        throw new FileSystemException(sprintf($errorMessage, $file, $errors['type'], $errors['message']));
      }
    }
  }

  /**
   * @throws  \Seitenbau\FileSystem\FileSystemException
   */
  public static function moveDir($source, $destination, $errorMessage = null)
  {
    if (!file_exists($source)) {
      throw new FileSystemException('Source directory "' . $source . '" does not exists');
    }
    if (!self::createDirIfNotExists($destination, true)) {
      throw new FileSystemException('Error creating directory "' . $destination . '"');
    }
    if (!@rename($source, $destination)) {
      $errors= error_get_last();
      if (empty($errorMessage)) {
        $errorMessage = "error moving directory from '%s' to '%s' (%s): %s";
      }
      throw new FileSystemException($errorMessage, $source, $destination, $errors['type'], $errors['message']);
    }
  }

  public static function joinPath()
  {
    $num_args = func_num_args();
    $args = func_get_args();
    $path = $args[0];

    if ($num_args > 1) {
      for ($i = 1; $i < $num_args; $i++) {
        $path .= DIRECTORY_SEPARATOR.$args[$i];
      }
    }

    return $path;
  }

  public static function getTreeForService($path)
  {
    if (!is_dir($path)) {
      return array();
    }

    $directories = array();
    $files = array();

    $dirIterator = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS);
    foreach ($dirIterator as $fileinfo) {
      if ($fileinfo->isDir()) {
        $subDirPath = self::joinPath($path, $fileinfo->getFilename());
        $directories[$fileinfo->getFilename()] = array(
          'text'      => $fileinfo->getFilename(),
          'leaf'      => false,
          'children'  => self::getTreeForService($subDirPath),
        );
      } else {
        $files[$fileinfo->getFilename()] = array(
          'text'      => $fileinfo->getFilename(),
          'leaf'      => true,
          'size'      => $fileinfo->getSize(),
        );
      }
    }

    ksort($directories);
    ksort($files);
    return array_merge(array_values($directories), array_values($files));
  }

  public static function getTreeAsFlatList($path)
  {
    if (!is_dir($path)) {
      return array();
    }

    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($path),
        \RecursiveIteratorIterator::SELF_FIRST
    );
    $files = array();
    foreach ($iterator as $fileinfo) {
      $tmp = $iterator->getSubPathname();
      if ($fileinfo->isFile()) {
        $files[] = $iterator->getSubPathname();
      }
    }

    return $files;
  }

  /**
   * @throws  \Seitenbau\FileSystem\FileSystemException
   */
  public static function writeContentToFile($file, $content, $errorMessage = null)
  {
    $result = @file_put_contents($file, $content);
    if ($result === false) {
      if (empty($errorMessage)) {
        $errorMessage = "error writing to file '%s' (%s): %s";
      }
      $errors = error_get_last();
      throw new FileSystemException(sprintf($errorMessage, $file, $errors['type'], $errors['message']));
    }
    return $result;
  }

  /**
   * @throws  \Seitenbau\FileSystem\FileSystemException
   */
  public static function readContentFromFile($file, $errorMessage = null)
  {
    $content = @file_get_contents($file);
    if ($content === false) {
      $errors = error_get_last();
      if (empty($errorMessage)) {
        $errorMessage = "error reading from file '%s' (%s): %s";
      }
      throw new FileSystemException(sprintf($errorMessage, $file, $errors['type'], $errors['message']));
    }

    return $content;
  }

  /**
   * @param string $pathName
   * @param int    $mode
   *
   * @throws FileSystemException
   */
  public static function chmod($pathName, $mode)
  {
    if (!@chmod($pathName, $mode)) {
      $errors = error_get_last();
      throw new FileSystemException(sprintf("error changing mode for '%s' to '%s' (%s): %s",
        $pathName, $mode, $errors['type'], $errors['message']));
    }
  }
}
