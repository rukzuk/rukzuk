<?php
namespace Cms\Response\Website;

use \Cms\Response\IsResponseData;
use \Cms\Data\Website as WebsiteData;

/**
 * @package      Cms
 * @subpackage   Response
 */

class EditColorscheme implements IsResponseData
{
  public $id = null;

  public $colorscheme = null;

  public function __construct($data)
  {
    if ($data instanceof WebsiteData) {
      $this->setValuesFromData($data);
    }
  }

  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getColorscheme()
  {
    return $this->colorscheme;
  }

  public function setColorscheme($colorscheme)
  {
    $this->colorscheme = $colorscheme;
  }

  protected function setValuesFromData(WebsiteData $data)
  {
    $this->setId($data->getId());
    $this->setColorscheme(\Zend_Json::decode($data->getColorscheme()));
  }
}
