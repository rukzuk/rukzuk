<?php
namespace Cms\Validator;

use Cms\Validator\ModuleId as ModuleIdValidator,
    Seitenbau\UniqueIdGenerator as UniqueIdGenerator,
    Orm\Data\Modul as OrmDataModule;

/**
 * validator test for Cms\Validator\ModuleId
 *
 * @package      Cms
 * @subpackage   Validator
 */
class ModuleIdTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider nonModuleIdValuesProvider
   */
  public function isValidShouldReturnFalseForNonModuleIdValues($legacySupport, $moduleId)
  {
    $moduleIdValidator = new ModuleIdValidator($legacySupport);
    $this->assertFalse($moduleIdValidator->isValid($moduleId));
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider moduleIdValuesProvider
   */
  public function isValidShouldReturnTrueForModuleIdValues($legacySupport, $moduleId)
  {
    $moduleIdValidator = new ModuleIdValidator($legacySupport);
    $this->assertTrue($moduleIdValidator->isValid($moduleId));
  }
  /**
   * @return array
   */
  public function nonModuleIdValuesProvider()
  {
    $nonModuleIds = array(
      array(false, new \stdClass()),
      array(false, null),
      array(false, array('one')),
      array(false, 'CAPITALS'),
      array(false, '0_started_with_number'),
      array(false, 'capital_at_enD'),
      array(false, '125.5'),
      array(false, '125,5'),
      array(true, OrmDataModule::ID_PREFIX.'01234567-456789abcdef'.OrmDataModule::ID_SUFFIX),
      array(true, OrmDataModule::ID_PREFIX.'01234567-89ab'.OrmDataModule::ID_SUFFIX),
      array(true, OrmDataModule::ID_PREFIX.'456789abcdef'.OrmDataModule::ID_SUFFIX),
      array(true, OrmDataModule::ID_PREFIX.'01234567_89ab_cdef_0123_456789abcdef'.OrmDataModule::ID_SUFFIX),
    );
    for($i=1; $i<=20; $i++) {
      $nonModuleIdChars = range(chr(0), chr(47));
      $nonModuleIdChars = array_merge($nonModuleIdChars, range(chr(58), chr(94)));
      $nonModuleIdChars[] = chr(96);
      $nonModuleIdChars = array_merge($nonModuleIdChars, range(chr(123), chr(255)));
      shuffle($nonModuleIdChars);
      $nonModuleId = substr(implode('', $nonModuleIdChars), 0, rand(1, count($nonModuleIdChars)-1));
      $nonModuleIds[] = array(false, $nonModuleId);
    }
    return $nonModuleIds;
  }
  /**
   * @return array
   */
  public function moduleIdValuesProvider()
  {
    $moduleIds = array(
      array(false, 'a'),
      array(false, 'a1'),
      array(false, 'new_module_id'),
      array(false, 'abcdefghijklmnopqrstuvwxyz_0123456789'),
      // legacy ids
      array(true, OrmDataModule::ID_PREFIX.UniqueIdGenerator::v4().OrmDataModule::ID_SUFFIX),
      array(true, OrmDataModule::ID_PREFIX.UniqueIdGenerator::v4().OrmDataModule::ID_SUFFIX),
      array(true, OrmDataModule::ID_PREFIX.'01234567-89ab-cdef-0123-456789abcdef'.OrmDataModule::ID_SUFFIX),
    );
    $moduleIdFirstChars = range('a', 'z');
    $moduleIdChars = array_merge($moduleIdFirstChars, range(0, 9));
    $moduleIdChars[] = '_';
    foreach ($moduleIdFirstChars as $moduleIdFirstChar) {
      shuffle($moduleIdChars);
      $moduleId = $moduleIdFirstChar.implode('', $moduleIdChars);
      $moduleIds[] = array(false, $moduleId);
    }
    return $moduleIds;
  }
}