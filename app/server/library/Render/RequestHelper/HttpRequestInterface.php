<?php


namespace Render\RequestHelper;

interface HttpRequestInterface
{
  /**
   * Returns the uri
   *
   * @return string
   */
  public function getUri();

  /**
   * Returns the value of the parameter given by $key
   *
   * @param string  $key
   * @param null    $default
   *
   * @return mixed
   */
  public function getParam($key, $default = null);

  /**
   * Returns the value of the given HTTP header
   *
   * @param $header
   *
   * @return mixed
   */
  public function getHeader($header);
}
