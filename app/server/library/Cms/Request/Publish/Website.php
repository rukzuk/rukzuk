<?php
namespace Cms\Request\Publish;

use Cms\Request\Base;

/**
 * Request object for Publish WebsiteById
 *
 * @package      Cms
 * @subpackage   Request
 */
class Website extends Base
{
  private $id = null;

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
