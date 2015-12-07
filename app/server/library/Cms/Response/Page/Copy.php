<?php
namespace Cms\Response\Page;

use Cms\Response\Page;
use Cms\Data\Page as PageData;
use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer Page copy Funktion
 *
 * @package      Cms
 * @subpackage   Response
 */

class Copy implements IsResponseData
{
  public $id = null;

  public $navigation = null;

  public function __construct($data)
  {
    if ($data instanceof PageData) {
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

  public function getNavigation()
  {
    return $this->navigation;
  }

  public function setNavigation($navigation)
  {
    $this->navigation = $navigation;
  }
}
