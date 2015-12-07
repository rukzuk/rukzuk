<?php
namespace Cms\Response\Page;

use Cms\Response\Page;
use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer create Funktion
 *
 * @package      Cms
 * @subpackage   Response
 */

class Create implements IsResponseData
{
  public $id = null;

  public $navigation = null;

  public function __construct($data)
  {
    if (is_array($data)) {
      $this->setAttributes($data);
    }
  }

  public function setAttributes($data)
  {
    if (isset($data['id'])) {
      $this->setId($data['id']);
    }
    if (isset($data['navigation'])) {
      $this->setNavigation($data['navigation']);
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
