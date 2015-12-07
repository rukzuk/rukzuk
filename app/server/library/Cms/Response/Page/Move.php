<?php
namespace Cms\Response\Page;

use Cms\Response\Page;
use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer move Funktion
 *
 * @package      Cms
 * @subpackage   Response
 */

class Move implements IsResponseData
{
  public $navigation = null;

  public function __construct($navigation)
  {
    if ($navigation) {
      $this->setNavigation($navigation);
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
