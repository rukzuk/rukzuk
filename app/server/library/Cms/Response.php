<?php
namespace Cms;

use Cms\Response\Base as BaseResponse;
use Cms\Response\Error as ResponseError;

/**
 * Response Grundgeruest fuer Standardabfragen
 *
 * @package      Cms
 * @subpackage   Response
 */

class Response extends BaseResponse
{
  public $success = true;

  public $error = array();

  public function getSuccess()
  {
    return $this->success;
  }

  public function setSuccess($boolean)
  {
    $this->success = ($boolean == true) ? true : false;
  }

  public function addError(ResponseError $error)
  {
    $this->setSuccess(false);
    $this->error[] = $error;
  }

  public function setError(array $error)
  {
    $this->setSuccess(false);
    $this->error = $error;
  }

  public function getError()
  {
    return $this->error;
  }
}
