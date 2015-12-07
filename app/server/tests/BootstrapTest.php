<?php
require_once 'Bootstrap.php';

use Test\Rukzuk\ConfigHelper;
use Seitenbau\Registry as Registry;
use Test\Seitenbau\Response\HttpTestCase as HttpTestCaseResponse;
use Test\Seitenbau\Logger\ActionMock as ActionLoggerMock;

/**
 * Test Bootstrap
 *
 * Hier findet das zusaetzliche Bootstrapping fuer die Testumgebung statt
 *
 * @package      Test
 */
class BootstrapTest extends Bootstrap
{

  protected function _bootstrap($resource = null) {
    $this->original_bootstrap($resource);
  }

  protected function _initOriginalConfig()
  {
    $this->bootstrap('config');
    // at first call, set the original config
    if (!ConfigHelper::hasOriginalConfig()) {
      ConfigHelper::setOriginalConfig(Registry::getConfig());
    }
  }

  protected function _initLogger()
  {
    // Falls Logger vorhanden, diesen zuerst entfernen
    $logger = Registry::getLogger();
    if ($logger instanceof Seitenbau\Logger) {
      $logger = null;
    }
    parent::_initLogger();
  }


  protected function _initModifiedFrontController()
  {
    // Http-Response-Objekt mit Datei-Streaming setzen
    $this->bootstrap('frontController');
    $front = $this->getResource('FrontController');
    $response = new HttpTestCaseResponse();
    $front->setResponse($response);
  }

  protected function getNewActionLogger(\Zend_Log $logger)
  {
    return new ActionLoggerMock($logger);
  }
}