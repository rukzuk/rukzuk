<?php


namespace Test\Rukzuk;

use \Seitenbau\FileSystem as FS;
use Seitenbau\Registry;
use Test\Rukzuk\FixturesLoader\ArrayDataSet;
use Test\Rukzuk\FixturesLoader\DbOperationInsert;

class FixturesLoaderTestCaseHelper
{
  /**
   * @var \Zend_Db_Adapter_Pdo_Mysql
   */
  private $dbAdapter = null;

  public function __construct($dbAdapter)
  {
    $this->dbAdapter = $dbAdapter;
  }

  /**
   * Loads all SQL fixtures from the given fixtures files
   *
   * @param array       $sqlFixtures -- array of sql fixtures files
   * @param string|null $fixturesDirectory
   */
  public function loadSqlFixtures(array $sqlFixtures, $fixturesDirectory = null)
  {
    if (empty($fixturesDirectory)) {
      $fixturesDirectory = Registry::getConfig()->test->fixtures->directory;
    }
    foreach ($sqlFixtures as $fixtureFilePath) {
      $this->loadSqlFixture(FS::joinPath($fixturesDirectory, $fixtureFilePath));
    }
  }

  /**
   * Loads the SQL fixtures from the given file
   *
   * @param $fixtureFilePath -- the path of the fixtures file
   */
  public function loadSqlFixture($fixtureFilePath)
  {
    switch (pathinfo($fixtureFilePath, PATHINFO_EXTENSION)) {
      case 'sql':
        return $this->loadSqlFixtureFromSqlFile($fixtureFilePath);
        break;
      case 'php':
        return $this->loadSqlFixtureFromPhpFile($fixtureFilePath);
        break;
      case 'json':
        return $this->loadSqlFixtureFromJsonFile($fixtureFilePath);
        break;
    }
  }

  /**
   * Loads the SQL fixtures from the given sql fixtures file
   *
   * @param $fixtureFilePath -- the path of the fixtures file
   */
  protected function loadSqlFixtureFromSqlFile($fixtureFilePath)
  {
    $sqlStatements = $this->loadSqlStatementsFromFile($fixtureFilePath);
    $this->runSqlStatements($sqlStatements);
  }

  /**
   * Loads the SQL fixtures from the given php file
   *
   * @param $fixtureFilePath -- the path of the fixtures file
   */
  protected function loadSqlFixtureFromPhpFile($fixtureFilePath)
  {
    $this->loadSqlFixtureFromArray(include($fixtureFilePath));
  }

  /**
   * Loads the SQL fixtures from the given json file
   *
   * @param $fixtureFilePath -- the path of the fixtures file
   */
  protected function loadSqlFixtureFromJsonFile($fixtureFilePath)
  {
    $dataSetsAsArray = json_decode(FS::readContentFromFile($fixtureFilePath), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \RuntimeException($fixtureFilePath . '::' . json_last_error_msg());
    }
    $this->loadSqlFixtureFromArray($dataSetsAsArray);
  }

  /**
   * * Loads the SQL fixtures from array
   *
   * @param array $dataSetsAsArray
   */
  protected function loadSqlFixtureFromArray(array $dataSetsAsArray)
  {
    $databaseFixture = new ArrayDataSet($dataSetsAsArray);
    $connection = new \Zend_Test_PHPUnit_Db_Connection($this->getDbAdapter(), 'test_db');
    $databaseTester = new \Zend_Test_PHPUnit_Db_SimpleTester($connection);
    $databaseTester->setSetUpOperation(new DbOperationInsert());
    $databaseTester->setupDatabase($databaseFixture);
  }

  /**
   * Loads all sql statements from an given sql file.
   *
   * @param $sqlFilePath -- the file path of the sql file
   *
   * @return array -- List of all sql statements as a sting (never null)
   */
  protected function loadSqlStatementsFromFile($sqlFilePath)
  {
    $sqlStatements = explode("\n", self::getFileContent($sqlFilePath));
    $allSqlStatements = array();
    foreach ($sqlStatements as $sqlStatement) {
      $line = trim($sqlStatement);
      if ($line !== '') {
        $allSqlStatements[] = $line;
      }
    }
    return $allSqlStatements;
  }

  /**
   * @return \Zend_Db_Adapter_Pdo_Mysql
   */
  protected  function getDbAdapter()
  {
    return $this->dbAdapter;
  }

  /**
   * @return mixed -- The current DB pdo
   */
  protected  function getDBConnection()
  {
    return $this->getDbAdapter()->getConnection();
  }

  /**
   * Runs the given SQL Statement on the Test PDO
   *
   * @param array $sqlStatements -- The SQL statement that should be run/called
   *
   * @throws Exception -- When the SQL statement fails.
   */
  protected function runSqlStatements(array $sqlStatements)
  {
    $pdo = $this->getDBConnection();
    foreach ($sqlStatements as $sqlStatement) {
      if ($pdo->exec($sqlStatement) === false) {
        $exceptionMessage = implode(':', $pdo->errorInfo()) . PHP_EOL
            . ">>>> Triggered by [ %s ]";
        $exceptionMessage = sprintf($exceptionMessage, $sqlStatement);
        throw new \Exception($exceptionMessage);
      }
    }
  }

  /**
   * @param $filePath -- Path to the file that should be loaded
   *
   * @return string -- The file content as a string.
   */
  protected static function getFileContent($filePath)
  {
    $content = file_get_contents($filePath);
    if (function_exists('mb_convert_encoding')) {
      return \mb_convert_encoding(
        $content,
        'UTF-8',
        \mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)
      );
    }
    return $content;
  }

}