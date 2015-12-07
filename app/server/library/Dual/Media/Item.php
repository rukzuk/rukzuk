<?php
namespace Dual\Media;

use Dual\Render\RenderContext;

/**
 * Class Item
 * @package Dual\Media
 */
class Item
{
  private $mediaContext = null;
  private $id;

  public function __construct($id)
  {
    $this->mediaContext = RenderContext::getMediaContext();
    $this->id = $id;
  }

  public function reset()
  {
    // DO NOTHING HERE
  }

  public function getUrl()
  {
    return $this->mediaContext->getMediaInfoStorage()->getUrl($this->id);
  }

  public function getDownloadUrl()
  {
    return $this->mediaContext->getMediaInfoStorage()->getDownloadUrl($this->id);
  }

  public function getIconUrl($maxWidth = null, $maxHeight = null)
  {
    $op = array();
    if (!is_null($maxWidth) || !is_null($maxHeight)) {
      $op[] = array('maxsize', (int)$maxWidth, (int)$maxHeight);
    }
    return $this->mediaContext->getMediaInfoStorage()->getPreviewUrl($this->id, $op);
  }

  public function getImage($icon = false)
  {
    return new Image($this->id, $icon);
  }
}
