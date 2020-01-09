<?php
namespace Seitenbau\Controller;

/**
 * Controller fuer Voreinstellungen
 *
 * @package    Seitenbau
 * @subpackage Controller
 */

abstract class Action extends \Zend_Controller_Action
{
  const RESPONSE_TYPE_JSON        = 'json';
  const RESPONSE_TYPE_JS_VAR      = 'jsvar';
  const RESPONSE_TYPE_HTML_VIEW   = 'htmlview';
  
  private $responseType       = 'json';
  private $responseTypeParams = null;

  protected $responseData = array();
  protected $contentTypeValue = null;

  /**
   * @param string $value
   */
  protected function setContentTypeValue($value)
  {
    $this->contentTypeValue = $value;
  }
  
  /**
   * Initialisert den Action Controller und entfernt den View-Render
   */
  public function init()
  {
    $this->_helper->viewRenderer->setNoRender();
    $this->contentTypeValue = 'application/json; charset=utf-8';
    parent::init();
  }

  public function preDispatch()
  {
    parent::preDispatch();
    $this->setResponseType();
  }

  /**
   * Erstellt die komplette Ausgabe der Actions als JSON
   */
  public function postDispatch()
  {
    switch($this->responseType) {
      // use zend view
      case self::RESPONSE_TYPE_HTML_VIEW:
          $this->setContentTypeValue('text/html');
            break;
      // return as javascript variable
      case self::RESPONSE_TYPE_JS_VAR:
          $responseString = sprintf(
              "var %s = %s;",
              (isset($this->responseTypeParams['name']) ? $this->responseTypeParams['name'] : 'CMSDATA'),
              \Seitenbau\Json::encode($this->responseData)
          );
          $this->buildResponse($responseString, 'application/javascript; charset=utf-8');
            break;
      // return as json
      case self::RESPONSE_TYPE_JSON:
      default:
          $json = \Seitenbau\Json::encode($this->responseData);
          $this->buildResponse($json, $this->contentTypeValue);
            break;
    }
    parent::postDispatch();
  }

  /**
   * Fuegt Daten zum Response hinzu
   * @param mixed $data
   */
  protected function addToResponse($data)
  {
    $this->responseData[] = $data;
  }

  /**
   * Setzt den Response zusammen
   * @param string $json
   */
  protected function buildResponse($json, $contentType = 'application/json')
  {
    $this->getResponse()->setHeader('Content-Type', $contentType);
    $this->getResponse()->setBody($json);
  }
  
  protected function setResponseType($type = self::RESPONSE_TYPE_JSON, $params = null)
  {
    $this->responseType = $type;
    $this->responseTypeParams = $params;
  }
}
