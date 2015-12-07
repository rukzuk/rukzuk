<?php


namespace Render\MediaCDNHelper\MediaResponse;

class IconResponse extends ImageResponse
{
  /**
   * @return string
   */
  protected function getOriginalFilePath()
  {
    return $this->getMediaItem()->getIconFilePath();
  }

  /**
   * @return string
   */
  protected function getFileNameForHeader()
  {
    $filename = preg_replace(
        '/[^0-9a-z_\-+\.]/i',
        '',
        basename($this->getOriginalFilePath())
    );
    return utf8_decode($filename);
  }

  /**
   * @return string
   */
  protected function getCacheFilePath()
  {
    return $this->getMediaCache()->getCacheFilePath(
        $this->getMediaItem(),
        $this->getOperations(),
        true
    );
  }
}
