<?php


namespace Render\RequestHelper;

class SimpleHttpRequest implements HttpRequestInterface
{
  /**
   * Returns the uri
   *
   * @return string
   */
  public function getUri()
  {
    if (isset($_SERVER['HTTP_X_REWRITE_URL'])
      && !empty($_SERVER['HTTP_X_REWRITE_URL'])) {
      return $_SERVER['HTTP_X_REWRITE_URL'];
    } elseif (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
      return $_SERVER['REQUEST_URI'];
    }
    // TODO: add more detections (e.g. IIS with rewrite)
    return '';
  }

  /**
   * Returns the value of the parameter given by $key
   *
   * @param string $key
   * @param null   $default
   *
   * @return mixed
   */
  public function getParam($key, $default = null)
  {
    $rawValue = $this->getRawParam($key);
    if (!isset($rawValue)) {
      return $default;
    }
    if ($this->isMagicQuotesActive()) {
      return $this->arrayStripSlashes($rawValue);
    } else {
      return $rawValue;
    }
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
    if (isset($_SERVER[$header])) {
      return $_SERVER[$header];
    }
    return false;
  }

  /**
   * Returns the value of the parameter given by $key
   *
   * @param string $key
   *
   * @return null|mixed
   */
  protected function getRawParam($key)
  {
    if (isset($_GET[$key])) {
      return $_GET[$key];
    } elseif (isset($_POST[$key])) {
      return $_POST[$key];
    }
    return null;
  }

  /**
   * @return bool
   */
  protected function isMagicQuotesActive()
  {
    return (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc());
  }

  /**
   * @param array|string $mix
   *
   * @return array|string
   */
  protected function arrayStripSlashes($mix)
  {
    if (is_array($mix)) {
      $b = array();
      foreach ($mix as $k => $v) {
        $b[$k] = $this->arrayStripSlashes($v);
      }
      return $b;
    } else {
      return stripslashes($mix);
    }
  }
}
