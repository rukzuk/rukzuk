<?php
namespace Cms\Business\User;

use Cms\Business\User as UserBusiness,
    Orm\Data\User as DataUser,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * CreateTest
 *
 * @package      Test
 * @subpackage   Business
 */
class CreateTest extends ServiceTestCase
{
  /**
   * @var Cms\Business\User
   */
  protected $business;
  
  protected function setUp()
  {
    parent::setUp();
    
    $this->business = new UserBusiness('User');
  }
  /**
   * @test
   * @group library
   */
  public function businessShouldCreateUserAsExpected()
  {
    $createValues = array(
      'email' => 'business.test@seitenbau-test.de',
      'lastname' => 'business_create_test_lastname',
      'firstname' => 'business_create_test_firstname',
      'gender' => 'm',
      'isSuperuser' => false,
      'isDeletable' => true
    );
    
    $testUser = $this->business->create($createValues);
    
    $this->assertInstanceOf('\Cms\Data\User', $testUser);
    
    foreach ($createValues as $field => $value) {
      if ($field !== 'isSuperuser' && $field !== 'isDeletable') {
        $getter = 'get' . $field;
      } else {
        $getter = $field;  
      }
      $this->assertSame($createValues[$field], $testUser->$getter());
    }
    $this->assertTrue($this->validateUniqueId(new DataUser(), $testUser->getId()));
  }
}