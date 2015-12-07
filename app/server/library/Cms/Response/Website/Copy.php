<?php
namespace Cms\Response\Website;

use Cms\Response\IsResponseData;
use Cms\Data\Website as WebsiteData;

/**
 * Response Ergebnis fuer copy Funktion
 *
 * @package      Cms
 * @subpackage   Response
 */

class Copy implements IsResponseData
{
  public $id = null;

  public function __construct($data)
  {
    if ($data instanceof WebsiteData) {
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
