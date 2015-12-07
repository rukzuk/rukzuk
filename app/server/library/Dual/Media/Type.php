<?php
namespace Dual\Media;

// TODO: move this class out of Dual

/**
 * Type
 *
 * @package      Dual
 * @subpackage   Media
 */
class Type
{
  const TYPE_IMAGE        = 'image';
  const TYPE_DOWNLOAD     = 'download';
  const TYPE_MULTIMEDIA   = 'multimedia';
  const TYPE_MISC         = 'misc';

  /**
   * @var array
   */
  protected static $fileExtensionTypeMappings = array(
    'gif'   => self::TYPE_IMAGE,
    'png'   => self::TYPE_IMAGE,
    'jpg'   => self::TYPE_IMAGE,
    'jpeg'  => self::TYPE_IMAGE,
    'svg'   => self::TYPE_IMAGE,

    'pdf'   => self::TYPE_DOWNLOAD,
    'xls'   => self::TYPE_DOWNLOAD,
    'doc'   => self::TYPE_DOWNLOAD,
    'ppt'   => self::TYPE_DOWNLOAD,
    'css'   => self::TYPE_DOWNLOAD,

    'mp3'   => self::TYPE_MULTIMEDIA,
    'wav'   => self::TYPE_MULTIMEDIA,
    'mov'   => self::TYPE_MULTIMEDIA,
    'mp4'   => self::TYPE_MULTIMEDIA,
    'flv'   => self::TYPE_MULTIMEDIA,
  );
  /**
   * @param  string $extension
   * @return string
   */
  public static function getByExtension($extension)
  {
    if (!empty($extension)) {
      $extension = strtolower($extension);
      if (!empty(self::$fileExtensionTypeMappings[$extension])) {
        return self::$fileExtensionTypeMappings[$extension];
      }
    }
    return self::TYPE_MISC;
  }
}
