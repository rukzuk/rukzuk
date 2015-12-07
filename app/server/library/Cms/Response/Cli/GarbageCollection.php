<?php
namespace Cms\Response\Cli;

use Cms\Response\IsResponseData;

/**
 * @package      Cms
 * @subpackage   Response
 */

class GarbageCollection implements IsResponseData
{
  public $deletedWebsites = array();

  public function __construct(array $gbInfo)
  {
    if (isset($gbInfo['deletedWebsites']) && is_array($gbInfo['deletedWebsites'])) {
      $this->setDeletedWebsites($gbInfo['deletedWebsites']);
    }
  }
  
  /**
   * @param string $deletedWebsites
   */
  public function setDeletedWebsites($deletedWebsites)
  {
    $this->deletedWebsites = $deletedWebsites;
  }
  
  /**
   * @return string
   */
  public function getDeletedWebsites()
  {
    return $this->deletedWebsites;
  }
}
