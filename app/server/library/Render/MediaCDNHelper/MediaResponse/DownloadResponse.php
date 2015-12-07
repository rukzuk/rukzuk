<?php


namespace Render\MediaCDNHelper\MediaResponse;

class DownloadResponse extends StreamResponse
{
  protected function addContentDispositionHeader()
  {
    $this->addHeader(
        'Content-Disposition',
        'attachment; filename="'.$this->getFileNameForHeader().'"'
    );
  }
}
