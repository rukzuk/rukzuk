<?php
namespace Test\Seitenbau\Cms\Service;


/**
 * Builder mock class
 *
 * @package     Seitenbau
 * @subpackage  Service
 */
class BuilderMock extends \Cms\Service\Builder
{
  protected static $buildTimestamp = null;
  protected static $writeBuildVersionFileCallback = null;
  protected static $lastWebsiteCreatorDirectoryMock = null;
  
  
  /**
   * @return integer
   */
  protected function getBuildTimestamp()
  {
    if (isset(self::$buildTimestamp)) {
      return self::$buildTimestamp;
    }
    return parent::getBuildTimestamp();
  }
  
  /**
   * set the build timestamp
   * 
   * @param integer $buildTimestamp
   */
  public static function setBuildTimestamp($buildTimestamp) {
    self::$buildTimestamp = $buildTimestamp;
  }
  
  /**
   * clear the build timestamp
   */
  public static function clearBuildTimestamp() {
    self::$buildTimestamp = null;
  }

  /**
   * @param  string $buildVersionFile
   * @param  string $buildVersionContent
   * @return boolean
   */
  protected function writeBuildVersionFile($buildVersionFile, $buildVersionContent)
  {
    if (isset(self::$writeBuildVersionFileCallback)) {
      $callbackFnc = self::$writeBuildVersionFileCallback;
      return $callbackFnc($buildVersionFile, $buildVersionContent);
    }
    return parent::writeBuildVersionFile($buildVersionFile, $buildVersionContent);
  }
  
  /**
   * set the build wirte build version file callback
   * 
   * @param callable $callbackFnc
   */
  public static function setWriteBuildVersionFileCallback($callbackFnc) {
    self::$writeBuildVersionFileCallback = $callbackFnc;
  }
  
  /**
   * clear the build wirte build version file callback
   */
  public static function clearWriteBuildVersionFileCallback() {
    self::$writeBuildVersionFileCallback = null;
  }
  
  /**
   * @return string 
   */
  public function getLastWebsiteCreatorDirectory()
  {
     if (isset(self::$lastWebsiteCreatorDirectoryMock)) {
      return self::$lastWebsiteCreatorDirectoryMock;
    }
    return parent::getLastWebsiteCreatorDirectory();
  }
  
  /**
   * set the last website creator directory variable
   * 
   * @param integer $lastWebsiteCreatorDirectoryMock
   */
  public static function setLastWebsiteCreatorDirectoryMock($lastWebsiteCreatorDirectoryMock) {
    self::$lastWebsiteCreatorDirectoryMock = $lastWebsiteCreatorDirectoryMock;
  }
  
  /**
   * clear the last website creator directory variable
   */
  public static function clearLastWebsiteCreatorDirectoryMock() {
    self::$lastWebsiteCreatorDirectoryMock = null;
  }
}