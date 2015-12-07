<?php


namespace Test\Rukzuk;


class UnitHelper
{
  /**
   * @return object
   */
  public static function getValidPageUnit()
  {
    return (object) array(
      'id' => 'p_123456789',
      'templateUnitId' => 't_123456789',
      'name' => 'this_is_the_unit_name',
      'description' => 'this_is_the_unit_description',
      'moduleId' => 'm_987654321',
      'formValues' => (object) array(
        'foo' => 'bar',
      ),
      'deletable' => false,
      'readonly' => true,
      'ghostContainer' => true,
      'visibleFormGroups' => array('abc', 'def'),
      'expanded' => true,
      'children' => array()
    );
  }
}
