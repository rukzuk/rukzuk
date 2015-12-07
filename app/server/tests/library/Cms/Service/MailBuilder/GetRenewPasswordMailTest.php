<?php
namespace Cms\Service\Optin;

use Cms\Service\MailBuilder as MailBuilderService,
    Test\Seitenbau\ServiceTestCase;
use Seitenbau\Registry;

/**
 * GetRenewPasswordMailTest
 *
 * @package      Test
 * @subpackage   Service
 */
class GetRenewPasswordMailTest extends ServiceTestCase
{
  /**
   * @var \Cms\Service\MailBuilder
   */
  private $service;
  
  public function setUp()
  {
    parent::setUp();

    $this->service = new MailBuilderService();
  }
  
  /**
   * @test
   * @group library
   */
  public function mailTextShouldContainConfiguredUri()
  {
    $userRenewPasswordUriPath = '/bar/';
    $formerRenewPasswordUri = $this->changeUserRenewPasswordUri(
      $userRenewPasswordUriPath
    );

    $user = new \Cms\Data\User();
    $user->setId('USER-test5421shd-USER');
    $user->setFirstname('John');
    $user->setLastname('Doe');
    $user->setEmail('john.doe@test.de');
    
    $optin = new \Orm\Entity\OptIn();
    $optin->setCode('testRS01');
    $optin->setUserId($user->getId());
    $optin->setUser($user);
    
    $mail = $this->service->getRenewPasswordMail($optin);
    
    $this->changeUserRenewPasswordUri($formerRenewPasswordUri);
    
    $mailContent = $mail->getBodyText('true');
    $mailContent = str_replace("=\r\n", "", $mailContent);
    $mailContent = str_replace("=\n", "", $mailContent);

    $renewUrl = Registry::getBaseUrl().$userRenewPasswordUriPath.'?t=';
    $this->assertTrue(strchr($mailContent, $renewUrl) !== false);
    $this->assertTrue(strchr($mailContent, $optin->getCode()) !== false);
  }
}