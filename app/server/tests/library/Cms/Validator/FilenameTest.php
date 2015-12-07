<?php
namespace Cms\Validator;

use Cms\Validator\Filename as FilenameValidator;

/**
 * FilenameTest
 *
 * @package      Cms
 * @subpackage   Validator
 */
class FilenameTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group small
   * @grouo dev
   * @group library
   * @dataProvider invalidFilenamesProvider
   */
  public function isValidShouldReturnFalseForInvalidFilenameValues($name)
  {
    $validator = new FilenameValidator();
    $this->assertFalse($validator->isValid($name));
  }
  /**
   * @test
   * @group small
   * @grouo dev
   * @group library
   * @dataProvider validFilenamesProvider
   */
  public function isValidShouldReturnTrueForValidFilenameValues($name)
  {
    $validator = new FilenameValidator();
    $this->assertTrue($validator->isValid($name));
  }
  /**
   * @return array
   */
  public function invalidFilenamesProvider()
  {
    return array(
      array(''),
      array('one/'),
      array('one\\'),
      array('one*'),
      array('one?'),
      array('one:'),
      array('one['),
      array('one]'),
      array('one{'),
      array('one}'),
      array('one;'),
      array('one%'),
    );
  }
  /**
   * @return array
   */
  public function validFilenamesProvider()
  {
    return array(
      array('v542121-1312298235'),
      array('abdssolkd4521_'),
    );
  }
}