<?php
namespace Test\Rukzuk;


class MediaImageMock extends GetSetMock
{

  private $mediaItem;
  private $modifications = array();

  public function __construct($data, $mediaItem)
  {
    parent::__construct($data);
    $this->mediaItem = $mediaItem;
  }

  public function resetOperations()
  {
    $this->setWidth($this->getOriginalWidth());
    $this->setHeight($this->getOriginalHeight());
    $this->modifications = array();
  }

  public function getMediaItem()
  {
    return $this->mediaItem;
  }

  public function resizeScale($w = null, $h = null)
  {
    $this->addMod('resizeScale', array($w, $h));
    return $this;
  }

  public function resizeCenter($w, $h)
  {
    $this->addMod('resizeCenter', array($w, $h));
    return $this;
  }

  public function crop($x, $y, $w, $h)
  {
    $this->addMod('crop', array($x, $y, $w, $h));
    return $this;
  }

  public function setQuality($q)
  {
    $this->addMod('quality', array($q));
    return $this;
  }

  public function getUrl()
  {
    $url = '';
    foreach ($this->modifications as $mod) {
      $url .= '/' . $mod[0] . '(' . implode(',', $mod[1]) . ')';
    }
    $url .= '/' . parent::getUrl();
    return $url;
  }

  private function addMod($name, $values = array())
  {
    $this->modifications[] = array($name, array_filter($values, function ($val) {
      return $val !== null;
    }));
  }
}
