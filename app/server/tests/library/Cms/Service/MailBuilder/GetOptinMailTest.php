<?php
namespace Cms\Service\Mailbuilder;

use Cms\Service\MailBuilder as MailBuilderService,
    Test\Seitenbau\ServiceTestCase;
use Seitenbau\Registry;

/**
 * GetOptinMailTest
 *
 * @package      Test
 * @subpackage   Service
 */

class GetOptinMailTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\MailBuilder
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
    $userOptinUriPath = '/bar/';
    $formerOptinUri = $this->changeUserOptinUri($userOptinUriPath);

    $user = new \Cms\Data\User();
    $user->setId('USER-test5421shd-USER');
    $user->setFirstname('John');
    $user->setLastname('Doe');
    $user->setEmail('john.doe@test.de');

    $optin = new \Orm\Entity\OptIn();
    $optin->setCode('testRS01');
    $optin->setUserId($user->getId());
    $optin->setUser($user);

    $mail = $this->service->getOptinMail($optin);

    $this->changeUserOptinUri($formerOptinUri);

    $mailContent = $mail->getBodyText('true');
    $mailContent = str_replace("=\r\n", "", $mailContent);
    $mailContent = str_replace("=\n", "", $mailContent);

    $optinUrl = Registry::getBaseUrl().$userOptinUriPath.'?t=';
    $this->assertTrue(strchr($mailContent, $optinUrl) !== false);
    $this->assertTrue(strchr($mailContent, $optin->getCode()) !== false);
  }
}