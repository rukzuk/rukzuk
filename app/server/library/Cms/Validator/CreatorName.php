<?php
namespace Cms\Validator;

use Cms\Creator\CreatorFactory;

/**
 * Creator Name Validator
 *
 * @package      Cms
 * @subpackage   Validator
 */

class CreatorName extends \Zend_Validate_Abstract
{
  const INVALID = 'typeInvalid';

  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => "Creator '%value%' not supported"
  );

  /**
   * @param  string $value
   * @return boolean
   */
  public function isValid($value)
  {
    $this->_setValue($value);
    if (!CreatorFactory::creatorExists($value)) {
      return false;
    } else {
      return true;
    }
  }
}
