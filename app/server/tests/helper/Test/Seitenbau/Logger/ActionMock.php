<?php
namespace Test\Seitenbau\Logger;

/**
 * Action logger test class
 *
 * @package     Seitenbau
 * @subpackage  Logger
 */
class ActionMock extends \Seitenbau\Logger\Action
{
  protected static $currentTimestampMock = null;

  /**
   * @return integer
   */
  protected function getCurrentTimestamp()
  {
    if (isset(self::$currentTimestampMock)) {
      return self::$currentTimestampMock;
    }
    return parent::getCurrentTimestamp();
  }
  
  /**
   * set the current timestamp
   * 
   * @param integer $currentTimestamp
   */
  public static function setCurrentTimestamp($currentTimestamp) {
    self::$currentTimestampMock = $currentTimestamp;
  }
  
  /**
   * clear the current timestamp
   */
  public static function clearCurrentTimestamp() {
    self::$currentTimestampMock = null;
  }

}