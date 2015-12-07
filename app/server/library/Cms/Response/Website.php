<?php
namespace Cms\Response;

use \Cms\Data\Website as WebsiteData;
use \Seitenbau\Registry;

/**
 * Einzelne Website als Response
 *
 * @package      Cms
 * @subpackage   Response
 */

class Website implements IsResponseData
{
  public $id = null;

  public $name = null;

  public $description = null;

  public $navigation = null;

  public $screenshot = null;

  public $version = null;

  public $publishingEnabled = false;

  public $publish = null;

  public $publishInfo = null;

  public $colorscheme = null;

  public $resolutions = null;

  public $privileges = null;

  public $home = null;

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

  public function getNavigation()
  {
    return $this->navigation;
  }

  public function setNavigation($navigation)
  {
    $this->navigation = $navigation;
  }

  public function setPrivileges($privileges)
  {
    $this->privileges = $privileges;
  }

  public function setVersion($version)
  {
    $this->version = $version;
  }

  public function getVersion()
  {
    return $this->version;
  }

  public function getScreenshot()
  {
    return $this->screenshot;
  }

  public function setScreenshot()
  {
    if ($this->getId() != '') {
      $this->screenshot =
        Registry::getConfig()->server->url .
        Registry::getConfig()->screens->url . '/' .
        Registry::getConfig()->request->parameter . '/' .
        urlencode('{"websiteid":"' . $this->getId() . '","type":"website"}');
    }
  }

  public function getPublishingEnabled()
  {
    return $this->publishingEnabled;
  }

  public function setPublishingEnabled($publishingEnabled)
  {
    $this->publishingEnabled = (bool)$publishingEnabled;
  }

  public function getPublish()
  {
    return $this->publish;
  }

  public function setPublish($publish)
  {
    $this->publish = new Publish($publish);
  }

  public function getPublishInfo()
  {
    return $this->publishInfo;
  }

  public function setPublishInfo($publishInfo)
  {
    $this->publishInfo = $publishInfo;
  }

  public function getColorscheme()
  {
    return $this->colorscheme;
  }

  public function setColorscheme($colorscheme)
  {
    $this->colorscheme = $colorscheme;
  }

  public function getResolutions()
  {
    return $this->resolutions;
  }

  public function setResolutions($resolutions)
  {
    $this->resolutions = $resolutions;
  }

  public function getHome()
  {
    return $this->home;
  }
  public function setHome($home)
  {
    $this->home = $home;
  }

  protected function setValuesFromData(WebsiteData $data)
  {
    $this->setId($data->getId());
    $this->setName($data->getName());
    $this->setDescription($data->getDescription());
    $this->setNavigation($data->getNavigation());
    $this->setPublishingEnabled($data->getPublishingEnabled());
    $this->setPublish(\Zend_Json::decode($data->getPublish()));
    $this->setColorscheme(\Zend_Json::decode($data->getColorscheme()));
    $this->setResolutions(\Zend_Json::decode($data->getResolutions(), \Zend_Json::TYPE_OBJECT));
    $this->setVersion($data->getVersion());
    $this->setScreenshot();
    $this->setHome($data->getHome());
  }
}
