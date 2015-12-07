<?php
namespace Cms\Request\Cli;

use Cms\Request\UnSetAwareBase;

/**
 * Cli/BuildTheme Request
 *
 * @package      Cms
 * @subpackage   Request
 */
class BuildTheme extends UnSetAwareBase
{
  protected function getSupportedProperties()
  {
    return array(
      'content',
      'file',
    );
  }
}
