<?php
namespace Cms\Response\Cli;

use Cms\Response\User;
use Cms\Response\IsResponseData;
use Cms\Data\User as UserData;
use Cms\Data\OptIn as OptInData;

/**
 * @package      Cms
 * @subpackage   Response
 */

class RegisterUser implements IsResponseData
{
  public $user = null;
  
  public $token = null;
  
  public $tokenUrl = null;

  public function __construct($data)
  {
    if ($data['user'] instanceof UserData) {
      $this->setUser($data['user']);
    }
    
    if (isset($data['token'])) {
      $this->setToken($data['token']);
    }
    if (isset($data['tokenUrl'])) {
      $this->setTokenUrl($data['tokenUrl']);
    }
  }
  
  protected function setUser(UserData $user)
  {
    $this->user = new User($user);
  }
  
  public function getUser()
  {
    return $this->user;
  }

  public function getToken()
  {
    return $this->token;
  }

  public function setToken($token)
  {
    $this->token = $token;
  }

  public function getTokenUrl()
  {
    return $this->tokenUrl;
  }

  public function setTokenUrl($tokenUrl)
  {
    $this->tokenUrl = $tokenUrl;
  }
}
