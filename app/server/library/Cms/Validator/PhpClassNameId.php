<?php


namespace Cms\Validator;

/**
 * Class PhpClassNameId
 *
 * @package Cms\Validator
 */
class PhpClassNameId extends \Zend_Validate_StringLength
{
  const INVALID = 'idInvalid';
  const TOO_SHORT = 'idTooShort';
  const TOO_LONG  = 'idTooLong';

  /**
   * constructor
   */
  public function __construct()
  {
    parent::__construct(array('max' => 255, 'min' => 1));
  }

  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => "Invalid id '%value%' given.",
  );

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
    if (!preg_match('/^[a-z][a-z0-9_]*$/', $value)) {
      $this->_error(self::INVALID);
      return false;
    }

    if (!parent::isValid($value)) {
      return false;
    }

    return true;
  }
}
