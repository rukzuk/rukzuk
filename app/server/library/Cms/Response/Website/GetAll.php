<?php
namespace Cms\Response\Website;

use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer getAll Funktion
 *
 * @package      Cms
 * @subpackage   Response
 */

class GetAll implements IsResponseData
{
    public $websites = array();

    public function __construct($websites = array())
    {
      $this->setWebsites($websites);
    }

    public function getWebsites()
    {
      return $this->websites;
    }

    protected function setWebsites(array $websites)
    {
      foreach ($websites as $website) {
        $this->websites[] = new \Cms\Response\Website($website);
      }
    }
}
