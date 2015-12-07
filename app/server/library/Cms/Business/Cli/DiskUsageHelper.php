<?php

namespace Cms\Business\Cli;

use Cms\Business\Cli as CliBusiness;
use Seitenbau\Registry;

class DiskUsageHelper
{

  public static function logDiskUsage()
  {
    Registry::getActionLogger()->logAction(CliBusiness::SPACE_DISK_USAGE_ACTION, array('disk_usage' => self::getDiskUsage(), 'metric_value' => 'disk_usage'));
  }

  /**
   * Calls unix 'du' to measure the disk usage of this space (aka instance)
   * @returns int usage in KB
   * @throws \Exception
   */
  public static function getDiskUsage()
  {
    $cmd = 'du -s -k ' . escapeshellarg(DOCUMENT_ROOT) . ' 2> /dev/null';
    $du_str = @exec($cmd);
    if (preg_match('/^[0-9]+/', trim($du_str), $match)) {
      $usage = $match[0];
      return intval($usage);
    } else {
      throw new \Exception('Failed to get disk usage of space');
    }
  }
}
