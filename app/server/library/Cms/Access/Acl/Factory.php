<?php
namespace Cms\Access\Acl;

use \Seitenbau\FactoryBase as FactoryBase;

/**
 * Factory
 *
 * @package      Cms
 * @subpackage   Access\Acl
 */
class Factory extends FactoryBase
{
  const DEFAULT_CLASS = 'DefaultAcl';
  const CLASS_PATH = '\Cms\Access\Acl\Type\%s';
}
