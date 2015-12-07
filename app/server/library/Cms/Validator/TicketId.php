<?php
namespace Cms\Validator;

/**
 * TicketId Validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class TicketId extends \Zend_Validate_Abstract
{
  const INVALID = 'ticketInvalid';
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => self::INVALID
  );
  /**
   * @param  mixed $value
   * @return boolean
   */
  public function isValid($value)
  {
    if (!is_string($value)) {
      $this->_error(self::INVALID);
      return false;
    }
    $this->_setValue($value);
    if (!preg_match('/^[A-Za-z0-9\-]{10}$/', $value)) {
      $this->_error(self::INVALID);
      return false;
    }
    return true;
  }
}
