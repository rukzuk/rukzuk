<?php
namespace Cms\Validator;

/**
 * ModuleId Validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class ModuleId extends \Zend_Validate_Abstract
{
  const INVALID = 'moduleidInvalid';

  /**
   * @var boolean
   */
  protected $legacySupport = false;

  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => "Invalid module id '%value%' given.",
  );

  /**
   * @param boolean $legacySupport
   */
  public function __construct($legacySupport)
  {
    $this->legacySupport = $legacySupport;
  }

  /**
   * @param  string $value
   * @return boolean
   */
  public function isValid($value)
  {
    if (!is_string($value)) {
      $this->_error(self::INVALID);
      return false;
    }
    $this->_setValue($value);
    
    // php class name in lowercase without [\x7f-\xff] characters and [_] characters at start
    // (php class name regexp from php.net: /^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/)
    if (preg_match('/^[a-z][a-z0-9_]*$/', $value)) {
      return true;
    }
    
    // legacy id support
    if ($this->legacySupport) {
      $legacyIdRegex = '/^MODUL-[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}-MODUL$/';
      if (preg_match($legacyIdRegex, $value)) {
        return true;
      }
    }
    
    $this->_error(self::INVALID);
    return false;
  }
}
