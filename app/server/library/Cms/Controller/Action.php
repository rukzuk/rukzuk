<?php
namespace Cms\Controller;

use Seitenbau\Registry as Registry;
use Seitenbau\Log as Log;
use Cms\Service\Iface\Marker as CmsService;
use Cms\Business\Iface\Marker as CmsBusiness;
use Cms\Exception;
use Cms\Service;
use Cms\Response;

/**
 * Controller fuer CMS Voreinstellungen
 *
 * @package    Cms
 * @subpackage Controller
 */

abstract class Action extends \Seitenbau\Controller\Action
{
  protected $service;

  protected $business;

  protected $pushDataToErrorController = false;

  public function init()
  {
    parent::init();
    $this->responseData = new Response();
    $this->pushDataToErrorController = false;
  }

  protected function initBusiness($businessname)
  {
    $business = 'Cms\Business\\' . $businessname;
    if (class_exists($business)) {
      $this->setBusiness(new $business($businessname));
    } else {
      $message = sprintf("Business '%s' could not be loaded", $business);
      throw new Exception($message);
    }
  }

  protected function setBusiness(CmsBusiness $business)
  {
    $this->business = $business;
  }

  /**
   * Gibt ein Business zurueck, wird kein Businessname uebergeben so wird
   * der initialisierte Business zurueckgegeben
   *
   * @param string $businessname
   * @return \Cms\Business\Iface\Marker
   */
  protected function getBusiness($businessname = '')
  {
    if ($businessname == '') {
      return $this->business;
    } else {
      $business = 'Cms\Business\\' . $businessname;

      if (class_exists($business)) {
        return new $business($businessname);
      }
    }
  }

  protected function initService($servicename)
  {
    $service = 'Cms\Service\\' . $servicename;

    if (class_exists($service)) {
      $this->setService(new $service($servicename));
    } else {
      $message = sprintf("Service '%s' could not be loaded", $service);
      throw new Exception($message);
    }
  }

  protected function setService(CmsService $service)
  {
    $this->service = $service;
  }

  /**
   * Gibt einen Service zurueck, wird kein Servicename uebergeben so wird
   * der initialisierte Service zurueckgegeben
   *
   * @param string $servicename
   * @return \Cms\Service\Iface\Marker
   */
  protected function getService($servicename = '')
  {
    if ($servicename == '') {
      return $this->service;
    } else {
      $service = 'Cms\Service\\' . $servicename;

      if (class_exists($service)) {
        return new $service($servicename);
      }
    }
  }
  
  /**
   * dispatch action
   *
   * @param string $action Method name of action
   * @return void
   */
  public function dispatch($action)
  {
    if ($this->shouldTheSessionBeClosedBeforeActionDispatched($action)) {
      \Zend_Session::writeClose();
    }
            
    return parent::dispatch($action);
  }
  
  /**
   * check if the session should be closed
   *
   * @param string $action  Method name of action
   * @return boolean
   */
  protected function shouldTheSessionBeClosedBeforeActionDispatched($action)
  {
    return true;
  }

  /**
   * Erstellt die komplette Ausgabe der Actions als JSON
   * Wirft evtl. aufgetretene Fehler
   */
  public function postDispatch()
  {
    // Exceptions ausgeben, wenn welche im Stack liegen
    $exceptions = \Cms\ExceptionStack::getExceptions();
    if (count($exceptions) > 0) {
    // response->data weitergeben?
      $data = ($this->pushDataToErrorController === true
                ? $this->responseData->getData()
                : null);
      \Cms\ExceptionStack::throwErrors($data);
    }

    parent::postDispatch();
  }

