<?php


namespace Render\MediaCDNHelper\MediaResponse;

class NotFoundResponse extends ErrorResponse
{
  /**
   * @return int
   */
  public function getResponseCode()
  {
    return 404;
  }
}
