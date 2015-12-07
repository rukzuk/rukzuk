<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer Group
 *
 * @package      Orm
 * @subpackage   Data
 */
class Group implements UuidMarker
{
  const ID_PREFIX = 'GROUP-';
  const ID_SUFFIX = '-GROUP';
}
