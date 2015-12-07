<?php
namespace Application\Controller\User;

use Cms\Access\PasswordHasher;
use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Optin as OptinTestHelper,
    Seitenbau\Registry;
/**
 * OptinTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class OptinTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidCodesProvider
   */
  public function optinShouldReturnValidationErrorForInvalidCodes($code)
  {
    $password = 'foobooo';
    $optinRequest = sprintf(
      '/user/optin/params/{"code":"%s","password":"%s"}',
      $code,
      $password
    );
    
    $this->dispatch($optinRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame('code', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidPasswordsProvider
   */
  public function optinShouldReturnValidationErrorForInvalidPasswords($password)
  {
    $code = str_repeat('x', Registry::getConfig()->optin->code->length);
    $optinRequest = sprintf(
      '/user/optin/params/{"code":"%s","password":"%s"}',
      $code,
      $password
    );
    
    $this->dispatch($optinRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame('password', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function optinForRegisterShouldReturnErrorForNonExistingCode()
  {
    $optinCode = 'f00bb6noway';
    $password = 'testpassword';
    $optinRequest = sprintf(
      '/user/optin/params/{"code":"%s","password":"%s"}',
      $optinCode,
      $password
    );
    
    $this->dispatch($optinRequest);
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame(1035, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   */
  public function optinForPasswordShouldReturnErrorForNonExistingCode()
  {
    $optinCode = 'f00bb7noway';
    $password = 'testpassword';
    $optinRequest = sprintf(
      '/user/optin/params/{"code":"%s","password":"%s"}',
      $optinCode,
      $password
    );
    
    $this->dispatch($optinRequest);
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame(1035, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   */
  public function optinForRegisterShouldReturnErrorForExpiredCode()
  {
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      2
    );
    
    $optinCode = 'f01bb65grbw';
    $password = 'testpassword';
    $optinRequest = sprintf(
      '/user/optin/params/{"code":"%s","password":"%s"}',
      $optinCode,
      $password
    );
    
    $this->dispatch($optinRequest);
    
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      $formerLifetime
    );
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame(1037, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   */
  public function optinForPasswordShouldReturnErrorForExpiredCode()
  {
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      2
    );
    
    $optinCode = 'f03bb65grbw';
    $password = 'testpassword';
    $optinRequest = sprintf(
      '/user/optin/params/{"code":"%s","password":"%s"}',
      $optinCode,
      $password
    );
    
    $this->dispatch($optinRequest);
    
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      $formerLifetime
    );
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame(1037, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   */
  public function optinForRegisterShouldSetPasswordAndRemoveOptinCode()
  {
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      0
    );
    $userId = 'USER-reg04gc0-b7a3-4599-b396-94c8bb6c10d9-USER';
    $optinCode = 'f02bb65grbw';
    $password = 'testpassword_register';
    
    $optinRequest = sprintf(
      '/user/optin/params/{"code":"%s","password":"%s"}',
      $optinCode,
      $password
    );
    
    $this->dispatch($optinRequest);
    
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      $formerLifetime
    );
    
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    
    $userDao = \Cms\Dao\Factory::get('User');
    $optinDao = \Cms\Dao\Factory::get('Optin');
    
    $user = $userDao->getById($userId);
    $pH = new PasswordHasher();
    $this->assertTrue($pH->validate($password,  $user->getPassword()));

    try {
      $optinDao->getByCode($optinCode);
      $this->fail('An expected exception has not been raised.');
    } catch (\Exception $e) {
      $this->assertInstanceOf('Cms\Exception', $e);
    }
  }
  /**
   * @test
   * @group integration
   */
  public function optinShouldNotBeRejectedWhenUserNotLoggedIn()
  {
    $this->activateGroupCheck();
    
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      0
    );
    
    $config = Registry::getConfig();
    
    $optinCode = 'g03bb64cpoi';
    $password = 'testpassword_password';
    
    $optinRequest = sprintf(
      '/user/optin/params/{"code":"%s","password":"%s"}',
      $optinCode,
      $password
    );
    
    $this->dispatch($optinRequest);
    
    $this->deactivateGroupCheck();
    
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      $formerLifetime
    );
    
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
  }
  /**
   * @test
   * @group integration
   */
  public function optinForPasswordShouldSetNewPasswordAndRemoveOptinCode()
  {
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      0
    );
    
    $userId = 'USER-ren01gc0-b7a3-4599-b396-94c8bb6c10d9-USER';
    $optinCode = 'f03bb65grbw';
    $password = 'testpassword_password';
    
    $optinRequest = sprintf(
      '/user/optin/params/{"code":"%s","password":"%s"}',
      $optinCode,
      $password
    );
    
    $this->dispatch($optinRequest);
    
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      $formerLifetime
    );
    
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    
    $optinDao = \Cms\Dao\Factory::get('Optin');
    $userDao = \Cms\Dao\Factory::get('User');
    
    $user = $userDao->getById($userId);
    $pH = new PasswordHasher();
    $this->assertTrue($pH->validate($password,  $user->getPassword()));

    try {
      $optinDao->getByCode($optinCode);
      $this->fail('An expected exception has not been raised.');
    } catch (\Exception $e) {
      $this->assertInstanceOf('Cms\Exception', $e);
    }
  }
  /**
   * @return array
   */
  public function invalidCodesProvider()
  {
    return array(
      array('absb'),
      array(str_repeat('x', Registry::getConfig()->optin->code->length + 1)),
      array(null),
      array(16),
    );
  }
  /**
   * @return array
   */
  public function invalidPasswordsProvider()
  {
    return array(
      array(null),
      array(9),
      array('a'),  
      array(str_repeat('y', 256)),
    );
  }

}