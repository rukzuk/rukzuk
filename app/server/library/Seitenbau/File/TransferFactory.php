<?php
namespace Seitenbau\File;

/**
 * File transfer factory
 *
 * @package      Seitenbau
 */

class TransferFactory
{
  protected static $_adapter;

  /**
   * Returns the instance of the transfer adapter
   *
   * @return Zend_File_Transfer_Adapter_Abstract
   */
  public static function getAdapter()
  {
    if (self::$_adapter === null) {
        self::setAdapter(new \Zend_File_Transfer_Adapter_Http());
    }

    return self::$_adapter;
  }

  /**
   * Sets the transfer adapter
   *
   * @param Zend_File_Transfer_Adapter_Abstract $value
   */
  public static function setAdapter(\Zend_File_Transfer_Adapter_Abstract $value)
  {
    self::$_adapter = $value;
  }

  /**
   * Clears out the set transfer adapter
   */
  public static function clearAdapter()
  {
    self::$_adapter = null;
  }
}
