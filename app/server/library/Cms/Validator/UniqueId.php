<?php
namespace Cms\Validator;

/**
 * UniqueId Validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class UniqueId extends \Zend_Validate_Abstract
{
  const INVALID = 'uniqueidInvalid';
  /**
   * @var string
   */
  private $prefix;
  /**
   * @var string
   */
  private $suffix;
  /**
   * @var string
   */
  private $idRegex;
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => self::INVALID
  );

  /**
   * @param string        $prefix
   * @param string        $suffix
   * @param null|string   $idRegex
   */
  public function __construct($prefix, $suffix, $idRegex = null)
  {
    $this->prefix = $prefix;
    $this->suffix = $suffix;
    if (is_string($idRegex)) {
      $this->idRegex = $idRegex;
    } else {
      $this->idRegex = '/^%s[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}%s$/';
    }
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
    $idRegex = sprintf($this->idRegex, $this->prefix, $this->suffix);
    if (!preg_match($idRegex, $value)) {
      $this->_error(self::INVALID);
      return false;
    }
    return true;
  }
}
