<?php
namespace Cms\Response;

use Cms\Data\Template as TemplateData;
use Seitenbau\Registry;
use Cms\Response\IsResponseData;

/**
 * Einzelnes Template fuer Response-Darstellung
 *
 * @package      Cms
 * @subpackage   Response
 */

class Template implements IsResponseData
{
  /**
   * @var string
   */
  public $id = null;
  
  /**
   * @var string
   */
  public $websiteId = null;
  
  /**
   * @var string
   */
  public $name = null;
  
  /**
   * @var string $content
   */
  public $content;

  /**
   * @var string
   */
  public $pageType = null;

  public $screenshot;

  /**
   * @param \Cms\Data\Template $data
   */
  public function __construct(TemplateData $data)
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
  
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
  
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
   * @return string
   */
  public function getContent()
  {
    return $this->content;
  }
  
  /**
   * @param string $content
   */
  public function setContent($content)
  {
    if (is_string($content)) {
      $content = \Zend_Json::decode($content, \Zend_Json::TYPE_OBJECT);
    }
    $this->content = $content;
  }

  /**
   * @return string
   */
  public function getPageType()
  {
    return $this->pageType;
  }

  /**
   * @param string $pageType
   */
  public function setPageType($pageType)
  {
    $this->pageType = $pageType;
  }

  public function getScreenshot()
  {
    return $this->screenshot;
  }

  public function setScreenshot()
  {
    $this->screenshot =
      Registry::getConfig()->server->url .
      Registry::getConfig()->screens->url . '/' .
      Registry::getConfig()->request->parameter . '/' .
      urlencode('{"websiteid":"' . $this->getWebsiteId() . '",' .
        '"type":"template","id":"' . $this->getId() . '"}');
  }
  
  /**
   * @param \Cms\Data\Template $data
   */
  protected function setValuesFromData(TemplateData $data)
  {
    $this->setId($data->getId());
    $this->setWebsiteId($data->getWebsiteId());
    $this->setName($data->getName());
    $this->setContent($data->getContent());
    $this->setPageType($data->getPageType());
    $this->setScreenshot();
  }
}
