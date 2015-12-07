<?php
namespace Cms;

/**
 * Version
 *
 * @package      Cms
 */
class Version
{
  /**
   * Placeholder which is set when a version is builded
   */
  const CHANNEL = '@@BUILD.CHANNEL@@';
  
  /**
   * Placeholder which is set when a version is builded
   */
  const BUILD = '@@BUILD.VERSION@@';
  
  /**
   * Placeholder which is set when a version is builded
   */
  const VERSION_HASH = '@@BUILD.HASH@@';

  /**
   * Only increase the export version if the generated export file
   * can't import in older system versions
   */
  const EXPORT = '1.8.0';
  
  const MODE_FULL = 'full';

  public static function getRelease()
  {
    return self::RELEASE;
  }
  
  public static function getChannel()
  {
    if (substr(self::CHANNEL, 0, 2) === '@@') {
      return null;
    }
    return self::CHANNEL;
  }
  
  public static function getMode()
  {
    switch(CMS_MODE) {
      case self::MODE_FULL:
      default:
            return self::MODE_FULL;
        break;
    }
  }

  public static function isCliMode()
  {
    return (defined('CMS_ISCLI') && CMS_ISCLI === true);
  }
}
