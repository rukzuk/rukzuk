<?php

namespace Cms\Request\Page;

use Cms\Request\UnSetAwareBase;

/**
 * @package      Cms\Request
 * @subpackage   Page
 *
 * @SWG\Model(id="Request/Page/EditMeta")
 * @SWG\Property(name="runid", type="string", required=true)
 * @SWG\Property(name="id", type="string", required=true, description="ID of the page")
 * @SWG\Property(name="websiteid", type="string", required=true, description="ID of the associated website")
 * @SWG\Property(name="mediaid", type="string", required=false, description="new media id")
 * @SWG\Property(name="name", type="string", required=false, description="new page name")
 * @SWG\Property(name="description", type="string", required=false, description="new page description")
 * @SWG\Property(name="innavigation", type="string", required=false, description="should the page show in navigation")
 * @SWG\Property(name="navigationtitle", type="string", required=false, description="new page title shown in navigation")
 * @SWG\Property(name="date", type="string", required=false, description="new page date")
 * @SWG\Property(name="pageattributes", type="string", required=false, description="new page attributes")
 */
class EditMeta extends UnSetAwareBase
{
  protected function transformProperties()
  {
    if ($this->hasProperty('pageattributes')) {
      $this->setPageAttributes($this->properties['pageattributes']);
    }
  }

  protected function setPageAttributes($pageAttributes)
  {
    if (is_object($pageAttributes) || is_array($pageAttributes)) {
      $this->properties['pageattributes'] = json_encode($pageAttributes);
    } else {
      $this->properties['pageattributes'] = $pageAttributes;
    }
  }

  protected function getSupportedProperties()
  {
    return array(
      'runid',
      'id',
      'websiteid',
      'mediaid',
      'name',
      'description',
      'innavigation',
      'navigationtitle',
      'date',
      'pageattributes',
    );
  }
}
