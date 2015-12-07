<?php
namespace Cms\Validator;

use Cms\Validator\Integer as IntegerValidator;

/**
 * Komponententest für Cms\Validator\Integer
 *
 * @package      Cms
 * @subpackage   Validator
 */
class IntegerTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider nonIntegerValuesProvider
   */
  public function isValidShouldReturnFalseForNonIntegerValues($value)
  {
    $integerValidator = new IntegerValidator;
    $this->assertFalse($integerValidator->isValid($value));
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider integerValuesProvider
   */
  public function isValidShouldReturnTrueForIntegerValues($value)
  {
    $integerValidator = new IntegerValidator;
    $this->assertTrue($integerValidator->isValid($value));
  }
  /**
   * @return array
   */
  public function nonIntegerValuesProvider()
  {
    return array(
      array('some_string'),
      array(15245.52),
      array(new \stdClass()),
      array('125.5'),
      array('125,5'),
      array(null),
      array('null'),
      array(array('one')),
    );
  }
  /**
   * @return array
   */
  public function integerValuesProvider()
  {
    return array(
      array(0),
      array(15),
      array('0'),
      array('20'),
    );
  }
}