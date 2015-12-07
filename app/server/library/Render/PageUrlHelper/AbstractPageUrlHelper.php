<?php


namespace Render\PageUrlHelper;

/**
 * @package Render\PageUrlHelper
 */
abstract class AbstractPageUrlHelper implements IPageUrlHelper
{
  /**
   * @var string
   */
  private $baseUrl;

  /**
   * @param string|null $baseUrl
   */
  public function __construct($baseUrl = null)
  {
    $this->baseUrl = $baseUrl;
  }

  /**
   * @param string $url
   * @param array  $parameters
   * @param bool   $absoluteUrl
   *
   * @return string
   */
  protected function createUrlWithQuery($url, array $parameters, $absoluteUrl)
  {
    if ($absoluteUrl) {
      $url = $this->createAbsoluteUrl($url);
    }

    $queryString = http_build_query($parameters, '', '&');
    if (empty($queryString)) {
      return $url;
    } else {
      return $url . '?' . $queryString;
    }
  }

  /**
   * @return string
   */
  protected function getBaseUrl()
  {
    if (is_null($this->baseUrl)) {
      $scheme = $this->getServerVar('HTTPS', 'off') == 'on' ? "https://" : "http://";
      $this->baseUrl = $scheme . $this->getServerVar('HTTP_HOST', 'localhost');
    }
    return $this->baseUrl;
  }

  /**
   * @param string $name
   * @param string $default
   *
   * @return string
   */
  protected function getServerVar($name, $default)
  {
    return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
  }

  /**
   * @param string $url
   *
   * @return string
   */
  protected function createAbsoluteUrl($url)
  {
    if (!is_string($url)) {
      return $this->getBaseUrl();
    }
    if (0 !== strpos($url, '/')) {
      $url = '/' . $url;
    }
    return $this->getBaseUrl() . $url;
  }
}
