<?php
namespace Cms\Business\User;

use Cms\Business\User as UserBusiness,
    Orm\Data\User as DataUser,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * EditTest
 *
 * @package      $package
 * @subpackage   $subpackage
 */
class EditTest extends ServiceTestCase
{
  /**
   * @var \Cms\Business\User
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
  public function businessShouldEditUserAsExpected()
  {
    // ARRANGE
    $userId = 'USER-ba67e2cf-1175-45e9-bcbd-a25cee8a74c1-USER';
    $editValues = array(
      'email' => 'business.test.edited@seitenbau-test.de',
      'lastname' => 'rewind_business_edited',
      'firstname' => 'jonny_business_edited',
      'gender' => 'm',
      'password' => 'EditTest01',
      'isSuperuser' => true,
      'isDeletable' => false,
      'isOwner' => false,
    );

    // ACT
    $testUser = $this->business->edit($userId, $editValues);

    // ASSERT
    $this->assertTrue($this->business->validatePassword(
      $editValues['password'], $testUser->getPassword()));
    foreach ($editValues as $field => $value) {
      if ($field == 'password') {
        continue;
      }
      if (in_array($field, array('isSuperuser', 'isDeletable', 'isOwner'))) {
        $getter = $field;
      } else {
        $getter = 'get' . $field;
      }
      $this->assertSame($editValues[$field], $testUser->$getter());
    }
  }
}