<?php
namespace Cms\Business\Uuid;

use Cms\Business\Uuid as UuidBusiness,
    Cms\Validator\UniqueId as UniqueIdValidator;

/**
 * GetTest
 *
 * @package      Library\Cms
 * @subpackage   Business
 */

class GetTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var Cms\Business\Uuid
   */
  private $business;

  public function setUp()
  {
    parent::setUp();

    $this->business = new UuidBusiness('Uuid');
  }
  /**
   * @test
   * @group library
   * @dataProvider dataClassNameOrmDataAndCountProvider
   */
  public function serviceShouldGenerateExpectedUiids($dataClassName, $uuidable, $count)
  {
    $uuids = $this->business->getUuids($dataClassName, $count);
    
    $this->assertSame($count, count($uuids));

    $uuidValidator = new UniqueIdValidator(
      $uuidable::ID_PREFIX,
      $uuidable::ID_SUFFIX
    );
    foreach ($uuids as $uuid) {
      $this->assertTrue($uuidValidator->isValid($uuid));
    }
  }
  /**
   * @test
   * @group library
   * @dataProvider invalidDataClassNamesProvider
   * @expectedException Cms\Exception
   * @param mixed   $dataClassName
   * @param integer $count
   */
  public function serviceShouldThrowExpectedExceptionForInvalidDataClassNames(
    $dataClassName, $count)
  {
    $this->business->getUuids($dataClassName, $count);
  }

  /**
   * @return array
   */
  public function invalidDataClassNamesProvider()
  {
    return array(
      array('Test', 2),
      array('Templateo', 2),
      array(5, 2),
      array(null, 2),
    );
  }

  /**
   * @return array
   */
  public function dataClassNameOrmDataAndCountProvider()
  {
    return array(
      array('Modul', 'Orm\Data\Modul', 2),
      array('Template', 'Orm\Data\Template', 2),
      array('Unit', 'Orm\Data\Unit', 4),
      array('Page', 'Orm\Data\Page', 2),
      array('Site', 'Orm\Data\Site', 6),
    );
  }
}