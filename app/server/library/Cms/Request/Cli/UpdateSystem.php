<?php
namespace Cms\Request\Cli;

use Cms\Request\Base;

/**
 * UpdateSystem Request
 *
 * @package      Cms
 * @subpackage   Request
 */

class UpdateSystem extends Base
{
  /**
   * @var string
   */
  private $version;
  
  /**
   * @return string
   */
  public function getVersion()
  {
    return $this->version;
  }
  /**
   * @param string $version
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }
  protected function setValues()
  {
    $this->setVersion($this->getRequestParam('version'));
  }
}
