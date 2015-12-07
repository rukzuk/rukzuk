<?php
namespace Cms\Request\Uuid;

use Cms\Request\Base;

/**
 * Request object for Uuid getUuids
 *
 * @package      Cms
 * @subpackage   Request\Uuid
 */
class GetUuids extends Base
{
  /**
   * @var string
   */
  private $count;

  protected function setValues()
  {
    $this->setCount($this->getRequestParam('count'));
  }
  /**
   * @param string $count
   */
  public function setCount($count)
  {
    $this->count = $count;
  }
  /**
   * @return integer
   */
  public function getCount()
  {
    return (int) $this->count;
  }
}
