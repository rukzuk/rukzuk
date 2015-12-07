<?php
namespace Test\Seitenbau\Sql;

use Test\Seitenbau\Reader as AbstractReader;

/**
 * Reader
 *
 * @package      Test
 * @subpackage   Sql
 */
class Reader extends AbstractReader
{
  /**
   * @var string
   */
  private $sqlDirectory;
  
  /**
   * @param string $sqlDirectory
   */
  public function __construct($sqlDirectory)
  {
    $this->sqlDirectory = $sqlDirectory;
  }
  /**
   * @return array Returns all in $sqlDirectory stored Sql Statements
   */
  public function getAllStatements()
  {
    $sqlFiles = $this->getSqlFiles();
    
    foreach ($sqlFiles as $sqlFile) {
      $sqlStatements = explode("\n", $this->getFileContent($sqlFile));
      foreach ($sqlStatements as $sqlStatement) {
        $allSqlStatements[] = trim($sqlStatement);
      }
    }

    return $allSqlStatements;
  }
  /**
   * @return array
   */
  private function getSqlFiles()
  {
    $iterator = new \DirectoryIterator($this->sqlDirectory);
    $sqlFiles = array();
    foreach ($iterator as $fileinfo) {
      if (!$fileinfo->isDot()) {
        $splitFileName = explode('.', $fileinfo->getFilename());
        $extensionOfFile = end($splitFileName);
        if ($extensionOfFile === 'sql') {
          $fullPathToFile = $fileinfo->getPath()
            . DIRECTORY_SEPARATOR . $fileinfo->getFilename();
          $sqlFiles[] = $fullPathToFile;
        }
      }
    }
    
    foreach ($sqlFiles as $index => $sqlFile) {
      if (strpos($sqlFile, 'Media.sql') !== false) {
        array_push($sqlFiles, $sqlFiles[$index]);
        unset($sqlFiles[$index]);
      } 
    }
    
    return $sqlFiles;
  }
  
  /**
   * @param string $name
   * @return array @return array Returns in $sqlDirectory stored Sql Statements by name
   */
  public function byName($name)
  {
    throw new Exception('Not implemented yet');
  }
}