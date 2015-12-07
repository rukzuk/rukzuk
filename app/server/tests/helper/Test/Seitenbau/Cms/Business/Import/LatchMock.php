<?php
namespace Test\Seitenbau\Cms\Business\Import;


/**
 * Latch import mock class
 *
 * @package    Seitenbau

 */
class LatchMock extends \Cms\Business\Import\Latch
{
  protected static $testLatchDateAndTime = null;
  
  /**
   * @return integer
   */
  protected function getLatchDateAndTime()
  {
    if (isset(self::$testLatchDateAndTime)) {
      return self::$testLatchDateAndTime;
    }
    return parent::getLatchDateAndTime();
  }
  
  /**
   * set the latch date/time
   * 
   * @param integer $latchDateAndTime
   */
  public static function setTestLatchDateAndTime($latchDateAndTime) {
    self::$testLatchDateAndTime = $latchDateAndTime;
  }
  
  /**
   * clear the latch date/time
   */
  public static function clearTestLatchDateAndTime() {
    self::$testLatchDateAndTime = null;
  }
}