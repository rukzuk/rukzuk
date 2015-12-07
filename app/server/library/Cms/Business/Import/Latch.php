<?php
namespace Cms\Business\Import;

use Seitenbau\Registry as Registry;
use Cms\Exception as CmsException;

/**
 * Stellt die Business-Logik fuer den Import Zwischenspicher zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */
class Latch extends \Cms\Business\Base\Plain
{
  const IMPORT_LATCH_STORAGE_FILE = 'latched-imports.json';
  const LATCH_IMPORT_ID_LENGTH = 15;
  /**
   * @return string
   */
  private function generateImportId()
  {
    $randomCharacters = str_split(md5(mt_rand(0, 200000) . microtime(true)));
    $mergedCharacters = array_merge($randomCharacters, range('a', 'z'));
    shuffle($mergedCharacters);
    return substr(implode('', $mergedCharacters), 0, self::LATCH_IMPORT_ID_LENGTH);
  }
  /**
   * @return integer
   */
  protected function getLatchDateAndTime()
  {
    return time();
  }
  
  /**
   * @param string $importId
   * @throws Cms\Exception
   */
  public function unlatchImportFile($importId)
  {
    if ($this->existsLatchDataForImport($importId)) {
      $config = Registry::getConfig();
      $importLatchDirectory = $config->import->latch->directory;
      $importLatchStorageFile = $importLatchDirectory
        . DIRECTORY_SEPARATOR . self::IMPORT_LATCH_STORAGE_FILE;
      
      $latchJson = json_decode(file_get_contents($importLatchStorageFile));
      $latchJsonArrayObject = new \ArrayObject($latchJson);
      $latchJsonValues = $latchJsonArrayObject->getArrayCopy();

      foreach ($latchJsonValues as $latchedImportId => $latchedImport) {
        if ($latchedImportId === $importId) {
          if (file_exists($latchedImport->file)) {
            unlink($latchedImport->file);
          }
          unset($latchJsonValues[$importId]);
        }
      }
      file_put_contents(
          $importLatchStorageFile,
          json_encode($latchJsonValues),
          LOCK_EX
      );
    }
    
    $this->deleteInvalidLatchImports();
  }
  /**
   * @param  string $importId
   * @return array
   * @throws Cms\Exception
   */
  public function getLatchDataForImport($importId)
  {
    $latchData = array();
    if ($this->existsLatchDataForImport($importId)) {
      $config = Registry::getConfig();
      $importLatchDirectory = $config->import->latch->directory;
      $importLatchStorageFile = $importLatchDirectory
        . DIRECTORY_SEPARATOR . self::IMPORT_LATCH_STORAGE_FILE;
      
      $latchJson = json_decode(file_get_contents($importLatchStorageFile));
      $latchJsonArrayObject = new \ArrayObject($latchJson);
      $latchJsonValues = $latchJsonArrayObject->getArrayCopy();
      
      foreach ($latchJsonValues as $latchedImportId => $latchedImport) {
        if ($latchedImportId === $importId) {
          $latchData = array(
            'id' => $importId,
            'websiteId' => $latchedImport->websiteId,
            'file' => $latchedImport->file
          );
        }
      }
      return $latchData;
    }
    return $latchData;
  }
  
