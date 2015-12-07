<?php
namespace Application\Controller\User;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * LoginTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class LoginTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidUsernamesProvider
   */
  public function loginShouldReturnValidationErrorForInvalidUsernames($username)
  {
    $password = 'foobooo';
    $loginRequest = sprintf(
      '/user/login/params/{"username":"%s","password":"%s"}',
      $username,
      $password
    );
    
    $this->dispatch($loginRequest);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame('username', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function loginShouldReturnErrorForNonExistingUser()
  {
    $username = 'non.existing@null.de';
    $password = 'foobooo';
    
    $loginRequest = sprintf(
      '/user/login/params/{"username":"%s","password":"%s"}',
      $username,
      $password
    );
    
    $this->dispatch($loginRequest);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame(6, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   */
  public function loginShouldAllowLoginForUserWithPendingOptin()
  {
    $username = 'pending.optin@sbcms.de';
    $password = 'TEST09';
    
    $loginRequest = sprintf(
      '/user/login/params/{"username":"%s","password":"%s"}',
      $username,
      $password
    );
    
    $this->dispatch($loginRequest);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertTrue($response->getSuccess());
    $this->assertNull($response->getData());
  }
  /**
   * @test
   * @group integration
   */
  public function loginShouldLoginGivenUser()
  {
    $username = 'login0@sbcms.de';
    $password = 'TEST09';
    
    $loginRequest = sprintf(
      '/user/login/params/{"username":"%s","password":"%s"}',
      $username,
      $password
    );
    
    $this->dispatch($loginRequest);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertTrue($response->getSuccess());
  }
  /**
   * @return array
   */
  public function invalidUsernamesProvider()
  {
    return array(
      array(null),
      array(''),
      array(str_repeat('abcdefghij', 25).'123456'), // 256 characters
    );
  }
}