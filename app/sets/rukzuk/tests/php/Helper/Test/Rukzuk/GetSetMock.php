<?php
namespace Test\Rukzuk;

class GetSetMock
{

  protected $data;

  public function __construct($data = null)
  {
    if (is_array($data)) {
      $this->data = $data;
    } else {
      $this->data = array();
    }
  }

  private function getValue($key)
  {
    if (is_array($cfg) && isset($cfg[$key])) {
      return $cfg[$key];
    } else {
      return $default;
    }
  }

  public function __call($name, $arguments) {
    $action = substr($name, 0, 3);
    $key = lcfirst(substr($name, 3, strlen($name)));

    if ($action === 'get') {
      if (isset($this->data[$key])) {
        return $this->data[$key];
      } else {
        return null;
      }
    }

    if ($action === 'set') {
      $this->data[$key] = $arguments[0];
      return $this;
    }
  }
}
