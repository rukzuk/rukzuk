<?php
namespace Cms\Response\Lock;

use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer GetAll
 *
 * @package      Cms
 * @subpackage   Response
 */
class GetAll implements IsResponseData
{
  /**
   * @var array
   */
  public $locks = array();

  /**
   * @param array $locks
   */
  public function __construct(array $locks = array())
  {
    $this->setLocks($locks);
  }

  /**
   * @return array
   */
  public function getLocks()
  {
    return $this->locks;
  }
  
  /**
   * @param array $locks
   */
  protected function setLocks(array $locks)
  {
    foreach ($locks as $lock) {
      $this->locks[] = new \Cms\Response\Lock($lock);
    }
  }
}
