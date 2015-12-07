<?php
namespace Cms\Response\Modul;

use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer Modul Lock
 *
 * @package      Cms
 * @subpackage   Response
 */
class Lock implements IsResponseData
{
  /**
   * @var array
   */
  public $overridable = false;
  /**
   * @param array $lockState
   */
  public function __construct(array $lockState)
  {
    if (isset($lockState['overridable']) && $lockState['overridable']) {
      $this->overridable = true;
    } else {
      $this->overridable = false;
    }
  }
  /**
   * @return array
   */
  public function getOverridable()
  {
    return $this->lockState;
  }
}
