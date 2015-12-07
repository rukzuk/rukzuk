<?php
namespace Cms\Request\User;

use Cms\Request\Base;

/**
 * GetById Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class GetById extends Base
{
  /**
   * @var string
   */
  private $id;
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
  }
}
