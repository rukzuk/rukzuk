<?php
namespace Cms\Validator;

use Cms\Validator\UniqueId as UniqueIdValidator,
    Seitenbau\UniqueIdGenerator as UniqueIdGenerator,
    Orm\Data\Template as Template,
    Orm\Data\Page as Page;

/**
 * Komponententest für Cms\Validator\UniqueId
 *
 * @package      Cms
 * @subpackage   Validator
 */
class UniqueIdTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider nonUniqueIdValuesProvider
   */
  public function isValidShouldReturnFalseForNonUniqueIdValues($value)
  {
    $uuidValidator = new UniqueIdValidator('TEST-', '-TEST');
    $this->assertFalse($uuidValidator->isValid($value));
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider uniqueIdValuesProvider
   */
  public function isValidShouldReturnTrueForUniqueIdValues($prefix,
    $suffix, $uuid)
  {
    $uuidValidator = new UniqueIdValidator($prefix, $suffix);
    $this->assertTrue($uuidValidator->isValid($uuid));
  }
  /**
   * @return array
   */
  public function nonUniqueIdValuesProvider()
  {
    return array(
      array('ABC'),
      array('NULL'),
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
  public function uniqueIdValuesProvider()
  {
    $uuidables = array(
      'Orm\Data\Modul',
      'Orm\Data\Template',
      'Orm\Data\Page',
      'Orm\Data\Site',
      'Orm\Data\Unit',
    );

    $providerEntries = array();

    foreach ($uuidables as $uuidable) {
      $providerEntries[] = array(
        $uuidable::ID_PREFIX,
        $uuidable::ID_SUFFIX,
        $uuidable::ID_PREFIX . UniqueIdGenerator::v4() . $uuidable::ID_SUFFIX
      );
    }

    return $providerEntries;
  }
}