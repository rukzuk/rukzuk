<?php
namespace Cms\Response\Cli;

use Cms\Response\User;
use Cms\Response\IsResponseData;
use Cms\Data\User as UserData;

/**
 * @package      Cms
 * @subpackage   Response
 */

class InitSystem implements IsResponseData
{
  public $id = null;

  public function __construct($data)
  {
    if ($data instanceof UserData) {
      $this->setId($data->getId());
    }
  }

  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = $id;
  }
}
