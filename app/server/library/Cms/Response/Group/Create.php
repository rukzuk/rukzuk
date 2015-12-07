<?php
namespace Cms\Response\Group;

use Cms\Response\IsResponseData;
use Cms\Data\Group as GroupData;

/**
 * @package      Cms
 * @subpackage   Response
 */
class Create implements IsResponseData
{
  public $id = null;

  public function __construct($data)
  {
    if ($data instanceof GroupData) {
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
