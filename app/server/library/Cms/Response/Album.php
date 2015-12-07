<?php
namespace Cms\Response;

use Cms\Data;
use Seitenbau\Registry;
use Cms\Response\IsResponseData;

/**
 * Einzelnes Album fuer Response-Darstellung
 *
 * @package      Cms
 * @subpackage   Response
 */
class Album implements IsResponseData
{
  /**
   * @var string
   */
  public $id;
  
  /**
   * @var string
   */
  public $websiteId;
  
  /**
   * @var string
   */
  public $name;

  /**
   * @param Cms\Data\Album $data
   */
  public function __construct(Data\Album $data)
  {
    $this->setValuesFromData($data);
  }
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
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  
  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  
  /**
   * @param $data
   */
  protected function setValuesFromData(Data\Album $data)
  {
    $this->setId($data->getId());
    $this->setWebsiteId($data->getWebsiteId());
    $this->setName($data->getName());
  }
}
