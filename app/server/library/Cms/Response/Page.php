<?php
namespace Cms\Response;

use Cms\Data\Page as PageData;
use Seitenbau\Registry;

/**
 * Einzelne Page fuer Response-Darstellung
 *
 * @package      Cms
 * @subpackage   Response
 */

class Page implements IsResponseData
{
  public $id = null;

  public $websiteId = null;

  public $templateId = null;

  public $mediaId = null;

  public $name = null;

  public $description = null;

  public $inNavigation = null;

  public $date = null;

  public $navigationTitle = null;

  public $content = null;

  public $pageType = null;

  public $pageAttributes = null;

  public $screenshot = null;

  public function __construct($data)
  {
    $this->initPageAttributes();
    if ($data instanceof PageData) {
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

  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getTemplateId()
  {
    return $this->templateId;
  }

  public function setTemplateId($templateId)
  {
    $this->templateId = $templateId;
  }

  public function getMediaId()
  {
    return $this->mediaId;
  }

  public function setMediaId($mediaId)
  {
    $this->mediaId = $mediaId;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function setDescription($description)
  {
    $this->description = $description;
  }

  public function getInNavigation()
  {
    return $this->inNavigation;
  }

  public function setInNavigation($inNavigation)
  {
    
    if ($inNavigation == true) {
      $this->inNavigation = true;
    } elseif ($inNavigation == false) {
      $this->inNavigation = false;
    } else {
      $this->inNavigation = null;
    }
  }

  public function getDate()
  {
    return $this->date;
  }

  public function setDate($date)
  {
    $this->date = (int) $date;
  }

  public function getNavigationTitle()
  {
    return $this->navigationTitle;
  }

  public function setNavigationTitle($navigationTitle)
  {
    $this->navigationTitle = $navigationTitle;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function setContent($content)
  {
    if (is_string($content)) {
      $content = \Seitenbau\Json::decode($content, \Zend_Json::TYPE_OBJECT);
    }
    $this->content = $content;
  }


  public function getPageType()
  {
    return $this->pageType;
  }

  public function setPageType($pageType)
  {
    $this->pageType = $pageType;
  }

  public function getPageAttributes()
  {
    return $this->pageAttributes;
  }

  public function setPageAttributes($pageAttributes)
  {
    if (is_string($pageAttributes)) {
      $this->pageAttributes = json_decode($pageAttributes);
    } else {
      $this->pageAttributes = $pageAttributes;
    }
    if (!is_object($this->pageAttributes)) {
      $this->initPageAttributes();
    }
  }

  private function initPageAttributes()
  {
    $this->pageAttributes = new \stdClass();
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
        '"type":"page","id":"' . $this->getId() . '"}');
  }

  protected function setValuesFromData(PageData $data)
  {
    $this->setId($data->getId());
    $this->setWebsiteId($data->getWebsiteid());
    $this->setTemplateId($data->getTemplateid());
    $this->setMediaId($data->getMediaId());
    $this->setName($data->getName());
    $this->setDescription($data->getDescription());
    $this->setInNavigation($data->getInnavigation());
    $this->setDate($data->getDate());
    $this->setNavigationTitle($data->getNavigationtitle());
    $this->setContent($data->getContent());
    $this->setPageType($data->getPageType());
    $this->setPageAttributes($data->getPageAttributes());
    $this->setScreenshot();
  }
}
