<?php
namespace Cms\Response\User;

use Cms\Response\IsResponseData;

class RenewPassword implements IsResponseData
{
  public $redirect = null;

  public function __construct($data)
  {
    if (isset($data['redirect'])) {
      $this->setRedirect($data['redirect']);
    }
  }

  public function getRedirect()
  {
    return $this->redirect;
  }

  public function setRedirect($redirect)
  {
    $this->redirect = $redirect;
  }
}
