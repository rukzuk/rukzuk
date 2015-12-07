<?php
namespace Cms\Access\Auth;

/**
 * @package      Cms
 * @subpackage   Auth
 */
class Result extends \Zend_Auth_Result
{
  /**
   * Sets the result code, identity, and failure messages
   *
   * @param  int   $code
   * @param  mixed $identity
   * @param  array $messages
   */
  public function __construct($code, $identity, array $messages = array())
  {
    $code = (int) $code;

    if ($code < self::FAILURE_UNCATEGORIZED) {
      $code = self::FAILURE;
    } elseif ($code > self::SUCCESS) {
      $code = 1;
    }

    $this->_code     = $code;
    $this->_identity = $identity;
    $this->_messages = $messages;
  }

  /**
   * @params string $backendName
   */
  public function addBackendName($backendName)
  {
    if (!isset($this->_identity['backends']) || !is_array($this->_identity['backends'])) {
      $this->_identity['backends'] = array();
    }
    $this->_identity['backends'][$backendName] = true;
  }

  /**
   * @params string $backendName
   */
  public function removeBackendName($backendName)
  {
    if (!isset($this->_identity['backends']) || !is_array($this->_identity['backends'])) {
      $this->_identity['backends'] = array();
    }
    if (isset($this->_identity['backends'][$backendName])) {
      unset($this->_identity['backends'][$backendName]);
    }
  }

  /**
   * @params string $backendName
   * @param $backendName
   *
   * @return boolean
   */
  public function isCreatedFromBackend($backendName)
  {
    return self::isIdentityCreatedFromBackend($backendName, $this->_identity);
  }

  /**
   * @params string $backendName
   * @params array  $identity
   * @param       $backendName
   * @param array $identity
   *
   * @return boolean
   */
  public static function isIdentityCreatedFromBackend($backendName, array $identity)
  {
    if (!isset($identity['backends']) || !is_array($identity['backends'])) {
      return false;
    }
    return (isset($identity['backends'][$backendName]) && $identity['backends'][$backendName] === true);
  }
}
