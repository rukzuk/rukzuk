<?php
namespace Cms\Request;

use Seitenbau\Registry as Registry;
use Seitenbau\Log as SbLog;

/**
 * base request object
 *
 * @package      Cms
 * @subpackage   Request
 */

abstract class Base
{
  const REQUEST_PARAMETER = 'params';

  protected $requestParams = array();

  protected $controllerName;

  protected $actionName;

  public function __construct(\Zend_Controller_Request_Abstract $request = null)
  {
    if ($request !== null) {
      $this->setControllerName($request->getControllerName());
      $this->setActionName($request->getActionName());
    }
    $this->initParamsFromRequest($request);
    $this->setValues();
  }
  
  protected function initParamsFromRequest(\Zend_Controller_Request_Abstract $request = null)
  {
    if ($request != null) {
      $this->setJsonRequestParams($request);
    }
  }

  /**
   * legt den namen des controllers fest
   *
   * @param string $controller
   */
  public function setControllerName($controllerName)
  {
    $this->controllerName = $controllerName;
  }

  /**
   * gibt den controller name zurueck
   *
   * @return string
   */
  public function getControllerName()
  {
    return $this->controllerName;
  }

  /**
   * legt den namen des controllers fest
   *
   * @param string $controller
   */
  public function setActionName($actionName)
  {
    $this->actionName = $actionName;
  }

  /**
   * gibt den action name zurueck
   *
   * @return string
   */
  public function getActionName()
  {
    return $this->actionName;
  }

  /**
   * Wandelt die ankommenden Request Parameter Keys in lowercase um, fuer eine
   * leichtere Entnahme.
   *
   * @param  array $params
   * @return array
   */
  protected function lowercaseIncomingRequestParamsKeys(array $params)
  {
    $lowercasedRequestParams = array();
    foreach ($params as $key => $value) {
      $lowercasedRequestParams[strtolower($key)] = $value;
    }
    return $lowercasedRequestParams;
  }

  /**
   * prueft ob ein angegebenes Attribute/Property gesetzt ist
   *
   * @return boolean
   */
  public function isPropertySet($property)
  {
    if (isset($this->$property)) {
      return ($this->$property !== null && $this->$property !== 0
             && $this->$property !== '');
    }
  }

  /**
   * set the json params from request
   *
   * @param Zend_Controller_Request_Abstract  $request
   */
  protected function setJsonRequestParams(\Zend_Controller_Request_Abstract $request)
  {
    $paramString = $request->getParam(self::REQUEST_PARAMETER);
    if (empty($paramString)) {
      return;
    }

    try {
      $incomingRequestParams = \Seitenbau\Json::decode($paramString, \Zend_Json::TYPE_OBJECT);
    } catch (\Exception $e) {
      try {
        $incomingRequestParams = \Seitenbau\Json::decode(utf8_encode($paramString), \Zend_Json::TYPE_OBJECT);
      } catch (\Exception $e) {
        Registry::getLogger()->logException(__METHOD__, __LINE__, $e, SbLog::DEBUG);
        return null;
      }
    }

    if (is_object($incomingRequestParams)) {
      $this->setRequestParams(get_object_vars($incomingRequestParams));
    }
  }

  /**
   * @param array $params
   */
  protected function setRequestParams($params)
  {
    $this->requestParams = $this->lowercaseIncomingRequestParamsKeys($params);
  }

  /**
   * @param array $params
   */
  protected function setRequestParam($paramname, $paramvalue)
  {
    $this->requestParams[strtolower($paramname)] = $paramvalue;
  }

  /**
   * get one request param by name
   *
   * @param string $paramname
   * @return string
   */
  protected function getRequestParam($paramname)
  {
    if (isset($this->requestParams[$paramname])) {
      return $this->requestParams[$paramname];
    }
    return null;
  }

  /**
   * returns whether the param with the given name is set in the request
   *
   * @param string $paramname
   * @return bool
   */
  protected function hasRequestParam($paramname)
  {
    if (isset($this->requestParams[$paramname]) || array_key_exists($paramname, $this->requestParams)) {
      return true;
    }
    return false;
  }

  /**
   * set values function is called in construct
   * hier werden die values fuer das request objekt gesetzt
   */
  abstract protected function setValues();
}
