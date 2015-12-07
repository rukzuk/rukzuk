<?php
namespace Cms\Request\Cli;

use Cms\Request\Base;

/**
 * RegisterUser Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class RegisterUser extends Base
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
