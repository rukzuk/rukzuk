<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer Group
 *
 * @package      Orm
 * @subpackage   Data
 */
class User implements UuidMarker
{
  const ID_PREFIX = 'USER-';
  const ID_SUFFIX = '-USER';
}
