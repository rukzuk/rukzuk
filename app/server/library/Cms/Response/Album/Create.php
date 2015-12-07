<?php
namespace Cms\Response\Album;

use Cms\Response\Album;
use Cms\Response\IsResponseData;
use Cms\Data;

/**
 * Album create response
 *
 * @package      Cms
 * @subpackage   Response
 */
class Create implements IsResponseData
{
  public $id = null;

  public function __construct($data)
  {
    if ($data instanceof Data\Album) {
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
