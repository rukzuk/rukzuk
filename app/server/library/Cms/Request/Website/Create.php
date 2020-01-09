<?php

namespace Cms\Request\Website;

use Cms\Request\Base;

class Create extends Base
{
  private $id;

  private $name;

  private $publishingEnabled;

  private $publish;

  private $colorscheme;

  private $resolutions;

  private $home;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setName($this->getRequestParam('name'));
    $this->setPublishingEnabled($this->getRequestParam('publishingenabled'));
    $this->setPublish($this->getRequestParam('publish'));
    $this->setColorscheme($this->getRequestParam('colorscheme'));
    $this->setResolutions($this->getRequestParam('resolutions'));
    $this->setHome($this->getRequestParam('home'));
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function setName($name)
  {
    if (is_null($name)) {
      return;
    }
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setPublish($publish)
  {
    if (is_null($publish)) {
      return;
    }
    $this->publish = \Seitenbau\Json::encode($publish);
  }

  public function getPublish()
  {
    return $this->publish;
  }

  public function setColorscheme($colorscheme)
  {
    if (is_null($colorscheme)) {
      return;
    }
    $this->colorscheme = \Seitenbau\Json::encode($colorscheme);
  }

  public function getColorscheme()
  {
    return $this->colorscheme;
  }

  public function setResolutions($resolutions)
  {
    if (is_null($resolutions)) {
      return;
    }
    $this->resolutions = \Seitenbau\Json::encode($resolutions);
  }

  public function getResolutions()
  {
    return $this->resolutions;
  }

  public function setHome($home)
  {
    if (is_null($home)) {
      return;
    }
    $this->home = $home;
  }

  public function getHome()
  {
    return $this->home;
  }

  public function setPublishingEnabled($publishingEnabled)
  {
    if (is_null($publishingEnabled)) {
      return;
    }
    $this->publishingEnabled = (bool)$publishingEnabled;
  }

  public function getPublishingEnabled()
  {
    return $this->publishingEnabled;
  }
}
