<?php
namespace Cms\Response\User;

use Cms\Response\IsResponseData;
use Cms\Response\Userinfo;

/**
 * @package      Cms
 * @subpackage   Response
 */

class Info implements IsResponseData
{
  public $userInfo = null;

  public function __construct($data)
  {
    if (is_array($data)) {
      $this->setUserinfo($data);
    }
  }

  public function setUserinfo(array $data)
  {
    $this->userInfo = new Userinfo($data);
  }
}
