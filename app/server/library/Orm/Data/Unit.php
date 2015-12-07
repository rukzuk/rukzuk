<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer Unit
 *
 * @package      Orm
 * @subpackage   Data
 */
class Unit implements UuidMarker
{
  const ID_PREFIX = 'MUNIT-';
  const ID_SUFFIX = '-MUNIT';
}
