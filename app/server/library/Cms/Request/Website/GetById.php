<?php
namespace Cms\Request\Website;

use Cms\Request\Base;

/**
 * Request object for Website getById
 *
 * @package      Cms
 * @subpackage   Request
 */

class GetById extends Base
{
  private $id;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }
}
