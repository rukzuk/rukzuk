<?php
namespace Cms\Validator;

use Cms\Validator\UserRight as UserRightValidator;
/**
 * RightTest
 *
 * @package      Cms
 * @subpackage   Validator
 */
class UserRightTest extends \PHPUnit_Framework_TestCase
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
    $rightValidator = new UserRightValidator;
    $this->assertFalse($rightValidator->isValid($value));

    $validationErrors = $rightValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      UserRightValidator::INVALID_NO_OBJECT,
      $validationErrors[0]
    );
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnFalseForMissmatchingFields()
  {
    $value = new \stdClass();
    $value->one = 1;
    $value->zwo = 2;
    $rightValidator = new UserRightValidator;
    $this->assertFalse($rightValidator->isValid($value));

    $validationErrors = $rightValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      UserRightValidator::INVALID_HAS_NOT_ALL_REQUIRED_FIELDS,
      $validationErrors[0]
    );
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnFalseForInvalidArea()
  {
    $value = new \stdClass();
    $value->area = 'test';
    $value->privilege = 'all';
    $value->ids = array();

    $rightValidator = new UserRightValidator;
    $this->assertFalse($rightValidator->isValid($value));

    $validationErrors = $rightValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      UserRightValidator::INVALID_AREA,
      $validationErrors[0]
    );
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnFalseForInvalidPrivilege()
  {
    $value = new \stdClass();
    $value->area = 'modules';
    $value->privilege = 'test';
    $value->ids = array();

    $rightValidator = new UserRightValidator;
    $this->assertFalse($rightValidator->isValid($value));

    $validationErrors = $rightValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      UserRightValidator::INVALID_PRIVILEGE,
      $validationErrors[0]
    );
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnFalseForInvalidPrivilegeForArea()
  {
    $value = new \stdClass();
    $value->area = 'pages';
    $value->privilege = 'publish';
    $value->ids = array();
    
    $rightValidator = new UserRightValidator;
    $this->assertFalse($rightValidator->isValid($value));

    $validationErrors = $rightValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      UserRightValidator::INVALID_PRIVILEGE_FOR_AREA,
      $validationErrors[0]
    );
  }
  
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnFalseForUnallowedArea()
  {
    $value = new \stdClass();
    $value->area = 'pages';
    $value->privilege = 'allRights';
    $value->ids = array();
    
    $rightValidator = new UserRightValidator(array('pages'));
    $this->assertFalse($rightValidator->isValid($value));

    $validationErrors = $rightValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      UserRightValidator::UNALLOWED_AREA,
      $validationErrors[0]
    );
  }
  
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnTrueForNonIdableArea()
  {
    $value = new \stdClass();
    $value->area = 'website';
    $value->privilege = 'publish';
    $value->ids = array();
    
    $rightValidator = new UserRightValidator;
    $this->assertTrue($rightValidator->isValid($value));
  }
  
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnTrueForNonIdablePrivilege()
  {
    $value = new \stdClass();
    $value->area = 'pages';
    $value->privilege = 'none';
    $value->ids = array();
    
    $rightValidator = new UserRightValidator;
    $this->assertTrue($rightValidator->isValid($value));
  }
  
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnFalseForEmptyPageIds()
  {
    $value = new \stdClass();
    $value->area = 'pages';
    $value->privilege = 'subEdit';
    $value->ids = array();
    
    $rightValidator = new UserRightValidator;
    $this->assertFalse($rightValidator->isValid($value));

    $validationErrors = $rightValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      UserRightValidator::INVALID_IDS_NO_ARRAY,
      $validationErrors[0]
    );
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnFalseForInvalidIdsIds()
  {
    $value = array(
      'area' => 'pages',
      'privilege' => 'subEdit',
      'ids' => array(
        'ALBUM-be1ecf03-acc4-4fdb-add4-72ebb0878006-ALBUM',
        'USER-be1ecf03-acc4-4fdb-add4-72ebb0878006-USER'
      )
    );
    $value = new \stdClass();
    $value->area = 'pages';
    $value->privilege = 'subEdit';
    $value->ids = array(
        'ALBUM-be1ecf03-acc4-4fdb-add4-72ebb0878006-ALBUM',
        'USER-be1ecf03-acc4-4fdb-add4-72ebb0878006-USER'
    );
    
    $rightValidator = new UserRightValidator;
    $this->assertFalse($rightValidator->isValid($value));

    $validationErrors = $rightValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      UserRightValidator::INVALID_IDS_PAGE_ID,
      $validationErrors[0]
    );
  }
  /**
   * @return array
   */
  public function nonArrayValuesProvider()
  {
    return array(
      array(0),
      array(null),
      array('0'),
      array('cbs'),
      array(150),
    );
  }
}