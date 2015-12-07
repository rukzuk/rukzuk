<?php
namespace Application\Controller\User;

use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\Cms\Response as Response;
use Test\Seitenbau\ControllerTestCase;
use Test\Seitenbau\Optin as OptinTestHelper;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
/**
 * RenewTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class RenewPasswordTest extends ControllerTestCase
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
   * @dataProvider invalidEmailsProvider
   */
  public function renewPasswordShouldReturnValidationErrorForInvalidEmails($email)
  {
    $renewRequest = '/user/renewpassword/params/'.urlencode(json_encode(array('email' => $email)));
    
    $this->dispatch($renewRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals('email', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function renewPasswordShouldReturnErrorForUnknownEmail()
  {
    $nonExistingEmail = 'test.dog@dogpound.de';
    $renewRequest = sprintf(
      '/user/renewpassword/params/{"email":"%s"}',
      $nonExistingEmail
    );
    
    $this->dispatch($renewRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertEquals(1040, $responseError[0]->code);
  }
  /**
   * @test
   * @group integration
   */
  public function renewPasswordShouldNotBeRejectedWhenUserIsNotLoggedIn()
  {
    $this->activateGroupCheck();
    
    $email = 'renew0@sbcms.de';
    $renewRequest = sprintf(
      '/user/renewpassword/params/{"email":"%s"}',
      $email
    );
    
    $this->dispatch($renewRequest);
    
    $this->deactivateGroupCheck();
    
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
  }
  /**
   * @test
   * @group integration
   * @expectedException \Cms\Exception
   */
  public function renewPasswordShouldNotCreatePasswordCodeWhenUserMailIsInActive()
  {
    $formerUserMailActiveStatus = OptinTestHelper::changeConfiguredUserMailActiveStatus(0);
    
    $email = 'renew0@sbcms.de';
    $renewRequest = sprintf(
      '/user/renewpassword/params/{"email":"%s"}',
      $email
    );
    
    $this->dispatch($renewRequest);
   
    OptinTestHelper::changeConfiguredUserMailActiveStatus(
      $formerUserMailActiveStatus
    );
    
    $optinService = new \Cms\Service\Optin('Optin');
    $optinService->getDao()->getByUserId('USER-ren00gc0-b7a3-4599-b396-94c8bb6c10d9-USER');
  }
  /**
   * @test
   * @group integration
   */
  public function renewPasswordShouldNotSendRenewMailWhenUserMailIsInActive()
  {
    $formerUserMailActiveStatus = OptinTestHelper::changeConfiguredUserMailActiveStatus(0);
    
    $email = 'renew0@sbcms.de';
    $renewRequest = sprintf(
      '/user/renewpassword/params/{"email":"%s"}',
      $email
    );
    
    $this->dispatch($renewRequest);
    
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
  public function renewPasswordShouldCreatePasswordCodeForUser()
  {
    $email = 'renew0@sbcms.de';
    $renewRequest = sprintf(
      '/user/renewpassword/params/{"email":"%s"}',
      $email
    );
    
    $this->dispatch($renewRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    
    $this->assertEquals(
      1, 
      OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory)
    );
    
    $mailsContent = OptinTestHelper::getFileMailsContent(
      $this->mailsFromFileTransportDirectory
    );
    $actualMailContent = $mailsContent[0];
    $actualRenewCode = OptinTestHelper::getRenewCodeFromMailContent(
      $actualMailContent
    );
    
    $optinService = new \Cms\Service\Optin('Optin');
    $renew = $optinService->getDao()->getByCode($actualRenewCode);
    
    $this->assertEquals($actualRenewCode, $renew->getCode());
    $this->assertEquals($renew->getUser()->getId(), $renew->getUserid());
    $this->assertEquals(\Orm\Entity\Optin::MODE_PASSWORD, $renew->getMode());
  }
  /**
   * @test
   * @group integration
   */
  public function renewPasswordShouldSendExpectedRenewMailsCount()
  {
    $email = 'renew0@sbcms.de';
    $renewRequest = sprintf(
      '/user/renewpassword/params/{"email":"%s"}',
      $email
    );
    
    $this->dispatch($renewRequest);
    
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    
    $this->assertEquals(
      1, 
      OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory)
    );
  }

  /**
   * @test
   * @group integration
   */
  public function renewPasswordShouldSendRenewMailWithExpectedContent()
  {
    // ARRANGE
    ConfigHelper::removeOwner();
    $config = Registry::getConfig();
    $expectedFromUser = array(
      'email' => $config->user->mail->renew->password->from->address,
      'name' => $config->user->mail->renew->password->from->name,
    );
    $userId = 'USER-ren00gc0-b7a3-4599-b396-94c8bb6c10d9-USER';
    $userBusiness = new \Cms\Business\User('User');
    $user = $userBusiness->getById($userId);
    $renewRequest = sprintf(
      '/user/renewpassword/params/{"email":"%s"}',
      $user->getEmail()
    );

    // ACT
    $this->dispatch($renewRequest);

    // ASSERT
    $this->getValidatedSuccessResponse();

    $this->assertEquals(1, OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory));
    
    $mailsContent = OptinTestHelper::getFileMailsContent(
      $this->mailsFromFileTransportDirectory
    );
    $actualMailContent = $mailsContent[0];
    $actualRenewCode = OptinTestHelper::getRenewCodeFromMailContent(
      $actualMailContent
    );
    $optinService = new \Cms\Service\Optin('Optin');
    $optin = $optinService->getDao()->getByCode($actualRenewCode);

    $this->assertOptinMailSendSuccessfully($optin, $user, $expectedFromUser, $actualMailContent);
  }


  /**
   * @test
   * @group integration
   */
  public function test_renewPasswordShouldSendRenewMailFromOwnerAdress()
  {
    // ARRANGE
    $expectedOwner = ConfigHelper::setOwner();
    $expectedFromUser = array(
      'email' => $expectedOwner['email'],
      'name' => $expectedOwner['firstname'] . ' ' . $expectedOwner['lastname'],
    );
    $userId = 'USER-ren00gc0-b7a3-4599-b396-94c8bb6c10d9-USER';
    $userBusiness = new \Cms\Business\User('User');
    $user = $userBusiness->getById($userId);

    // ACT
    $this->dispatchWithParams('user/renewpassword',  array(
      'email' => $user->getEmail(),
    ));

    // ASSERT
    $this->getValidatedSuccessResponse();
    $this->assertEquals(1, OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory));
    $mailsContent = OptinTestHelper::getFileMailsContent($this->mailsFromFileTransportDirectory);
    $actualMailContent = $mailsContent[0];
    $actualRenewCode = OptinTestHelper::getRenewCodeFromMailContent($actualMailContent);
    $optinService = new \Cms\Service\Optin('Optin');
    $optin = $optinService->getDao()->getByCode($actualRenewCode);

    $this->assertOptinMailSendSuccessfully($optin, $user, $expectedFromUser, $actualMailContent);
  }

  /**
   * @test
   * @group  bugs
   * @ticket SBCMS-788
   */
  public function renewPasswordShouldBeAbleWhenRegisterIsAlreadySet()
  {
    $email = 'renew2@sbcms.de';
    $renewRequest = sprintf(
      '/user/renewpassword/params/{"email":"%s"}',
      $email
    );
    
    $this->dispatch($renewRequest);
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
  }
  
  /**
   * @return array
   */
  public function invalidEmailsProvider()
  {
    return array(
      array(null),
      array(array()),
      array('a'),
      array(16),
      array('www.heise.de'),
    );
  }

  protected function assertOptinMailSendSuccessfully($optin, $user, $expectedFromUser,
                                                     $actualMailContent)
  {
    $expectedMailContent = OptinTestHelper::createMailContentFromMailTemplate(
      FS::joinPath(realpath(Registry::getConfig()->test->files->directory), 'mails', 'renew.txt'),
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
}