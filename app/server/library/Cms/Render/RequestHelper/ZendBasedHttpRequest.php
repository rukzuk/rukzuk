<?php


namespace Cms\Render\RequestHelper;

use Render\RequestHelper\HttpRequestInterface;

class ZendBasedHttpRequest implements HttpRequestInterface
{
  /**
   * @var \Zend_Controller_Request_Http
   */
  private $zendRequest;

  /**
   * @param \Zend_Controller_Request_Http $zendRequest
   */
  public function __construct(\Zend_Controller_Request_Http $zendRequest)
  {
    $this->zendRequest = $zendRequest;
  }

  /**
   * @return string
   */
  public function getUri()
  {
    return $this->getZendRequest()->getRequestUri();
  }

  /**
   * @param string  $key
   * @param null    $default
   *
   * @return mixed
   */
  public function getParam($key, $default = null)
  {
    return $this->getZendRequest()->getParam($key, $default);
  }

  /**
   * Returns the value of the given HTTP header
   *
   * @param $header
   *
   * @return mixed
   */
  public function getHeader($header)
  {
    try {
      return $this->getZendRequest()->getHeader($header);
    } catch (\Exception $ignore) {
      return false;
    }
  }

  /**
   * @return \Zend_Controller_Request_Http
   */
  protected function getZendRequest()
  {
    return $this->zendRequest;
  }
}
