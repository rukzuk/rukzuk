<?php
use Cms\Controller as Controller;
use \Dual\Render\RenderObject as RenderObject;

/**
 * Render Controller
 *
 * @package      Application
 * @subpackage   Controller
 */

class RenderController extends Controller\Action
{
  public function init()
  {
    parent::init();
    $this->_helper->viewRenderer->setNoRender();
    $this->initBusiness('Render');
  }

  public function postDispatch()
  {
  }

  /**
   * Rendert ein Template anhand der im Data uebergebenen Struktur oder der
   * uebergebenen Template-ID
   *
   * Wird beides uebergeben, so hat Data eine hoehere Priorisierung
   */
  public function templateAction()
  {
    $validatedRequest = $this->getValidatedRequest('Render', 'Template');

    $check = array(
      'id'        => $validatedRequest->getTemplateId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('template', $check);

    if ($validatedRequest->getMode() == \Render\RenderContext::RENDER_MODE_EDIT) {
      $response = $this->getResponse();
      $response->setHeader('X-XSS-Protection', '0', true);
    }

    $this->getBusiness()->renderTemplate(
        $validatedRequest->getTemplateId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getData(),
        $validatedRequest->getMode(),
        RenderObject::CODE_TYPE_HTML
    );
  }

  /**
   * Rendert das CSS ein Template anhand der im Data uebergebenen Struktur oder der
   * uebergebenen Template-ID
   *
   * Wird beides uebergeben, so hat Data eine hoehere Priorisierung
   */
  public function templatecssAction()
  {
    $validatedRequest = $this->getValidatedRequest('Render', 'Template');

    $check = array(
      'id'        => $validatedRequest->getTemplateId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('templateCss', $check);

    $response = $this->getResponse();
    $response->setHeader('Content-Type', 'text/css', true);
    if ($validatedRequest->getMode() == \Render\RenderContext::RENDER_MODE_EDIT) {
      $response->setHeader('X-XSS-Protection', '0', true);
    }

    $this->getBusiness()->renderTemplate(
        $validatedRequest->getTemplateId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getData(),
        $validatedRequest->getMode(),
        RenderObject::CODE_TYPE_CSS
    );
  }

  /**
   * Rendert eine Page
   *
   * Wird der Parameter Data uebergeben, so wird der uebergebene Content
   * gerendert und die Page-ID fuer den Navigationsaufbau benoetigt. Wird kein
   * Data uebergeben, so wird der Page Content aus der DB verwendet
   */
  public function pageAction()
  {
    $validatedRequest = $this->getValidatedRequest('Render', 'Page');

    $check = array(
      'id'        => $validatedRequest->getPageId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('page', $check);

    if ($validatedRequest->getMode() == \Render\RenderContext::RENDER_MODE_EDIT) {
      $response = $this->getResponse();
      $response->setHeader('X-XSS-Protection', '0', true);
    }

    $this->getBusiness()->renderPage(
        $validatedRequest->getPageId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getData(),
        $validatedRequest->getMode(),
        null,
        RenderObject::CODE_TYPE_HTML
    );
  }

  /**
   * Rendert das CSS eine Page
   *
   * Wird der Parameter Data uebergeben, so wird der uebergebene Content
   * gerendert und die Page-ID fuer den Navigationsaufbau benoetigt. Wird kein
   * Data uebergeben, so wird der Page Content aus der DB verwendet
   */
  public function pagecssAction()
  {
    $validatedRequest = $this->getValidatedRequest('Render', 'Page');

    $check = array(
      'id'        => $validatedRequest->getPageId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('pageCss', $check);

    $response = $this->getResponse();
    $response->setHeader('Content-Type', 'text/css', true);
    if ($validatedRequest->getMode() == \Render\RenderContext::RENDER_MODE_EDIT) {
      $response->setHeader('X-XSS-Protection', '0', true);
    }

    $this->getBusiness()->renderPage(
        $validatedRequest->getPageId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getData(),
        $validatedRequest->getMode(),
        null,
        RenderObject::CODE_TYPE_CSS
    );
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
