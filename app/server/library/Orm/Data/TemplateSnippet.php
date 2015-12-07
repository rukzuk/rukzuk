<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer TemplateSnippet
 *
 * @package      Orm
 * @subpackage   Data
 */
class TemplateSnippet implements UuidMarker
{
  const ID_PREFIX = 'TPLS-';
  const ID_SUFFIX = '-TPLS';
}
