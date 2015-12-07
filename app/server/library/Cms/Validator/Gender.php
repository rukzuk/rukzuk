<?php
namespace Cms\Validator;

use \Orm\Entity\User;

/**
 * Gender Validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class Gender extends \Zend_Validate_Abstract
{
  const INVALID = 'genderInvalid';
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => "Gender '%value%' not supported"
  );
  /**
   * @var array
   */
  protected $validGenders;

  public function __construct()
  {
    $this->validGenders = array(
      User::GENDER_MALE,
      User::GENDER_FEMALE
    );
  }

  /**
   * @param  string $value
   * @return boolean
   */
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (\in_array($value, $this->validGenders)) {
      return true;
    }

    $this->_error(self::INVALID);
    
    return false;
  }
}