  /**
   * @return boolean
   * @throws Cms\Exception
   */
  public function existsLatchDataForImport($importId)
  {
    $config = Registry::getConfig();
    $importLatchDirectory = $config->import->latch->directory;
    
    if (!is_dir($importLatchDirectory)) {
      $detail = array(
        'detail' => 'import cache directory doesn\'t exists'
      );
      throw new CmsException(14, __METHOD__, __LINE__, $detail);
    }
    
    $importLatchStorageFile = $importLatchDirectory
      . DIRECTORY_SEPARATOR . self::IMPORT_LATCH_STORAGE_FILE;
    
    if (!file_exists($importLatchStorageFile)) {
      $detail = array(
        'detail' => 'import cache file doesn\'t exists'
      );
      throw new CmsException(14, __METHOD__, __LINE__, $detail);
    }
    
    $latchJson = json_decode(file_get_contents($importLatchStorageFile));
    if ($latchJson === null) {
      $detail = array(
        'detail' => 'import cache file is empty'
      );
      throw new CmsException(14, __METHOD__, __LINE__, $detail);
    }
    
    $latchJson = json_decode(file_get_contents($importLatchStorageFile));
    $latchJsonArrayObject = new \ArrayObject($latchJson);
    $latchJsonValues = $latchJsonArrayObject->getArrayCopy();

    $latchedImportIds = array_keys($latchJsonValues);

    if (!in_array($importId, $latchedImportIds)) {
      throw new CmsException(15, __METHOD__, __LINE__, array('value' => $importId));
    }
    
    foreach ($latchJsonValues as $latchedImportId => $latchedImport) {
      if ($latchedImportId === $importId) {
        if (!file_exists($latchedImport->file)) {
           throw new CmsException(16, __METHOD__, __LINE__);
        }
      }
    }
    
    return true;
  }
  
  
  /**
   * @param  string $importFile
   * @param  string $websiteId
   * @return string The import id
   * @throws \Cms\Exception
   */
  public function latchImportFile($websiteId, $importFile)
  {
    if (!file_exists($importFile)) {
      throw new CmsException('12', __METHOD__, __LINE__);
    }
    
    $this->deleteInvalidLatchImports();
    
    $importLatchId = $this->generateImportId();
        
    $config = Registry::getConfig();
    $importLatchDirectory = $config->import->latch->directory;

    if (!is_dir($importLatchDirectory)) {
      mkdir($importLatchDirectory);
    }
    $latchImportFile = $importLatchDirectory
      . DIRECTORY_SEPARATOR . $importLatchId.'.zip';
    $importLatchStorageFile = $importLatchDirectory
      . DIRECTORY_SEPARATOR . self::IMPORT_LATCH_STORAGE_FILE;
    
    if (file_exists($importLatchStorageFile)) {
      $latchJson = json_decode(file_get_contents($importLatchStorageFile));
      if ($latchJson !== null) {
        $latchJsonArrayObject = new \ArrayObject($latchJson);
        $latchJsonValues = $latchJsonArrayObject->getArrayCopy();
        foreach ($latchJsonValues as $latchedImportId => $latchedImport) {
          if ($latchedImport->file === $latchImportFile) {
            throw new CmsException('13', __METHOD__, __LINE__);
          }
        }
      }
    }

    if (is_file($importFile)) {
      if (!copy($importFile, $latchImportFile)) {
        throw new CmsException('12', __METHOD__, __LINE__);
      }
    } elseif (is_dir($importFile)) {
      if (!$this->createLatchImportZipFile($importFile, $latchImportFile)) {
        throw new CmsException('12', __METHOD__, __LINE__);
      }
    } else {
      throw new CmsException('12', __METHOD__, __LINE__);
    }
    
    if (!file_exists($importLatchStorageFile)) {
      file_put_contents($importLatchStorageFile, '', LOCK_EX);
    }

    $latchJson = json_decode(file_get_contents($importLatchStorageFile));
    $dateTime = $this->getLatchDateAndTime();

    if ($latchJson === null) {
      $latchJsonValues = array();
      $latchJsonValues[$importLatchId] = array(
        'websiteId' => $websiteId,
        'file' => $latchImportFile,
        'date' => $dateTime,
        'name' => basename($importFile)
      );
    } else {
      $latchJsonArrayObject = new \ArrayObject($latchJson);
      $latchJsonValues = $latchJsonArrayObject->getArrayCopy();
      $latchJsonValues[$importLatchId] = array(
        'websiteId' => $websiteId,
        'file' => $latchImportFile,
        'date' => $dateTime ,
        'name' => basename($importFile)
      );
    }

    file_put_contents(
        $importLatchStorageFile,
        json_encode($latchJsonValues),
        LOCK_EX
    );

    return $importLatchId;
  }
  
  /**
   * Loescht nicht mehr gueltige Imports
   */
  public function deleteInvalidLatchImports()
  {
    $config = Registry::getConfig();
    $maxLifeTime = time() - $config->import->latch->gc_maxlifetime;
    $importLatchDirectory = $config->import->latch->directory;
    $importLatchStorageFile = $importLatchDirectory
      . DIRECTORY_SEPARATOR . self::IMPORT_LATCH_STORAGE_FILE;

    if (file_exists($importLatchStorageFile)) {
      $latchJson = json_decode(file_get_contents($importLatchStorageFile));
      $latchJsonArrayObject = new \ArrayObject($latchJson);
      $latchJsonValues = $latchJsonArrayObject->getArrayCopy();

      foreach ($latchJsonValues as $latchedImportId => $latchedImport) {
        if ((int)$latchedImport->date < $maxLifeTime) {
          if (file_exists($latchedImport->file)) {
            unlink($latchedImport->file);
          }
          unset($latchJsonValues[$latchedImportId]);
        }
      }
      file_put_contents(
          $importLatchStorageFile,
          json_encode($latchJsonValues),
          LOCK_EX
      );
    }
  }

  /**
   * @param string $importDirectory
   * @param string $latchImportFile
   *
   * @return bool
   * @throws CmsException
   */
  protected function createLatchImportZipFile($importDirectory, $latchImportFile)
  {
    $zip = new \ZipArchive();
    if ($zip->open($latchImportFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
      throw new CmsException(12, __METHOD__, __LINE__, array('file' => $latchImportFile));
    }

    $iterator  = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($importDirectory),
        \RecursiveIteratorIterator::SELF_FIRST
    );
    while ($iterator->valid()) {
      if (!$iterator->isDot()) {
        if ($iterator->isDir()) {
          $zip->addEmptyDir(str_replace('\\', '/', $iterator->getSubPathName()));
        } else {
          $zip->addFile($iterator->key(), str_replace('\\', '/', $iterator->getSubPathName()));
        }
      }
      $iterator->next();
    }
    return $zip->close();
  }
}
