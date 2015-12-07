<?php
namespace Cms\Validator;

use Cms\Validator\BuildId as BuildIdValidator;

/**
 * BuildIdTest
 *
 * @package      Cms
 * @subpackage   Validator
 */
class BuildIdTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group library
   * @group small
   * @group dev
   * @dataProvider invalidBuildIdsProvider
   */
  public function isValidShouldReturnFalseForNonBuildIdValues($id)
  {
    $validator = new BuildIdValidator();
    $this->assertFalse($validator->isValid($id));
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider validBuildIdsProvider
   */
  public function isValidShouldReturnTrueForValidBuildIdValues($id)
  {
    $validator = new BuildIdValidator();
    $this->assertTrue($validator->isValid($id));
  }
  /**
   * @return array
   */
  public function invalidBuildIdsProvider()
  {
    return array(
      array(''),
      array('ABC'),
      array('NULL'),
      array(new \stdClass()),
      array('125.5'),
      array('125,5'),
      array(null),
      array('SITE-125dfb9f-362a-4b89-a084-53c4696473f8-SITE'),
      array('null'),
      array('v0-1312298235'),
      array('vagsbd-1312298235'),
      array('v12-red298235'),
      array(array('one')),
    );
  }
  /**
   * @return array
   */
  public function validBuildIdsProvider()
  {
    return array(
      array('v542121-1312298235'),
      array('v1-1312298235'),
      array('v512-1312298235'),
    );
  }
}