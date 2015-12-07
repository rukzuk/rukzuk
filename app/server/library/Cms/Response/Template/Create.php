<?php
namespace Cms\Response\Template;

use Cms\Response\IsResponseData;
use Cms\Data\Template as TemplateData;

/**
 * Response Ergebnis fuer Template create Funktion
 *
 * @package      Cms
 * @subpackage   Response
 */

class Create implements IsResponseData
{
  public $id = null;

  public function __construct($data)
  {
    if ($data instanceof TemplateData) {
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
