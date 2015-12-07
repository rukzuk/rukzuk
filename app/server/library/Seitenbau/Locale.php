<?php
namespace Seitenbau;

/**
 * @package      Seitenbau
 * @subpackage   Locale
 */
class Locale extends \Zend_Locale
{
  /**
   * Returns a string representation of the object in xx-XX format (en-US)
   *
   * @return string
   */
  public function getLanguageCode()
  {
    return self::convertToLanguageCode($this->toString());
  }
  
  public static function convertToLanguageCode($language)
  {
    if (!is_string($language)) {
      return;
    }
    return str_replace('_', '-', $language);
  }
}