  /**
   * return the validated request object
   *
   * @param string  $controllerName
   * @param string  $actionName
   * @param boolean $setHttpErrorCode
   *
   * @return Cms_Request_Abstract|false
   */
  protected function getValidatedRequest(
      $controllerName,
      $actionName,
      $setHttpErrorCode = false
  ) {
    $actionRequest = $this->getActionRequest($controllerName, $actionName);

    $requestValidator = $this->getRequestValidator($controllerName);

    $requestIsValid = $requestValidator->validate(
        $actionName,
        $actionRequest,
        $setHttpErrorCode
    );

    if (!$requestIsValid && $setHttpErrorCode) {
      $this->getResponse()->setHttpResponseCode(400);
      \Cms\ExceptionStack::reset();
      return false;
    }

    return $actionRequest;
  }

  /**
   * get the request for a specific controller action combination
   *
   * @param string $controllerName
   * @param string $actionName
   * @return Cms_Request_Abstract
   */
  protected function getActionRequest($controllerName, $actionName)
  {
    $requestClass = 'Cms\Request\\' . $controllerName . '\\' . $actionName;

    if (class_exists($requestClass)) {
      $actionRequest = new $requestClass($this->getRequest());
    } else {
      $data = array('class' => $requestClass);
      throw new Exception(-10, __METHOD__, __LINE__, $data);
    }

    return $actionRequest;
  }

  /**
   * get the request validator for a specific controller
   *
   * @param string $controllerName
   * @return Cms_Request_Validator_Abstract
   */
  protected function getRequestValidator($controllerName)
  {
    $validatorClass = 'Cms\Request\Validator\\' . $controllerName;

    if (class_exists($validatorClass)) {
      $requestValidator = new $validatorClass();
    } else {
      $data = array('class' => $validatorClass);
      throw new Exception(-11, __METHOD__, __LINE__, $data);
    }

    return $requestValidator;
  }

  /**
   * Prueft ob der angemeldeten Users fuer das aufgerufene Item im
   * Zusammenhang mit der Website-ID und Run-ID den Lock besitzt
   *
   * @param string  $runId
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @param boolean $noLock     true: Lock muss fuer den Benutzer vorhanden sein
   *                            false: gibt true zurueck auch wenn kein Lock existiert
   * @return boolean  true: Lock vorhanden; false: Lock nicht vorhanden
   */
  final protected function checkUserLock($runId, $itemId, $websiteId, $type, $noLock = true)
  {
    // Lock aktiv?
    if (\Seitenbau\Registry::getConfig()->lock->check->activ == true) {
    // Lock pruefen
      return $this->getBusiness('Lock')->checkUserLock(
          $runId,
          $itemId,
          $websiteId,
          $type,
          $noLock
      );
    }

    // Lockpruefueng abgeschaltet
    return true;
  }

  /**
   * Prueft ob fuer das angegebene Item ein Lock besteht
   *
   * @param string  $itemId
   * @param string  $websiteId
   * @param string  $type
   * @param boolean $throwError
   * @return mixed  false: kein Lock vorhanden, array: Lockdaten
   */
  final protected function hasLock($itemId, $websiteId, $type, $throwError = true)
  {
    // Lock aktiv?
    if (\Seitenbau\Registry::getConfig()->lock->check->activ == true) {
    // Lock vorhanden
      $lock = $this->getBusiness('Lock')->checkLock(
          null,
          $itemId,
          $websiteId,
          $type,
          false,
          $throwError
      );
      if (is_array($lock) && isset($lock['locked']) && $lock['locked'] === true) {
      // Vorhandener Lock zurueckgeben
        return $lock;
      }
      
      // Kein Lock vorhanden
      return false;
    }

    // Lockpruefueng abgeschaltet -> Kein lock vorhanden
    return false;
  }

  /**
   * Seztz das Flag, ob die response->data an den Error-Controller uebergeben
   * werden soll
   *
   * @param boolean $push   true: response->data weitergeben
   *                        false: response->data NICHT weitergeben
   */
  final protected function pushDataToErrorController($push = false)
  {
    // Status uebernehmen
    $this->pushDataToErrorController = ($push === true);
  }
}
