<?php
use Cms\Controller as Controller;
use \Cms\Access\Manager as AccessManager;
use \Seitenbau\Registry as Registry;

/**
 * Login Controller (HTML-Login-Seite)
 *
 * @package      Application
 * @subpackage   Controller
 */

class LoginController extends Controller\Action
{
  public function init()
  {
    parent::init();

    $this->_helper->viewRenderer->setNoRender(false);
    $this->contentTypeValue = 'text/html';
  }

  public function loginAction()
  {
    $config = Registry::getConfig();
    
    $this->view->loginFailed = false;
    
    // Bereits angemeldete User direkt weiterleiten
    if ($this->isUserDeclared()) {
      $this->_redirect(
          base64_decode($this->getRequest()->getParam('url')),
          array('prependBase'=>false)
      );
    }
    
    if ($this->getRequest()->getPost('login')) {
      $username = $this->getRequest()->getPost('username');
      $userpassword = $this->getRequest()->getPost('password');
      
      if ($this->tryUserLogin($username, $userpassword)) {
        $this->_redirect(
            base64_decode($this->getRequest()->getPost('url')),
            array('prependBase'=>false)
        );
      }
      
      // Ticket-Login mit den Zugangsdaten auf den Ticket-Service weiterleiten
      if ($this->getRequest()->getPost('ticket')) {
        $credentials = \Zend_Json::encode(array(
          'username' => $username,
          'password' => $userpassword,
        ));
        $url = base64_decode($this->getRequest()->getParam('url'));
        if (substr($url, -1, 1) != '/') {
          $url .= '/';
        }
        $url .= $config->request->parameter . '/' . urlencode($credentials);
        
        $this->_redirect($url, array('prependBase'=>false));
      }
      
      $this->view->loginFailed = true;
    }
    
    $this->view->url = $this->getRequest()->getParam('url');
    $this->view->useClientTemplate = realpath($config->client->template->login);
    
    // Ticket-Daten aufnehmen
    if ($this->getRequest()->getParam('ticket') == 2) {
      $this->view->loginFailed = true;
    }
    $this->view->ticket = $this->getRequest()->getParam('ticket');
  }

  /**
   * Pruefung, ob der User angemeldet ist
   *
   * @return boolean
   */
  private function isUserDeclared()
  {
    $accessManager = AccessManager::singleton();
    return $accessManager->hasIdentity();
  }
  
  /**
   * Versucht einen User anzumelden, mit den uebergebenen Daten
   *
   */
  private function tryUserLogin($username, $userpassword)
  {
    try {
      if ($this->getBusiness('User')->login($username, $userpassword)) {
        return true;
      }
    } catch (\Exception $e) {
    }
    
    return false;
  }
  
  public function postDispatch()
  {
  }
  
  /**
   * check if the session should be closed
   *
   * @param string $action  Method name of action
   * @return boolean
   */
  protected function shouldTheSessionBeClosedBeforeActionDispatched($action)
  {
    // Do not close the session
    return false;
  }
}
