<?php
namespace Cms\Controller\Router;

/**
 * Router fuer Cli aufruf
 *
 * @package    Cms
 * @subpackage Controller
 */
class Cli extends \Zend_Controller_Router_Abstract
{
  /**
    * Currently matched route
    *
    * @var \Zend_Controller_Router_Route_Interface
    */
  protected $_currentRoute = null;
  
  public function route(\Zend_Controller_Request_Abstract $dispatcher)
  {
    $cliOptions = getopt("", array("action:", "params::"));
    if (!is_array($cliOptions) || !isset($cliOptions['action']) || !is_string($cliOptions['action'])) {
      echo "No action given.\n", exit;
    }

    // get controler, action and params
    if (!preg_match('/^(.+)\/(.+)$/i', $cliOptions['action'], $matches)) {
      echo "Wrong action format.\n", exit;
    }
    $controllerName = $matches[1];
    $actionName = $matches[2];
    $params = (isset($cliOptions['params']) ? $cliOptions['params'] : array());

    // set controller name, action name and params
    $dispatcher->setControllerName($controllerName);
    $dispatcher->setActionName($actionName);
    $this->setCliParamsToDispatcher($dispatcher, $params);
    return $dispatcher;
  }

  /**
    * Retrieve a name of currently matched route
    *
    * @return string
    */
  public function getCurrentRouteName()
  {
    return 'cmsCli';
  }

  public function assemble($userParams, $name = null, $reset = false, $encode = true)
  {
    echo "Not implemented\n", exit;
  }
  
  /**
    * @params \Zend_Controller_Request_Abstract   $dispatcher
    * @params string  $paramString
    */
  protected function setCliParamsToDispatcher(\Zend_Controller_Request_Abstract $dispatcher, $paramString)
  {
    if (!empty($paramString)) {
      $decodeFunctions = array(
        'decode_none',
        'decode_base64',
        'decode_base32',
        'decode_base32hex',
        'decode_hex',
      );

      foreach ($decodeFunctions as $decoder) {
        $decodedPararms = $this->$decoder($paramString);
        if (!empty($decodedPararms) && $this->isJsonString($decodedPararms)) {
          $dispatcher->setParam('params', $decodedPararms);
          return;
        }
      }
    }
    
    $dispatcher->setParam('params', $paramString);
  }
  
  /**
    * @params $jsonString
    * @return boolean
    */
  protected function isJsonString($jsonString)
  {
    try {
      \Zend_Json::decode($jsonString, \Zend_Json::TYPE_OBJECT);
      return true;
    } catch (\Exception $doNothing) {
      try {
        \Zend_Json::decode(utf8_encode($jsonString), \Zend_Json::TYPE_OBJECT);
        return true;
      } catch (\Exception $doNothing) {
      }
    }
    return false;
  }
  /**
    * @params string  $orgString
    * @return string
    */
  protected function decode_none($orgString)
  {
    return $orgString;
  }

  /**
    * @params string  $base64String
    * @return string
    */
  protected function decode_base64($base64String)
  {
    return base64_decode($base64String, true);
  }
  
  /**
    * @params string  $base32String
    * @return string
    */
  protected function decode_base32($base32String, $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567')
  {
    $base32String = strtoupper($base32String);
    $tmp = '';
    foreach (str_split($base32String) as $c) {
      if (false === ($v = strpos($alphabet, $c))) {
        $v = 0;
      }
      $tmp .= sprintf('%05b', $v);
    }
    $args = array_map('bindec', str_split($tmp, 8));
    array_unshift($args, 'C*');
    return rtrim(call_user_func_array('pack', $args), "\0");
  }
  
  /**
    * @params string  $base32hexString
    * @return string
    */
  protected function decode_base32hex($base32hexString)
  {
    return $this->decode_base32($base32hexString, '0123456789ABCDEFGHIJKLMNOPQRSTUV');
  }

  /**
    * @params string  $hexString
    * @return string
    */
  protected function decode_hex($hexString)
  {
    $string='';
    for ($i=0; $i < strlen($hexString)-1; $i+=2) {
      $string .= chr(hexdec($hexString[$i].$hexString[$i+1]));
    }
    return $string;
  }
}
