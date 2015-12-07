<?php


namespace Test\Rukzuk\FixturesLoader;


class DataSetDefaultTable extends \PHPUnit_Extensions_Database_DataSet_DefaultTable
{
  /**
   * Adds a row to the table with optional values.
   *
   * @param array $values
   */
  public function addRow($values = array())
  {
    $this->data[] = $values;
  }

  public function getValue($row, $column)
  {
    if (!isset($this->data[$row][$column]) && !array_key_exists($column, $this->data[$row])) {
      throw new FixtureValueNotExistsException();
    }
    return parent::getValue($row, $column);
  }
}