<?php
namespace Cms\Request\Feedback;

use Cms\Request\Base;

/**
 * Request object for Feedback send
 *
 * @package      Cms
 * @subpackage   Request
 */

class Send extends Base
{
  private $subject;
  
  private $body;
  
  private $email;
  
  private $clientErrors;
  
  private $userAgent;
  
  private $platform;

  protected function setValues()
  {
    $this->setSubject($this->getRequestParam('subject'));
    $this->setBody($this->getRequestParam('body'));
    $this->setEmail($this->getRequestParam('email'));
    $this->setClientErrors($this->getRequestParam('errors'));
    $this->setUserAgent($this->getRequestParam('useragent'));
    $this->setPlatform($this->getRequestParam('platform'));
  }

  public function setSubject($subject)
  {
    $this->subject = $subject;
  }

  public function getSubject()
  {
    return $this->subject;
  }
  
  public function getBody()
  {
    return $this->body;
  }

  public function setBody($body)
  {
    $this->body = $body;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function setEmail($email)
  {
    $this->email = $email;
  }
  
  public function getClientErrors()
  {
    return $this->clientErrors;
  }

  public function setClientErrors($clientErrors)
  {
    $this->clientErrors = $clientErrors;
  }

  public function getUserAgent()
  {
    return $this->userAgent;
  }

  public function setUserAgent($userAgent)
  {
    $this->userAgent = $userAgent;
  }

  public function getPlatform()
  {
    return $this->platform;
  }

  public function setPlatform($platform)
  {
    $this->platform = $platform;
  }
}
