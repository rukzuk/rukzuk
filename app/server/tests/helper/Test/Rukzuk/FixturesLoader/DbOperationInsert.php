<?php


namespace Test\Rukzuk\FixturesLoader;


class DbOperationInsert extends \Zend_Test_PHPUnit_Db_Operation_Insert
{
  /**
   *
   * @param \PHPUnit_Extensions_Database_DataSet_ITable $table
   * @param int $rowNum
   * @return array
   */
  protected function buildInsertValues(\PHPUnit_Extensions_Database_DataSet_ITable $table, $rowNum)
  {
    $values = array();
    foreach($table->getTableMetaData()->getColumns() as $columnName) {
      try {
        $values[$columnName] = $table->getValue($rowNum, $columnName);
      } catch (FixtureValueNotExistsException $doNothing) {}
    }
    return $values;
  }
}