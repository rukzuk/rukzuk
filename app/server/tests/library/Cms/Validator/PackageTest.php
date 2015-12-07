<?php


namespace Cms\Validator;

use Cms\Validator\PackageId as PackageIdValidator;

/**
 * Class PackageIdTest
 *
 * @package Cms\Validator
 *
 * @group package
 */
class PackageIdTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider nonIdValuesProvider
   */
  public function isValidShouldReturnFalseForNonPackageIdValues($id)
  {
    $idValidator = new PackageIdValidator();
    $this->assertFalse($idValidator->isValid($id));
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider idValuesProvider
   */
  public function isValidShouldReturnTrueForPackageIdValues($id)
  {
    $idValidator = new PackageIdValidator();
    $this->assertTrue($idValidator->isValid($id));
  }

  /**
   * @return array
   */
  public function nonIdValuesProvider()
  {
    $nonIds = array(
      array(new \stdClass()),
      array(null),
      array(array('one')),
      array('CAPITALS'),
      array('0_started_with_number'),
      array('capital_at_enD'),
      array('125.5'),
      array('125,5'),
    );
    for($i=1; $i<=20; $i++) {
      $nonIdChars = range(chr(0), chr(47));
      $nonIdChars = array_merge($nonIdChars, range(chr(58), chr(94)));
      $nonIdChars[] = chr(96);
      $nonIdChars = array_merge($nonIdChars, range(chr(123), chr(255)));
      shuffle($nonIdChars);
      $nonId = substr(implode('', $nonIdChars), 0, rand(1, count($nonIdChars)-1));
      $nonIds[] = array($nonId);
    }
    return $nonIds;
  }

  /**
   * @return array
   */
  public function idValuesProvider()
  {
    $ids = array(
      array('a'),
      array('a1'),
      array('new_id'),
      array('abcdefghijklmnopqrstuvwxyz_0123456789'),
    );
    $idFirstChars = range('a', 'z');
    $idChars = array_merge($idFirstChars, range(0, 9));
    $idChars[] = '_';
    foreach ($idFirstChars as $idFirstChar) {
      shuffle($idChars);
      $id = $idFirstChar.implode('', $idChars);
      $ids[] = array($id);
    }
    return $ids;
  }
}