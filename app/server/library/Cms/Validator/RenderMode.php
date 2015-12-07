<?php
namespace Cms\Validator;

use \Dual\Render\RenderContext as RenderContext;

/**
 * Render Mode Validator
 *
 * @package      Cms
 * @subpackage   Validator
 */

class RenderMode extends \Zend_Validate_Abstract
{
  const INVALID = 'modeInvalid';

  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => "mode '%value%' not supported"
  );

  protected $validMode = array();

  public function __construct()
  {
    $this->validMode = array(
      RenderContext::MODE_EDIT,
      RenderContext::MODE_PREVIEW
    );
  }

  /**
   * @param  string $value
   * @return boolean
   */
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (\in_array($value, $this->validMode)) {
      return true;
    }

    $this->_error(self::INVALID);
    return false;
  }
}
