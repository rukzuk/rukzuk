<?php
namespace Cms\Response\Cli;

use Cms\Response\IsResponseData;

/**
 * @package      Cms
 * @subpackage   Response
 */

class UpdateSystem implements IsResponseData
{
  public $db = null;

  public function __construct($data)
  {
    if (isset($data['db']) && is_array($data['db'])) {
      $this->setDb($data['db']);
    }
  }
  
  protected function setDb($db)
  {
    $this->db = $db;
  }
  
  public function getDb()
  {
    return $this->db;
  }
}
