<?php
namespace Cms\Response\TemplateSnippet;

use Cms\Response\IsResponseData;
use Cms\Data\TemplateSnippet as TemplateSnippetData;

/**
 * Response Ergebnis fuer TemplateSnippet create Funktion
 *
 * @package      Cms
 * @subpackage   Response
 */

class Create implements IsResponseData
{
  public $id = null;

  public function __construct($data)
  {
    if ($data instanceof TemplateSnippetData) {
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
