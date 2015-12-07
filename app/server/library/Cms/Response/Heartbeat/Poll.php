<?php
namespace Cms\Response\Heartbeat;

use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer Poll
 *
 * @package      Cms
 * @subpackage   Response
 */
class Poll implements IsResponseData
{
  /**
   * @var array
   */
  public $expired = array();
  /**
   * @var array
   */
  public $invalid = array();

  /**
   * @param array $locks
   */
  public function __construct(array $data = array())
  {
    if (isset($data['expired'])) {
      $this->setExpired($data['expired']);
    }
    if (isset($data['invalid'])) {
      $this->setInvalid($data['invalid']);
    }
  }

  /**
   * @return array
   */
  public function getExpired()
  {
    return $this->expired;
  }
  
  /**
   * @param array $expired
   */
  protected function setExpired(array $expired)
  {
    $this->expired = $expired;
  }

  /**
   * @return array
   */
  public function getInvalid()
  {
    return $this->invalid;
  }

  /**
   * @param array $invalid
   */
  protected function setInvalid(array $invalid)
  {
    $this->invalid = $invalid;
  }
}
