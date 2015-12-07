<?php
use Cms\Controller as Controller;
use Cms\Response\Shortener as Response;
use Cms\Exception as CmsException;
use Cms\Business\Shortener\LoginException;
use Cms\Business\Shortener\InvalidTicketException;
use Seitenbau\Registry as Registry;

/**
 * ShortenerController
 *
 * @package      Application
 * @subpackage   Controller
 */
class ShortenerController extends Controller\Action
{
  protected $isForwarded = false;
  
  public function init()
  {
    parent::init();
    $this->initBusiness('Shortener');
  }
  
  public function postDispatch()
  {
    if (!$this->isForwarded && $this->_helper->viewRenderer->getNoRender()) {
      parent::postDispatch();
    }
  }

  public function ticketAction()
  {
    // Ticketzugang aktiv?
    if (Registry::getConfig()->accessticket->activ != true) {
      // Nein -> Fehler
      throw new CmsException(1309, __METHOD__, __LINE__);
    }

    $validatedRequest = $this->getValidatedRequest('Shortener', 'Ticket');

    // Rechtepruefung
    $this->getBusiness()->checkUserRights('ticket');
    
    $credentials = array(
      'username' => $validatedRequest->getUsername(),
      'password' => $validatedRequest->getPassword()
    );

    try {
      // Ticket bearbeiten
      $ticket = $this->getBusiness()->processTicket(
          $validatedRequest->getTicketId(),
          $credentials
      );

    } catch (LoginException $e) {
      // Fehlenummer setzen
      $tErrorNo = 1;
      if (!empty($credentials['username']) || !empty($credentials['password'])) {
        $tErrorNo = 2;
      }
      
      // Weiterleiten auf Login
      $requestUri = \Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
      if (strpos($requestUri, '/params/') !== false) {
        $requestUri = substr($requestUri, 0, strpos($requestUri, '/params/'));
      }
      $this->_redirect(
          \Seitenbau\Registry::getConfig()->server->url .
          '/login/login/ticket/'.$tErrorNo.'/url/' . base64_encode($requestUri),
          array('prependBase'=>false)
      );
    } catch (CmsException $e) {
      $this->handelTicketError($e);
      return;
    }
    
    if (!($ticket instanceof \Cms\Data\Ticket)) {
      // redirekt nicht vorhanden (404 Senden)
      $this->getResponse()->setHttpResponseCode(404);
      return;
    }
    
    // redirect oder forwarding?
    if (!$ticket->isRedirect()) {
      // ***********************************
      // Weiterleiten auf anderen Controler
      $this->isForwarded = true;
      return $this->_forward(
          $ticket->getInternalAction(),
          $ticket->getInternalController(),
          null,
          $ticket->getInternalParams()
      );
    }

    // *************************************
    // Weiterleiten mittels HTTP-Redirect

    // Ziel Controller und Action ermitteln
    $redirectUrl = sprintf(
        '/%s/%s',
        $ticket->getInternalController(),
        $ticket->getInternalAction()
    );

    // Get-Request?
    if ($ticket->isGet()) {
      // Parameter anhaengen
      $params = $ticket->getInternalParams();
      if (is_array($params)) {
        foreach ($params as $paramKey => $paramValue) {
          $redirectUrl .= '/' . urlencode($paramKey) . '/' . urlencode($paramValue);
        }
      }
    } else {
      // Parameter werden ueber die Session (Plugin Accessticket) injiziert
      $redirectUrl .= '/fromticket/1';
    }

    if (!empty($redirectUrl)) {
      // Direkt auf die Url weiterleiten
      $this->_redirect($redirectUrl, array('prependBase'=>true, 'exit' => true));
      exit;
    }
  }
  
  private function handelTicketError(CmsException $e)
  {
    // XHR or browser
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
      // show json error
      throw $e;
    }

    // show html error
    if ($e instanceof InvalidTicketException) {
      $this->getResponse()->setHttpResponseCode(403);
    } else {
      $this->getResponse()->setHttpResponseCode(400);
    }
    $this->_helper->viewRenderer->setNoRender(false);
    $this->contentTypeValue = 'text/html';
    $this->view->errorMessage = $e->getMessage();
    $this->view->useClientTemplate = realpath(Registry::getConfig()->client->template->error);
    return;
  }

  public function createrenderticketAction()
  {
    // Ticketzugang aktiv?
    if (Registry::getConfig()->accessticket->activ != true) {
      // Nein -> Fehler
      throw new CmsException(1309, __METHOD__, __LINE__);
    }

    $validatedRequest = $this->getValidatedRequest('Shortener', 'CreateRenderTicket');
    
    // Rechtepruefung
    $this->getBusiness()->checkUserRights('createrenderticket', array(
      'id'        => $validatedRequest->getId(),
      'type'      => $validatedRequest->getType(),
      'websiteId' => $validatedRequest->getWebsiteId()
    ));
    
    // Zugangsdaten
    $credentials = null;
    if ($validatedRequest->getProtect()) {
      $credentials = $validatedRequest->getCredentials();
      if (is_object($credentials)) {
        $credentials = get_object_vars($credentials);
      }
    }
    
    // Ticket erstelen
    $ticketData = $this->getBusiness()->createRenderTicket(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getType(),
        $validatedRequest->getId(),
        $validatedRequest->getProtect(),
        $credentials,
        $validatedRequest->getTicketLifetime(),
        $validatedRequest->getSessionLifetime(),
        $validatedRequest->getRemainingCalls()
    );
    
    $this->responseData->setData(new Response\Ticket($ticketData));
  }
  
  /**
   * check if the session should be closed
   *
   * @param string $action  Method name of action
   * @return boolean
   */
  protected function shouldTheSessionBeClosedBeforeActionDispatched($action)
  {
    if ($action == 'ticketAction') {
      return false;
    }
    
    return true;
  }
}
