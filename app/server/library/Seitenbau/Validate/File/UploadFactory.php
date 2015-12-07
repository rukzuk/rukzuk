<?php
namespace Seitenbau\Validate\File;

/**
 * File upload validator factory
 *
 * @package      Seitenbau
 */

class UploadFactory
{
  protected static $_validator;

  /**
   * Returns the instance of the upload validator
   *
   * @return Zend_Validate_Abstract
   */
  public static function getValidator()
  {
    if (self::$_validator === null) {
        self::setValidator(new \Zend_Validate_File_Upload());
    }

    return self::$_validator;
  }

  /**
   * Sets the upload validator
   *
   * @param Zend_Validate_Abstract $value
   */
  public static function setValidator(\Zend_Validate_Abstract $value)
  {
    self::$_validator = $value;
  }

  /**
   * Clears out the set upload validator
   */
  public static function clearValidator()
  {
    self::$_validator = null;
  }
}
