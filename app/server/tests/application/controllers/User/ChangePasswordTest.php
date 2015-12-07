<?php
namespace Application\Controller\User;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase,
    Seitenbau\Registry;
/**
 * ChangePasswordTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class ChangePasswordTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidUserIdsProvider
   */
  public function changepasswordShouldReturnValidationErrorForInvalidUserIds($id)
  {
    $password = $oldpassword = 'foobooo';
    $changePasswordRequest = sprintf(
      '/user/changepassword/params/{"id":"%s","oldpassword":"%s","password":"%s"}',
      $id,
      $oldpassword,
      $password
    );
    
    $this->dispatch($changePasswordRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals('id', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidOldPasswordsProvider
   */
  public function changepasswordShouldReturnValidationErrorForInvalidOldPassword($oldpassword)
  {
    $id = 'USER-9a67e2c2-d175-45e9-bcbd-a25cee8a74ne-USER';
    $password = 'foobooo';
    $changePasswordRequest = sprintf(
      '/user/changepassword/params/{"id":"%s","oldpassword":"%s","password":"%s"}',
      $id,
      $oldpassword,
      $password
    );
    
    $this->dispatch($changePasswordRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals('oldpassword', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidPasswordsProvider
   */
  public function changepasswordShouldReturnValidationErrorForInvalidPassword($password)
  {
    $id = 'USER-9a67e2c2-d175-45e9-bcbd-a25cee8a74ne-USER';
    $oldpassword = 'foobooo';
    $changePasswordRequest = sprintf(
      '/user/changepassword/params/{"id":"%s","oldpassword":"%s","password":"%s"}',
      $id,
      $oldpassword,
      $password
    );
    
    $this->dispatch($changePasswordRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals('password', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function changepasswordShouldBeReturnAnErrorForNonExistingUser()
  {
    $id = 'USER-9a67e2c2-d175-45e9-bcbd-a25cee8a74ne-USER';
    $password = $oldpassword = 'foobooo';
    $changePasswordRequest = sprintf(
      '/user/changepassword/params/{"id":"%s","oldpassword":"%s","password":"%s"}',
      $id,
      $oldpassword,
      $password
    );
    
    $this->dispatch($changePasswordRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals(1002, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   */
  public function changepasswordShouldBeReturnAnErrorForNonMatchingOldPassword()
  {
    $id = 'USER-9a67e2c2-d175-45e9-bcbd-a25cee8a01cp-USER';
    $password = $oldpassword = 'foobooo';
    $changePasswordRequest = sprintf(
      '/user/changepassword/params/{"id":"%s","oldpassword":"%s","password":"%s"}',
      $id,
      $oldpassword,
      $password
    );
    
    $this->dispatch($changePasswordRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals(1021, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   */
  public function changepasswordIssuedAsSuperuserShouldChangePasswordAsExpected()
  {
    $this->activateGroupCheck();
    $username = 'change.password1@sbcms.de';
    $id = 'USER-9a67e2c2-d175-45e9-bcbd-a25cee8a02cp-USER';
    $oldpassword = 'TEST09';
    $password = 'brandnew';
    
    $this->assertSuccessfulLogin($username, $oldpassword);
    
    $changePasswordRequest = sprintf(
      '/user/changepassword/params/{"id":"%s","oldpassword":"%s","password":"%s"}',
      $id,
      $oldpassword,
      $password
    );
    
    $this->dispatch($changePasswordRequest);
    
    $this->deactivateGroupCheck();
    
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());

    $userService = new \Cms\Service\User('User');
    $user = $userService->getById($id);
    
    $this->assertInstanceOf('\Cms\Data\User', $user);
    $this->assertPasswordHash($password, $user->getPassword());
    $this->assertEquals($id, $user->getId());
  }
  /**
   * @test
   * @group integration
   */
  public function changepasswordIssuedAsNonSuperuserShouldChangePasswordAsExpected()
  {
    $this->activateGroupCheck();
    $username = 'change.password0@sbcms.de';
    $id = 'USER-9a67e2c2-d175-45e9-bcbd-a25cee8a01cp-USER';
    $oldpassword = 'TEST09';
    $password = 'brandnew';
    
    $this->assertSuccessfulLogin($username, $oldpassword);
    
    $changePasswordRequest = sprintf(
      '/user/changepassword/params/{"id":"%s","oldpassword":"%s","password":"%s"}',
      $id,
      $oldpassword,
      $password
    );
    
    $this->dispatch($changePasswordRequest);
    
    $this->deactivateGroupCheck();
    
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    
    $userService = new \Cms\Service\User('User');
    $user = $userService->getById($id);
    
    $this->assertInstanceOf('\Cms\Data\User', $user);
    $this->assertPasswordHash($password, $user->getPassword());
    $this->assertEquals($id, $user->getId());
  }
  /**
   * @test
   * @group integration
   */
  public function changepasswordShouldBeRejectedWhenUserNotLoggedIn()
  {
    $this->activateGroupCheck();
    $id = 'USER-9a67e2c2-d175-45e9-bcbd-a25cee8a74ne-USER';
    $password = $oldpassword = 'foobooo';
    $changePasswordRequest = sprintf(
      '/user/changepassword/params/{"id":"%s","oldpassword":"%s","password":"%s"}',
      $id,
      $oldpassword,
      $password
    );
    
    $this->dispatch($changePasswordRequest);
    
    $this->deactivateGroupCheck();
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals(5, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   */
  public function changepasswordShouldBeRejectedWhenUserChangeOtherUsersPassword()
  {
    $this->activateGroupCheck();
    $username = 'change.password1@sbcms.de';
    $otherUser = 'USER-9a67e2c2-d175-45e9-bcbd-a25cee8a01cp-USER';
    $password = 'TEST09';
    
    $this->assertSuccessfulLogin($username, $password);
    
    $changePasswordRequest = sprintf(
      '/user/changepassword/params/{"id":"%s","oldpassword":"oldpassword","password":"newpassword"}',
      $otherUser
    );
    
    $this->dispatch($changePasswordRequest);
    
    $this->deactivateGroupCheck();
    
    $response = new Response($this->getResponseBody());

    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals(7, $responseError[0]->code);

    $this->assertSuccessfulLogout();
  }  
  /**
   * @return array
   */
  public function invalidUserIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
    );
  }
  /**
   * @return array
   */
  public function invalidPasswordsProvider()
  {
    $shortPasswort = str_repeat(
      'x',
      Registry::getConfig()->user->password->min - 1
    );
    $longPasswort = str_repeat(
      'y',
      Registry::getConfig()->user->password->max + 1
    );
            
    return array(
      array(null),
      array(9),
      array($shortPasswort),  
      array($longPasswort),
    );
  }
  /**
   * @return array
   */
  public function invalidOldPasswordsProvider()
  {
    $longPasswort = str_repeat(
      'y',
      Registry::getConfig()->user->password->max + 1
    );
            
    return array(
      array(null),
      array(''),
      array($longPasswort),
    );
  }

  /**
   * @param string $password  the password to check
   * @param string $good_hash the hash which should be match the password
   */
  private function assertPasswordHash($password, $good_hash)
  {
    $pH = new \Cms\Access\PasswordHasher();
    $this->assertTrue($pH->validate($password ,$good_hash), 'Password not valid');
  }
}