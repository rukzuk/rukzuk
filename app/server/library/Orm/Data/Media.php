<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer Media
 *
 * @package      Orm
 * @subpackage   Data
 */
class Media implements UuidMarker
{
  const ID_PREFIX = 'MDB-';
  const ID_SUFFIX = '-MDB';
}
