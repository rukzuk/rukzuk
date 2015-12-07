<?php
namespace Cms;

/**
 * Error Stack Exception
 *
 * @package    Cms
 */

class ExceptionStackException extends \Exception
{
  protected $exceptions;
  protected $responseData;

  public function __construct(array $exceptions, $responseData = null)
  {
    $this->exceptions = $exceptions;
    $this->responseData = $responseData;
  }

  public function getExceptions()
  {
    return $this->exceptions;
  }

  public function getResponseData()
  {
    return $this->responseData;
  }
}
