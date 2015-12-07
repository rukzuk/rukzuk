<?php
namespace Cms\Response\Page;

use Cms\Response\Page;
use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer delete Funktion
 *
 * @package      Cms
 * @subpackage   Response
 */

class Delete implements IsResponseData
{
  public $navigation = null;

  public function __construct($data = null)
  {
    if (is_array($data)) {
      $this->setAttributes($data);
    }
  }

  public function setAttributes($data)
  {
    if (isset($data['navigation'])) {
      $this->setNavigation($data['navigation']);
    }
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
