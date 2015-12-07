<?php


namespace Render\MediaCDNHelper\MediaResponse;

class ErrorResponse implements ResponseInterface
{

  /**
   * Returns the http status code
   *
   * @return int
   */
  public function getResponseCode()
  {
    return 500;
  }

  /**
   * Returns the http headers
   *
   * @return array
   */
  public function getHeaders()
  {
    return array();
  }

  /**
   * Output the requested media item
   */
  public function outputBody()
  {
    // do nothing
  }
}
