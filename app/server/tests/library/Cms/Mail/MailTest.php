<?php
namespace Cms;

use Seitenbau\Registry;
use Test\Rukzuk\AbstractTestCase;
use Test\Rukzuk\ConfigHelper;

/**
 * Mail Test
 *
 * @package      Cms
 */

class MailTest extends AbstractTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @test
   * @group library
   */
  public function constructSetExpectedTransport()
  {
    ConfigHelper::mergeIntoConfig(array('mail' => array('transport' => 'file')));
    $mail = new Mail;
    $this->assertInstanceOf('Zend_Mail_Transport_File', $mail->getTransport());

    ConfigHelper::mergeIntoConfig(array('mail' => array('transport' => 'smtp')));
    $mail = new Mail;
    $this->assertInstanceOf('Zend_Mail_Transport_Smtp', $mail->getTransport());

    ConfigHelper::mergeIntoConfig(array('mail' => array('transport' => 'sendmail')));
    $mail = new Mail;
    $this->assertInstanceOf('Zend_Mail_Transport_Sendmail', $mail->getTransport());

    ConfigHelper::mergeIntoConfig(array('mail' => array('transport' => 'void')));
    $mail = new Mail;
    $this->assertInstanceOf('Cms\Mail\Transport\Void', $mail->getTransport());
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Mail\Exception
   */
  public function constructShouldReturnExceptionOnInvalidTransport()
  {
    ConfigHelper::mergeIntoConfig(array('mail' => array('transport' => 'gibtEsNicht')));
    $mail = new Mail;
  }
}