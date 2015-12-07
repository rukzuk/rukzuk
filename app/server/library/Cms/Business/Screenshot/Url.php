<?php
namespace Cms\Business\Screenshot;

use Seitenbau\Registry as Registry;
use Seitenbau\RandomGenerator as RandomGenerator;

/**
 *
 *
 * @package      Cms
 * @subpackage   Business
 */
class Url
{
  protected $websiteId;
  protected $type;
  protected $id;
  
  /**
   *
   */
  public function __construct($websiteId, $type, $id)
  {
    $this->websiteId = $websiteId;
    $this->type = $type;
    $this->id = $id;
  }
  
  /**
   * @return string
   */
  public function __toString()
  {
    return $this->get();
  }

  /**
   * @return string
   */
  public function get($useInternalUrl = false)
  {
    $config = Registry::getConfig();
    
    $credential = null;
    if ($config->screens->accessticket->authentication) {
      $credential = array (
        'username' => RandomGenerator::generateString(10),
        'password' => RandomGenerator::generateString(10),
      );
    }

    $requestConfig = $this->getShootRequest();
    
    \Seitenbau\Registry::getLogger()->logData(__METHOD__, __LINE__, "ShootRequest:", $requestConfig, \Seitenbau\Log::DEBUG);
    
    $ticketUrl = '';
    if (isset($requestConfig) && !empty($requestConfig)) {
      $ticketBusiness = $this->newTicketInstance();
      $ticketUrl = $ticketBusiness->createTicketUrl(
          $this->websiteId,
          false, // Forwarding
          true,
          $requestConfig,
          $config->screens->accessticket->ticketLifetime,
          $config->screens->accessticket->remainingCalls,
          $config->screens->accessticket->sessionLifetime,
          $credential,
          $credential,
          $useInternalUrl
      );
    }
    
    return $ticketUrl;
  }
  
  /**
   *
   *
   * @return \Cms\Bussiness\Ticket
   */
  protected function newTicketInstance()
  {
    return new \Cms\Business\Ticket('Ticket');
  }
 
  /**
   *
   *
   * @return string
   */
  protected function getShootRequest()
  {
    $params = array();
    
    switch($this->type) {
      case 'page':
          $params = array(
            'websiteid' => $this->websiteId,
            'pageid' => $this->id,
            'mode' => 'preview',
          );
            break;
      case 'template':
          $params = array(
            'websiteid' => $this->websiteId,
            'templateid' => $this->id,
            'mode' => 'preview',
          );
            break;
      
      default:
            return;
        break;
    }
    $paramsAsJson = \Zend_Json::encode($params);
    return array(
      'controller' => 'render',
      'action' => $this->type,
      'params' => array( Registry::getConfig()->request->parameter => $paramsAsJson),
    );
  }
}
