<?php

namespace Render\Nodes;

use Render\InfoStorage\ContentInfoStorage\IContentInfoStorage;
use Render\InfoStorage\ContentInfoStorage\Exceptions\TemplateDoesNotExists;

class ContentIncludeNode extends DynamicHTMLNode
{
  /**
   * Returns the id of the template that should be included.
   *
   * @return string
   */
  public function getIncludeTemplateId()
  {
    $formValues = $this->getUnit()->getFormValues();
    if (isset($formValues['includeTemplateId']) && is_string($formValues['includeTemplateId'])) {
      return $formValues['includeTemplateId'];
    } else {
      return '';
    }
  }

}
