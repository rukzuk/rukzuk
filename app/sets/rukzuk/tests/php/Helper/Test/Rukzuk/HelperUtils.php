<?php
namespace Test\Rukzuk;

class HelperUtils
{
  public static function getValue($key, $param, $defaultValue)
  {
    if (is_array($param) && array_key_exists($key, $param)) {
      return $param[$key];
    } else {
      return $defaultValue;
    }
  }
}
