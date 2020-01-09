<?php
namespace Cms\Request\Website;

use Cms\Request\Base;

/**
 * @package      Cms
 * @subpackage   Request
 */

class EditResolutions extends Base
{
  private $id;
  
  private $resolutions;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setResolutions($this->getRequestParam('resolutions'));
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getResolutions()
  {
    return $this->resolutions;
  }

  public function setResolutions($resolutions)
  {
    if ($resolutions !== null) {
      $this->resolutions = \Seitenbau\Json::encode($resolutions);
    }
  }
}
