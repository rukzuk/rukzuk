<?php
namespace Cms\Business\User;

use Cms\Data\User as UserData;
use Cms\Exception;

/**
 * Class UserIsReadOnlyException
 *
 * @package Cms\Business\User
 */
class UserIsReadOnlyException extends Exception
{
  /**
   * @var UserData
   */
  protected $user;

  /**
   * @param int      $code
   * @param string   $method
   * @param string   $linenumber
   * @param UserData $user
   * @param array    $data
   * @param null     $exception
   * @param int      $httpResponseCode
   *
   * @internal param string $filename
   */
  public function __construct(
      $code,
      $method = '',
      $linenumber = '',
      UserData $user,
      $data = array(),
      $exception = null,
      $httpResponseCode = 200
  ) {
    parent::__construct($code, $method, $linenumber, $data, $exception, $httpResponseCode);
    $this->user = $user;
  }

  /**
   * @return UserData
   */
  public function getUser()
  {
    return $this->user;
  }
}
