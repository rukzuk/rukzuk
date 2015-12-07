<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer Album
 *
 * @package      Orm
 * @subpackage   Data
 */
class Album implements UuidMarker
{
  const ID_PREFIX = 'ALBUM-';
  const ID_SUFFIX = '-ALBUM';
}
