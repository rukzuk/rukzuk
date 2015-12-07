<?php
namespace Cms\Validator;

use Cms\Validator\JsonStructure as JsonStructureValidator;

/**
 * Komponententest für Cms\Validator\JsonStructure
 *
 * @package      Application
 * @subpackage   Controller
 */
class JsonStructureTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider nonJsonStructureValuesProvider
   */
  public function isValidShouldReturnFalseForNonJsonStructureValues($value)
  {
    $jsonStructureValidator = new JsonStructureValidator();
    $this->assertFalse($jsonStructureValidator->isValid($value));
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider jsonStructureValuesProvider
   */
  public function isValidShouldReturnTrueForJsonStructureValues($value)
  {
    $jsonStructureValidator = new JsonStructureValidator();
    $this->assertTrue($jsonStructureValidator->isValid($value));
  }
  /**
   * @return array
   */
  public function nonJsonStructureValuesProvider()
  {
    return array(
      array(''),
      array('NULL'),
      array(new \stdClass()),
      array('true'),
      array('false'),
      array('125,5'),
      array(null),
      array('null'),
      array(array('one')),
    );
  }
  /**
   * @return array
   */
  public function jsonStructureValuesProvider()
  {
    return array(
      array('{}'),
      array('{"name": "test"}'),
      array('{"name": "test", "keys": ["value1", "value2"]}'),
    );
  }
}