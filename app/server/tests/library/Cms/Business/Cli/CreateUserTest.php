<?php
namespace Cms\Business\Cli;

use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\ServiceTestCase as ServiceTestCase,
    Seitenbau\Registry as Registry,
    Cms\Business\Cli as CliBusiness,
    Cms\Business\Website as WebsiteBusiness,
    Cms\Business\Group as GroupBusiness,
    Cms\Business\User as UserBusiness,
    Test\Seitenbau\Optin as OptinTestHelper,
    Seitenbau\FileSystem as FS;

/**
 * Cli business createUser Test
 */
class CreateUserTest extends ServiceTestCase
{
  const BACKUP_CONFIG = true;

  private $mailsFromFileTransportDirectory = '/tmp';

  /**
   * @var \Cms\Business\Cli
   */
  private $business;
  
  protected function setUp()
  {
    parent::setUp();

    OptinTestHelper::clearMailsFromFileTransports(
      $this->mailsFromFileTransportDirectory
    );

    $this->business = new CliBusiness('Cli');
  }
 
  protected function tearDown()
  {
    OptinTestHelper::clearMailsFromFileTransports(
      $this->mailsFromFileTransportDirectory
    );

    parent::tearDown();
  }
  
  /**
   * @test
   * @group library
   */
  public function test_createUserSuccess()
  {
    $userCreateValues = array(
      'email'       => 'phpunittest_1@rukzuk.com',
      'lastname'    => 'phpunit_1',
      'firstname'   => 'test_1',
      'isSuperuser' => true,
      'isDeletable' => false,
    );
    $user = $this->business->createUser($userCreateValues, false);
    $this->assertUserCreatedSuccessfully($userCreateValues, $user);
    $this->asserUserExists($user->getId(), $userCreateValues);
  }
  
  /**
   * @test
   * @group library
   */
  public function test_createUserShouldSendRegisterMailAsExpected()
  {
    ConfigHelper::mergeIntoConfig(array('user' => array('mail' => array('optin' => array('from' => array(
      'adress' => 'rukzuk-testrun-init-system@rukzuk.com',
      'name' => 'rukzuk-testrun-init-system',
    ))))));

    $userCreateValues = array(
      'email'       => 'phpunittest_2@rukzuk.com',
      'lastname'    => 'phpunit_2',
      'firstname'   => 'test_2',
      'isSuperuser' => true,
      'isDeletable' => false,
    );
    $user = $this->business->createUser($userCreateValues, true);
    $this->assertUserCreatedSuccessfully($userCreateValues, $user);
    $this->asserUserExists($user->getId(), $userCreateValues);
    $this->assertRegisterMailSend($user);
  }
  
  protected function assertUserCreatedSuccessfully($userCreateValues, $user)
  {
    $this->assertInstanceOf('\Cms\Data\User', $user);
    $this->assertInternalType('string', $user->getId());
    $this->assertEquals($userCreateValues['email'], $user->getEmail());
    $this->assertEquals($userCreateValues['lastname'], $user->getLastname());
    $this->assertEquals($userCreateValues['firstname'], $user->getFirstname());
    if (isset($userCreateValues['gender'])) {
      $this->assertEquals($userCreateValues['gender'], $user->getGender());
    } else {
      $this->assertNull($user->getGender());
    }
    $this->assertEquals($userCreateValues['isSuperuser'], $user->isSuperuser());
    $this->assertEquals($userCreateValues['isDeletable'], $user->isDeletable());
  }
  
  protected function asserUserExists($userId, $userCreateValues)
  {
    $userBusiness = new UserBusiness('User');
    $user = $userBusiness->getById($userId);
    $this->assertUserCreatedSuccessfully($userCreateValues, $user);
  }

  protected function assertRegisterMailSend($user)
  {
    $this->assertEquals(1, OptinTestHelper::getMailsCount($this->mailsFromFileTransportDirectory));
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
    $this->assertEquals(\Orm\Entity\Optin::MODE_REGISTER, $optin->getMode());

    $this->assertEquals($optin->getUser()->getId(), $user->getId());
    
    $this->assertRegisterMailSendSuccessfully($optin, $optin->getUser(), $actualMailContent);
  }

  protected function assertRegisterMailSendSuccessfully($optin, $user, $actualMailContent)
  {
    $config = Registry::getConfig();
    $expectedMailContent = OptinTestHelper::createMailContentFromMailTemplate(
      FS::joinPath(realpath($config->test->files->directory), 'mails', 'optin.txt'),
      array(
        '@@OPTIN_CODE@@'      => $optin->getCode(),
        '@@WEBHOST@@'         => Registry::getBaseUrl(),
        '@@FROM_NAME@@'       => $config->user->mail->optin->from->name,
        '@@FROM_ADRESS@@'     => $config->user->mail->optin->from->address,
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