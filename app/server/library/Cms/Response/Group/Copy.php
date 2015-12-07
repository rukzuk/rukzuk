<?php
namespace Cms\Response\Group;

use Cms\Response\IsResponseData;
use \Orm\Entity\Group as OrmGroup;

/**
 *
 * @package      Cms
 * @subpackage   Response
 */
class Copy implements IsResponseData
{
  public $id = null;

  public function __construct($id)
  {
    $this->setId($id);
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
