<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer Template
 *
 * @package      Orm
 * @subpackage   Data
 */
class Template implements UuidMarker
{
  const ID_PREFIX = 'TPL-';
  const ID_SUFFIX = '-TPL';
}
