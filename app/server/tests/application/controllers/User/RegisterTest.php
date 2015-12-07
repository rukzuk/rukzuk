<?php
namespace Application\Controller\User;

use Orm\Data\User as DataUser,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Optin as OptinTestHelper,
    Seitenbau\Registry,
    Seitenbau\FileSystem as FS;
use Test\Rukzuk\ConfigHelper;

/**
 * RegisterTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class RegisterTest extends ControllerTestCase
{
  private $mailsFromFileTransportDirectory = '/tmp';
  
  protected function tearDown()
  {
    OptinTestHelper::clearMailsFromFileTransports(
      $this->mailsFromFileTransportDirectory
    );
    
    parent::tearDown();
  }
  /**
   * @test
   * @group integration
   * @dataProvider nonArrayUserIdsProvider
   */
  public function registerUserShouldReturnValidationErrorForNonArrayIds($userIds)
  {
    $registerRequest = '/user/register/params/'.urlencode(json_encode(array('ids' => $userIds)));
    
    $this->dispatch($registerRequest);
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame('ids', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidUserIdsProvider
   */
  public function registerUserShouldReturnValidationErrorForInvalidUserIds($userIds)
  {
    $registerRequest = sprintf(
      '/user/register/params/{"ids":[%s]}',
      implode(',', $userIds)
    );
    
    $this->dispatch($registerRequest);
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame('id', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function registerUserShouldBeRejectedWhenUserNotLoggedIn()
  {
    $this->activateGroupCheck();
    
    $userIds = array(
      '"USER-reg00gc0-b7a3-4599-b396-94c8bb6c10d9-USER"' 
    );
    $registerRequest = sprintf(
      '/user/register/params/{"ids":[%s]}',
      implode(',', $userIds)
    );
    
    $this->dispatch($registerRequest);
    
    $this->deactivateGroupCheck();
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals(5, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   * @expectedException Cms\Exception
   */
  public function registerShouldNotCreateOptinCodeWhenUserMailIsInActive()
  {
    $formerUserMailActiveStatus = OptinTestHelper::changeConfiguredUserMailActiveStatus(0);
    
    $userIds = array(
      '"USER-reg00gc0-b7a3-4599-b396-94c8bb6c10d9-USER"' 
    );
    $registerRequest = sprintf(
      '/user/register/params/{"ids":[%s]}',
      implode(',', $userIds)
    );
    
    $this->dispatch($registerRequest);
   
    OptinTestHelper::changeConfiguredUserMailActiveStatus(
      $formerUserMailActiveStatus
    );
    
    $optinService = new \Cms\Service\Optin('Optin');
    $optinService->getDao()->getByUserId(str_replace('"', '', $userIds[0]));
  }
  /**
   * @test
   * @group integration
   */
  public function registerShouldNotSendOptinMailWhenUserMailIsInActive()
  {
    $formerUserMailActiveStatus = OptinTestHelper::changeConfiguredUserMailActiveStatus(0);
    
    $userIds = array(
      '"USER-reg00gc0-b7a3-4599-b396-94c8bb6c10d9-USER"' 
    );
    $registerRequest = sprintf(
      '/user/register/params/{"ids":[%s]}',
      implode(',', $userIds)
    );
    
    $this->dispatch($registerRequest);
    
    OptinTestHelper::changeConfiguredUserMailActiveStatus(
      $formerUserMailActiveStatus
    );
    
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    
    $this->assertEquals(
      0, 
      OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory)
    );
  }
  /**
   * @test
   * @group integration
   */
  public function registerShouldCreateOptinCodeForUser()
  {
    $userIds = array(
      '"USER-reg00gc0-b7a3-4599-b396-94c8bb6c10d9-USER"' 
    );
    $registerRequest = sprintf(
      '/user/register/params/{"ids":[%s]}',
      implode(',', $userIds)
    );
    
    $this->dispatch($registerRequest);
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $this->assertEquals(
      count($userIds), 
      OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory)
    );
    
    $mailsContent = OptinTestHelper::getFileMailsContent(
      $this->mailsFromFileTransportDirectory
    );
    $actualMailContent = $mailsContent[0];
    $actualOptinCode = OptinTestHelper::getOptinCodeFromMailContent(
      $actualMailContent
    );
    
    $optinService = new \Cms\Service\Optin('Optin');
    $optin = $optinService->getDao()->getByCode($actualOptinCode);
    
    $this->assertEquals($actualOptinCode, $optin->getCode());
    $this->assertEquals(str_replace('"', '', $userIds[0]), $optin->getUser()->getId());
    $this->assertEquals(str_replace('"', '', $userIds[0]), $optin->getUserid());
    $this->assertEquals(\Orm\Entity\Optin::MODE_REGISTER, $optin->getMode());
  }
  /**
   * @test
   * @group integration
   */
  public function registerShouldSendExpectedOptinMailsCount()
  {
    $userIds = array(
      '"USER-reg00gc0-b7a3-4599-b396-94c8bb6c10d9-USER"', 
      '"USER-reg01gc0-b7a3-4599-b396-94c8bb6c10d9-USER"'
    );
    $registerRequest = sprintf(
      '/user/register/params/{"ids":[%s]}',
      implode(',', $userIds)
    );
    
    $this->dispatch($registerRequest);
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $this->assertEquals(
      count($userIds), 
      OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory)
    );
  }

  /**
   * @test
   * @group integration
   */
  public function registerShouldSendOptinMailWithExpectedContent()
  {
    // ARRANGE
    ConfigHelper::removeOwner();
    $config = Registry::getConfig();
    $expectedFromUser = array(
      'email' => $config->user->mail->optin->from->address,
      'name' => $config->user->mail->optin->from->name,
    );
    $userId = 'USER-reg00gc0-b7a3-4599-b396-94c8bb6c10d9-USER';

    // ACT
    $this->dispatchWithParams('user/register',  array(
      'ids' => array($userId),
    ));

    // ASSERT
    $this->getValidatedSuccessResponse();

    $this->assertEquals(1, OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory));
    $mailsContent = OptinTestHelper::getFileMailsContent($this->mailsFromFileTransportDirectory);
    $actualMailContent = $mailsContent[0];
    $actualOptinCode = OptinTestHelper::getOptinCodeFromMailContent($actualMailContent);
    $optinService = new \Cms\Service\Optin('Optin');
    $optin = $optinService->getDao()->getByCode($actualOptinCode);
    
    $this->assertRegisterMailSendSuccessfully($optin, $optin->getUser(), $expectedFromUser,
      $actualMailContent);
  }

  /**
   * @test
   * @group integration
   */
  public function test_registerShouldSendOptinMailFromOwnerAdress()
  {
    // ARRANGE
    $expectedOwner = ConfigHelper::setOwner();
    $expectedFromUser = array(
      'email' => $expectedOwner['email'],
      'name' => $expectedOwner['firstname'] . ' ' . $expectedOwner['lastname'],
    );
    $userId = 'USER-reg00gc0-b7a3-4599-b396-94c8bb6c10d9-USER';

    // ACT
    $this->dispatchWithParams('user/register',  array(
      'ids' => array($userId),
    ));

    // ASSERT
    $this->getValidatedSuccessResponse();

    $this->assertEquals(1, OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory));
    $mailsContent = OptinTestHelper::getFileMailsContent($this->mailsFromFileTransportDirectory);
    $actualMailContent = $mailsContent[0];
    $actualOptinCode = OptinTestHelper::getOptinCodeFromMailContent($actualMailContent);
    $optinService = new \Cms\Service\Optin('Optin');
    $optin = $optinService->getDao()->getByCode($actualOptinCode);

    $this->assertRegisterMailSendSuccessfully($optin, $optin->getUser(), $expectedFromUser,
      $actualMailContent);
  }

  protected function assertRegisterMailSendSuccessfully($optin, $user, $expectedFromUser,
                                                        $actualMailContent)
  {
    $expectedMailContent = OptinTestHelper::createMailContentFromMailTemplate(
      FS::joinPath(realpath(Registry::getConfig()->test->files->directory), 'mails', 'optin.txt'),
      array(
        '@@OPTIN_CODE@@'      => $optin->getCode(),
        '@@WEBHOST@@'         => Registry::getBaseUrl(),
        '@@FROM_NAME@@'       => $expectedFromUser['name'],
        '@@FROM_ADRESS@@'     => $expectedFromUser['email'],
        '@@USER_FIRSTNAME@@'  => $user->getFirstname(),
        '@@USER_LASTNAME@@'   => $user->getLastname(),
        '@@USER_EMAIL@@'      => $user->getEmail(),
      )
    );
    
    $this->assertEquals(
      OptinTestHelper::clearLineBreaksInMailContent($expectedMailContent),
      OptinTestHelper::clearLineBreaksInMailContent($actualMailContent)
    );
  }
  
  /**
   * @test
   * @group  bugs
   * @ticket SBCMS-788
   */
  public function registerShouldBeAbleWhenPasswordIsAlreadySet()
  {
    $userIds = array(
      '"USER-reg05gc0-b7a3-4599-b396-94c8bb6c10d9-USER"'
    );
    $registerRequest = sprintf(
      '/user/register/params/{"ids":[%s]}',
      implode(',', $userIds)
    );
    
    $this->dispatch($registerRequest);
    
    $response = new Response( $this->getResponseBody());
    $this->assertTrue($response->getSuccess());
  }
  
  /**
   * @return array
   */
  public function invalidUserIdsProvider()
  {
    return array(
      array(array(15, 16, 17)),
      array(array('"a"', '"b"', '"c"')),
      array(array(
        '"MODUL-0rap62te-0t4c-42c7-8628-f2cb4236eb01-MODUL"', 
        '"MODUL-0rap62te-0t4c-42c7-8628-f2cb4236eb02-MODUL"'
      )),
    );
  }
  /**
   * @return array
   */
  public function nonArrayUserIdsProvider()
  {
    return array(
      array(null),
      array(array()),
      array('a'),
      array(16),
      array(true),
    );
  }
}