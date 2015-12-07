<?php
use Seitenbau\Controller;
use Seitenbau\Registry;

/**
 * Error Controller
 *
 * @package      Application
 * @subpackage   Controller
 */
class ErrorController extends Controller\Action
{
  protected $responseErrors = array();

  private $requestShowHtmlResponse = array(
    'render' => array('page', 'pagecss', 'template', 'templatecss')
  );

  public function init()
  {
    parent::init();
  }

  public function errorAction()
  {
    $errors = $this->_getParam('error_handler');

    if ($errors === null) {
      \Cms\ExceptionStack::throwErrors();
    }

    $this->setResponseStatusCode($errors);

    if ($errors->exception instanceof \Cms\ExceptionStackException) {
      $this->handleStackExceptions($errors->exception);
      $responseData = $errors->exception->getResponseData();
    } else {
      $this->handleError($errors->exception);
      $responseData = null;
    }

    $response = $this->generateResponse($this->responseErrors, $responseData);
    $this->responseData = $response;

    \Cms\ExceptionStack::reset();
  }

  public function getLog()
  {
    if (!Registry::getLogger()) {
      return false;
    }
    $log = Registry::getLogger();
    return $log;
  }

  protected function handleError($exception)
  {
    $logId = $this->getLogId($exception);

    $this->logException($exception, $logId);

    $this->responseErrors[] = $this->generateErrorResponse($exception, $logId);
  }

  protected function handleStackExceptions(\Cms\ExceptionStackException $exception)
  {
    $exceptions = $exception->getExceptions();

    foreach ($exceptions as $handleException) {
      $this->handleError($handleException);
    }
  }

  /**
   * set the response code on basis of error type
   *
   * @param array_object $errors
   */
  protected function setResponseStatusCode($errors)
  {
    switch ($errors->type) {
      case \Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
      case \Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
      case \Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        // 404 error -- controller or action not found
        $this->getResponse()->setHttpResponseCode(404);
            break;
      default:
        // application error
        if ($errors->exception instanceof \Cms\Exception) {
          $this->getResponse()->setHttpResponseCode(
              $errors->exception->getHttpResponseCode()
          );
        } else {
          $this->getResponse()->setHttpResponseCode(200);
        }
            break;
    }
  }

  protected function logException($exception, $logid)
  {
    // Log exception, if logger available
    $log = $this->getLog();

    // Exceptions, welche das CMS wirft werden bereits geloggt
    if ($log) {
      $priority = $this->getPriority($exception);

      $exceptionData = (method_exists($exception, 'getData'))
        ? $exception->getData()
        : null;

      if (method_exists($exception, 'getException')
        && $exception->getException() != null
      ) {
        $exceptionTrace = $exception->getException();
      } else {
        $exceptionTrace = $exception;
      }

      $log->logException(
          $exception->getFile(),
          $exception->getLine(),
          $exceptionTrace,
          $priority,
          $logid,
          $exceptionData
      );
    }
  }

  protected function getPriority($exception)
  {
    if (method_exists($exception, 'getPriority')) {
      return constant("\Zend_Log::" . $exception->getPriority());
    }
    if ($exception instanceof \Zend_Controller_Action_Exception) {
      if (in_array($exception->getCode(), array(404, 500))) {
        return \Zend_Log::NOTICE;
      }
    }
    return \Zend_Log::ERR;
  }

  protected function getLogId($exception)
  {
    if ($exception instanceof \Cms\Exception &&
      method_exists($exception, 'getLogid')
    ) {
      return $exception->getLogId();
    }

    return rand(100000, 999999);
  }

  protected function generateErrorResponse($exception, $logId)
  {
    return new \Cms\Response\Error($exception, $logId);
  }

  protected function generateResponse($responseErrors = array(), $responseData = null)
  {
    if ($this->checkShowLogin($responseErrors)) {
      $requestUri = \Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
      $this->_redirect(
          \Seitenbau\Registry::getConfig()->server->url .
          '/login/login/?url=' . urlencode(base64_encode($requestUri)),
          array('prependBase' => false)
      );
    }

    $response = new \Cms\Response();
    $response->setError($responseErrors);
    $response->setData($responseData);
    return $response;
  }

  private function checkShowLogin($responseErrors)
  {
    if ($this->hasRequestHtmlLogin()
      && $this->checkErrorsIncludeErrorCode($responseErrors, 5)
    ) {
      return true;
    }

    return false;
  }

  /**
   * Pruefung, ob einer der Response-Errors einen bestimmten Error-Code besitzen
   *
   * @param array $responseErrors
   * @param int   $errorCode
   *
   * @return boolean
   */
  private function checkErrorsIncludeErrorCode(array $responseErrors, $errorCode)
  {
    foreach ($responseErrors as $responseError) {
      if ($responseError->getCode() == $errorCode) {
        return true;
      }
    }

    return false;
  }

  /**
   * Pruefung, ob der Request ein HTML-Seite Login angezeigt bekommt
   *
   * @return type
   */
  private function hasRequestHtmlLogin()
  {
    $requestControllerName = $this->getRequest()->getParam('controller');

    if (array_key_exists($requestControllerName, $this->requestShowHtmlResponse)) {
      $requestActionName = $this->getRequest()->getParam('action');
      if (in_array($requestActionName, $this->requestShowHtmlResponse[$requestControllerName])) {
        return true;
      }
    }
    return false;
  }
}
