<?php
namespace Cms\Controller\Plugin;

use \Seitenbau\Registry;

/**
 * base plugin object
 *
 * @package      Cms
 * @subpackage   Controller\Plugin\Auth
 */

class Base extends \Zend_Controller_Plugin_Abstract
{
  function seems_utf8($Str)
  {
    for ($i=0; $i<strlen($Str); $i++) {
      if (ord($Str[$i]) < 0x80) {
        $n=0; # 0bbbbbbb
      } elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
      elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
      elseif ((ord($Str[$i]) & 0xF0) == 0xF0) $n=3; # 1111bbbb
      else {
        return false; # Does not match any model
      }      for ($j=0; $j<$n; $j++) { # n octets that match 10bbbbbb follow ?
        if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80)) {
          return false;
        }
      }
    }
    return true;
  }
  
  /**
   * Gibt einen Parameter aus dem Standard Request-Parameter zurueck
   *
   * @param \Zend_Controller_Request_Abstract $request
   * @param string  $param
   * @return  string|null
   */
  protected function getParam(\Zend_Controller_Request_Abstract $request, $param)
  {
    $paramString = $request->getParam(\Cms\Request\Base::REQUEST_PARAMETER);

    try {
      $paramArray = \Seitenbau\Json::decode($paramString);
    } catch (\Exception $e) {
      try {
        // Try to decode to UTF8
        $paramArray = \Seitenbau\Json::decode(utf8_encode($paramString));
      } catch (\Exception $e) {
        \Seitenbau\Registry::getLogger()->logException(
            __METHOD__,
            __LINE__,
            $e,
            \Seitenbau\Log::DEBUG
        );
        return null;
      }
    }

    if (is_array($paramArray) && count($paramArray) > 0) {
      foreach ($paramArray as $paramName => $paramValue) {
        if (strtolower($paramName) == $param) {
          return $paramValue;
        }
      }
    }

    return null;
  }
}
