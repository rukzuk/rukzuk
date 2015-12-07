<?php
namespace Seitenbau;

/**
 * Zufallsgenerator
 *
 * @package    Seitenbau
 */

class RandomGenerator
{
  const DEFAULT_CHARS = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-";
  
  /**
   * @return string
   */
  public static function generateString($length, $chars = self::DEFAULT_CHARS)
  {
    $string = '';
    $charCount = strlen($chars)-1;
    for ($i = 0; $i < $length; $i++) {
        $string  .= substr($chars, mt_rand(0, $charCount), 1);
    }
    return str_shuffle($string);
  }
}
