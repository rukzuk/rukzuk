<?php
namespace Cms\Response\Website;

use \Cms\Response\IsResponseData;
use \Cms\Data\Website as WebsiteData;

/**
 * @package      Cms
 * @subpackage   Response
 */

class EditResolutions implements IsResponseData
{
  public $id = null;

  public $resolutions = null;

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

  public function getResolutions()
  {
    return $this->resolutions;
  }

  public function setResolutions($resolutions)
  {
    $this->resolutions = $resolutions;
  }
  protected function setValuesFromData(WebsiteData $data)
  {
    $this->setId($data->getId());
    $this->setResolutions(\Zend_Json::decode($data->getResolutions(), \Zend_Json::TYPE_OBJECT));
  }
}
