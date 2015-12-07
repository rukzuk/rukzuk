<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer Website
 *
 * @package      Orm
 * @subpackage   Data
 */
class Site implements UuidMarker
{
  const ID_PREFIX = 'SITE-';
  const ID_SUFFIX = '-SITE';
}
