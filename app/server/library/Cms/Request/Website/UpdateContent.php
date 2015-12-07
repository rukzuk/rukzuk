<?php

namespace Cms\Request\Website;

use Cms\Request\UnSetAwareBase;

/**
 * @package      Cms\Request
 * @subpackage   Website
 *
 * @SWG\Model(id="Request/Website/UpdateContent")
 * @SWG\Property(name="websiteid", type="string", required=false, description="ID of the website")
 */
class UpdateContent extends UnSetAwareBase
{
  protected function getSupportedProperties()
  {
    return array(
      'websiteid',
    );
  }
}
