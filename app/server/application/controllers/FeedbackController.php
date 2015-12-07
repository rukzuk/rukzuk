<?php
use Cms\Controller as Controller;
use Cms\Response\Feedback as Response;

/**
 * Feedback Controller
 *
 * @package      Application
 * @subpackage   Controller
 */

class FeedbackController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Feedback');
    parent::init();
  }
  
  public function sendAction()
  {
    $validatedRequest = $this->getValidatedRequest('Feedback', 'Send');
    
    $attributes = array(
      'subject' => $validatedRequest->getSubject(),
      'content' => $validatedRequest->getBody(),
      'email' => $validatedRequest->getEmail(),
      'clientErrors' => $validatedRequest->getClientErrors(),
      'platform'  => $validatedRequest->getPlatform(),
      'userAgent' => $validatedRequest->getUserAgent()
    );
    $this->getBusiness()->send($attributes);
  }
}
