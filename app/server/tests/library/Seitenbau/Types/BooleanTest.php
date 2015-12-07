<?php
namespace Seitenbau;

use Seitenbau\Types\Boolean as Boolean;
/**
 * Komponententest für Seitenbau\Types\Boolean
 *
 * @package      Seitenbau
 * @subpackage   Types
 */
class BooleanTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group library
   */
  public function getValuesShouldReturnBooleanInStrictValueMode()
  {
    $boolean = new Boolean(null);
    $this->assertInternalType(
      \PHPUnit_Framework_Constraint_IsType::TYPE_BOOL,
      $boolean->getValue()
    );
  }
  /**
   * @test
   * @group library
   */
  public function getValuesShouldReturnIntegerInDoctrineValueMode()
  {
    $boolean = new Boolean(null);
    $this->assertInternalType(
      \PHPUnit_Framework_Constraint_IsType::TYPE_INT,
      $boolean->getValue(Boolean::DOCTRINE_VALUE)
    );
  }
  /**
   * @test
   * @group library
   * @dataProvider trueBooleanValuesProvider
   */
  public function getValueShouldReturnAStrictTrueForTrueBooleanValues($value)
  {
    $boolean = new Boolean($value);
    $this->assertSame(true, $boolean->getValue());
  }
  /**
   * @test
   * @group library
   * @dataProvider trueBooleanValuesProvider
   */
  public function getValueShouldReturnADoctrineTrueForTrueBooleanValues($value)
  {
    $boolean = new Boolean($value);
    $this->assertSame(1, $boolean->getValue(Boolean::DOCTRINE_VALUE));
  }
  /**
   * @test
   * @group library
   * @dataProvider falseBooleanValuesProvider
   */
  public function getValueShouldReturnAStrictFalseForFalseBooleanValues($value)
  {
    $boolean = new Boolean($value);
    $this->assertSame(false, $boolean->getValue());
  }
  /**
   * @test
   * @group library
   * @dataProvider falseBooleanValuesProvider
   */
  public function getValueShouldReturnADoctrineFalseForFalseBooleanValues($value)
  {
    $boolean = new Boolean($value);
    $this->assertSame(0, $boolean->getValue(Boolean::DOCTRINE_VALUE));
  }
  /**
   * @test
   * @group library
   * @dataProvider nonBooleanValuesProvider
   */
  public function getValueShouldReturnAStrictFalseForNonBooleanValues($value)
  {
    $boolean = new Boolean($value);
    $this->assertSame(false, $boolean->getValue());
  }
  /**
   * @return array
   */
  public function trueBooleanValuesProvider()
  {
    return array(
      array('1'),
      array('true'),
      array(true),
      array(1)
    );
  }
  /**
   * @return array
   */
  public function falseBooleanValuesProvider()
  {
    return array(
      array('0'),
      array('false'),
      array(false),
      array(0),
      array(null),
      array('null')
    );
  }
  /**
   * @return array
   */
  public function nonBooleanValuesProvider()
  {
    return array(
      array('some_string'),
      array(15245),
      array(new \stdClass()),
      array(array('one')),
    );
  }
}