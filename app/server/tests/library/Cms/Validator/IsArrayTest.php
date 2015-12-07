<?php
namespace Cms\Validator;

use Cms\Validator\IsArray as IsArrayValidator;

/**
 * Komponententest für Cms\Validator\IsArray
 *
 * @package      Cms
 * @subpackage   Validator
 */
class IsArrayTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider nonArrayValuesProvider
   */
  public function isValidShouldReturnFalseForNonArrayValues($value)
  {
    $isArrayValidator = new IsArrayValidator;
    $this->assertFalse($isArrayValidator->isValid($value));
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider arrayValuesProvider
   */
  public function isValidShouldReturnTrueForArrayValues($value)
  {
    $isArrayValidator = new IsArrayValidator;
    $this->assertTrue($isArrayValidator->isValid($value));
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider arrayMixedValuesProvider
   */
  public function isValidShouldReturnTrueForMixedAndEmptyArrayValues($value)
  {
    $isArrayValidator = new IsArrayValidator(false);
    $this->assertTrue($isArrayValidator->isValid($value));
  }
  /**
   * @return array
   */
  public function nonArrayValuesProvider()
  {
    return array(
      array('some_string'),
      array(15245.52),
      array(new \stdClass()),
      array('125.5'),
      array('125,5'),
      array(null),
      array('null'),
    );
  }
  /**
   * @return array
   */
  public function arrayValuesProvider()
  {
    return array(
      array(array(1,2,3)),
      array(array('key' => 'value')),
    );
  }
  /**
   * @return array
   */
  public function arrayMixedValuesProvider()
  {
    return array(
      array(array()),
      array(array(array())),
      array(array(1,2,3)),
      array(array('key' => 'value')),
    );
  }
}