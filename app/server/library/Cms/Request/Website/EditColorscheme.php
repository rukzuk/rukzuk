<?php
namespace Cms\Request\Website;

use Cms\Request\Base;

/**
 * request for website colorscheme edit
 *
 * @package      Cms
 * @subpackage   Request
 */

class EditColorscheme extends Base
{
  private $id;
  
  private $colorscheme;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setColorscheme($this->getRequestParam('colorscheme'));
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getColorscheme()
  {
    return $this->colorscheme;
  }

  public function setColorscheme($colorscheme)
  {
    if ($colorscheme !== null) {
      $this->colorscheme = \Seitenbau\Json::encode($colorscheme);
    }
  }
}
