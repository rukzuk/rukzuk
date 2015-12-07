<?php

use Cms\Controller;
use Cms\Response\ServerInfo as ServerInfoResponse;
use Cms\Version as CmsVersion;
use Seitenbau\Registry as Registry;

class IndexController extends Controller\Action
{
  public function init()
  {
    parent::init();

    $frontController = \Zend_Controller_Front::getInstance();
    $routeName = $frontController
               ->getRouter()
               ->getCurrentRouteName();
    if ($routeName == 'clientMock') {
      $requestUri = $frontController->getRequest()->getRequestUri();
      $this->_redirect(substr($requestUri, 4));
    }
  }

  public function indexAction()
  {
    $this->setResponseType(self::RESPONSE_TYPE_HTML_VIEW);
  }

  public function infoAction()
  {
    $quota = new \Cms\Quota();

    $serverData = array(
      'mode'          => CmsVersion::getMode(),
      'maxUploadSize' => $this->getMaxUploadSize(),
      'urls'          => $this->getPublicUrlEndpoints(),
      'quota'         => $quota->toArray(),
      'supportedPublishTypes'  => $this->getSupportedPublishTypes(),
      'language'      => Registry::getConfig()->translation->default,
    );
    
    $this->responseData->setData(new ServerInfoResponse($serverData));
    
    // XHR request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
      // return as json
      $this->setResponseType(self::RESPONSE_TYPE_JSON);
      return;
    }

    // return as javascript
    $this->setResponseType(self::RESPONSE_TYPE_JS_VAR, array('name' => 'CMSSERVER'));
    return;
  }
  
  protected function getMaxUploadSize()
  {
    $max_upload   = (int)(ini_get('upload_max_filesize'));
    $max_post     = (int)(ini_get('post_max_size'));
    return min($max_upload, $max_post)*1024*1024;
  }

  protected function getPublicUrlEndpoints()
  {
    $urls = array();

    $cfg = Registry::getConfig();
    if (isset($cfg->services)) {
      $urls['linkResolver'] = $cfg->services->linkResolver;
    }

    return $urls;
  }

  /**
   * @return array
   */
  protected function getSupportedPublishTypes()
  {
    return $this->getBusiness('Publisher')->getSupportedPublishTypes();
  }
}
