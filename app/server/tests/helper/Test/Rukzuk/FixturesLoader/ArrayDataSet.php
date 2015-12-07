<?php


namespace Test\Rukzuk\FixturesLoader;


class ArrayDataSet extends \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{
  /**
   * @var array
   */
  protected $tables = array();

  /**
   * @param array $data
   */
  public function __construct(array $data)
  {
    foreach ($data AS $tableName => $rows) {
      $columns = array_unique($this->getL2Keys($rows));
      $metaData = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $columns);
      $table = new DataSetDefaultTable($metaData);

      foreach ($rows AS $row) {
        $table->addRow($row);
      }
      $this->tables[$tableName] = $table;
    }
  }

  protected function createIterator($reverse = FALSE)
  {
    return new \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($this->tables, $reverse);
  }

  public function getTable($tableName)
  {
    if (!isset($this->tables[$tableName])) {
      throw new \InvalidArgumentException("$tableName is not a table in the current database.");
    }

    return $this->tables[$tableName];
  }

  protected function getL2Keys($array)
  {
    $result = array();
    foreach ($array as $sub) {
      $result = array_merge($result, $sub);
    }
    return array_keys($result);
  }
}