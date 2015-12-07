<?php
namespace Cms\Request\Shortener;

use Cms\Request\Base;

/**
 * Ticket Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class Ticket extends Base
{
  /**
   * @var string
   */
  private $ticketId = null;
  
  /**
   * @var string
   */
  private $username = null;
  
  /**
   * @var string
   */
  private $password = null;

  protected function initParamsFromRequest(\Zend_Controller_Request_Abstract $request = null)
  {
    parent::initParamsFromRequest($request);

    if ($request != null && $request->getParam('ticket') !== null) {
        $this->setRequestParam('ticket', $request->getParam('ticket'));
    }
  }
  
  /**
   * @return string
   */
  public function getTicketId()
  {
    return $this->ticketId;
  }

  /**
   * @param string $ticketId
   */
  public function setTicketId($ticketId)
  {
    $this->ticketId = $ticketId;
  }
  
  /**
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * @param string $username
   */
  public function setUsername($username)
  {
    $this->username = $username;
  }
  
  /**
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * @param string $password
   */
  public function setPassword($password)
  {
    $this->password = $password;
  }
  
  protected function setValues()
  {
    $this->setTicketId($this->getRequestParam('ticket'));
    $this->setUsername($this->getRequestParam('username'));
    $this->setPassword($this->getRequestParam('password'));
  }
}
