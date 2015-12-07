<?php
namespace Cms;

use \Cms\Error as CmsError;

/**
 * Cms Exception
 *
 * @package      Cms
 */

class Exception extends \Exception
{
  protected $data;

  protected $priority;

  protected $logid;

  protected $httpResponseCode;

  protected $exception;

  /**
   * @param int $code
   * @param string  $filename
   * @param string  $linenumber
   */
  public function __construct(
      $code,
      $method = '',
      $linenumber = '',
      $data = array(),
      $exception = null,
      $httpResponseCode = 200
  ) {
    if (isset($exception)) {
      $this->file = $exception->getFile();
      $this->line = $exception->getLine();
    } else {
      $this->file = $method;
      $this->line = $linenumber;
    }

    $this->data = $data;
    $this->code = $code;
    $this->message = $this->getMessageByCode($code, $data);
    $this->priority = $this->getPriorityByCode($code);
    $this->logid = rand(100000, 999999);
    $this->httpResponseCode = $httpResponseCode;
    $this->exception = $exception;
  }

  public function getData()
  {
    return $this->data;
  }

  public function getLogid()
  {
    return $this->logid;
  }

  public function getPriority()
  {
    return $this->priority;
  }

  public function getHttpResponseCode()
  {
    return $this->httpResponseCode;
  }

  public function getException()
  {
    return $this->exception;
  }

  protected function getMessageByCode($code, $data = array())
  {
    return CmsError::getMessageByCode($code, $data);
  }

  protected function getPriorityByCode($code)
  {
    return CmsError::getPriorityByCode($code);
  }
}
