<?php


namespace Render\APIs\APIv1;

class MediaIcon extends MediaImage
{

  protected function getFilePath()
  {
    $mediaId = $this->getMediaItem()->getId();
    $infoStorage = $this->getMediaContext()->getMediaInfoStorage();
    return $infoStorage->getItem($mediaId)->getIconFilePath();
  }

  public function getUrl()
  {
    return $this->getMediaContext()->getMediaInfoStorage()->getIconUrl(
        $this->getMediaId(),
        $this->getImageOperations()
    );
  }
}
