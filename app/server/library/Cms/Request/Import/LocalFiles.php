<?php

namespace Cms\Request\Import;

use Cms\Request\UnSetAwareBase;

/**
 * @package      Cms\Request
 * @subpackage   Import
 *
 * @SWG\Model(id="Request/Import/LocalFiles")
 * @SWG\Property(name="localid", type="string", required=true, description="ID of the local import")
 * @SWG\Property(name="allowedtype", type="string", required=false, description="allowed import type")
 * @SWG\Property(name="websiteid", type="string", required=false, description="ID of the website")
 * @SWG\Property(name="websitename", type="string", required=false, description="new name of the website")
 */
class LocalFiles extends UnSetAwareBase
{
  protected function getSupportedProperties()
  {
    return array(
      'websiteid',
      'localid',
      'allowedtype',
      'websitename',
    );
  }
}
