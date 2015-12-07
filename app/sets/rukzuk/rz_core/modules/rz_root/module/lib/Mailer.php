<?php


namespace Rukzuk\Modules;

use Render\APIs\APIv1\HeadAPI;

require_once(__DIR__ . '/MailerException.php');

/**
 * Class Mailer
 *
 * Usage:
 * <code>
 *   // use php mail function
 *   $mailer = new Mailer($api);
 *
 *   // use SMTP server
 *   $mailer = new Mailer($renderAPI, array(
 *     'transport' => 'smtp',
 *     'host' => 'smtp.myhost.com',
 *     'username' => 'my.username',
 *     'password' => '*****',
 *     'secure' => 'ssl',
 *   ));
 * </code>
 *
 * @package Rukzuk\Modules
 */
class Mailer
{
  /**
   * @var \Nette\Mail\IMailer
   */
  private $mailer = null;

  /**
   * @var \Nette\Mail\Message
   */
  private $message = null;

  /**
   * @param HeadAPI $api
   * @param array     $options
   *
   * @option  string transport  mail|smtp
   * @option  string host
   * @option  string username
   * @option  string password
   * @option  string port
   */
  public function __construct(HeadAPI $api, array $options = array())
  {
    try {
      $this->createMailer($api, $options);
      $this->createMessage();
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $email
   * @param string $name
   */
  public function setFrom($email, $name = null)
  {
    try {
      $this->message->setFrom($email, $name);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $email
   * @param string $name
   */
  public function addReplyTo($email, $name = null)
  {
    try {
      $this->message->addReplyTo($email, $name);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $email
   * @param string $name
   */
  public function addTo($email, $name = null)
  {
    try {
      $this->message->addTo($email, $name);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $email
   * @param string $name
   */
  public function addCc($email, $name = null)
  {
    try {
      $this->message->addCc($email, $name);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $email
   * @param string $name
   */
  public function addBcc($email, $name = null)
  {
    try {
      $this->message->addBcc($email, $name);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $email
   */
  public function setReturnPath($email)
  {
    try {
      $this->message->setReturnPath($email);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param int $priority
   */
  public function setPriority($priority)
  {
    try {
      $this->message->setPriority($priority);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $subject
   */
  public function setSubject($subject)
  {
    try {
      $this->message->setSubject($subject);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $body
   */
  public function setBody($body)
  {
    try {
      $this->message->setBody($body);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $html
   */
  public function setHtmlBody($html)
  {
    try {
      $this->message->setHtmlBody($html, false);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $file
   * @param string $content
   * @param string $contentType
   *
   * @return string content id
   */
  public function addEmbeddedFile($file, $content = null, $contentType = null)
  {
    try {
      $mailPart = $this->message->addEmbeddedFile($file, $content, $contentType);
      return $this->getIdFromMailPart($mailPart);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * @param string $file
   * @param string $content
   * @param string $contentType
   *
   * @return string content id
   */
  public function addAttachment($file, $content = null, $contentType = null)
  {
    try {
      $mailPart = $this->message->addAttachment($file, $content, $contentType);
      return $this->getIdFromMailPart($mailPart);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * Sends the email.
   */
  public function send()
  {
    try {
      $this->mailer->send($this->message);
    } catch (\Exception $e) {
      throw new MailerException($e->getMessage());
    }
  }

  /**
   * Creates the internal mailer object.
   *
   * @param HeadAPI $renderApi
   * @param array     $options
   */
  private function createMailer(HeadAPI $api, array $options)
  {
    $transport = $this->getTransport($options);
    switch ($transport) {
      case 'smtp':
        $this->mailer = new \Nette\Mail\SmtpMailer($options);
        break;
      case 'mail':
      default:
        $this->mailer = new \Nette\Mail\SendmailMailer();
        break;
    }
  }

  /**
   * Creates internal message object.
   */
  private function createMessage()
  {
    $this->message = new \Nette\Mail\Message();
  }

  /**
   * @param \Nette\Mail\MimePart $mailPart
   *
   * @return string
   */
  private function getIdFromMailPart(\Nette\Mail\MimePart $mailPart)
  {
    return substr($mailPart->getHeader('Content-ID'), 1, -1);
  }

  /**
   * @param array $options
   *
   * @return string
   */
  private function getTransport(array $options)
  {
    if (isset($options['transport']) && is_string($options['transport'])) {
      $transport = $options['transport'];
    } else {
      $transport = 'mail';
    }
    return strtolower($transport);
  }

}