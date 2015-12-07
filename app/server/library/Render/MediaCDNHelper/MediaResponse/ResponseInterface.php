<?php


namespace Render\MediaCDNHelper\MediaResponse;

interface ResponseInterface
{
  /**
   * Returns the http status code
   *
   * @return int
   */
  public function getResponseCode();

  /**
   * Returns the http headers
   *
   * @return array
   */
  public function getHeaders();

  /**
   * Output the requested media item
   */
  public function outputBody();
}
