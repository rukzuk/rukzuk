<?php


namespace Render\InfoStorage\ContentInfoStorage;

use Render\InfoStorage\ContentInfoStorage\Exceptions\TemplateDoesNotExists;

class ArrayBasedContentInfoStorage implements IContentInfoStorage
{
  /**
   * @var array
   */
  protected $templates;

  /*
   * @param array  $templates array(templateId => array(...))
   */
  public function __construct(array &$templates)
  {
    $this->templates = $templates;
  }

  /**
   * @param  string $templateId of the template
   *
   * @throws TemplateDoesNotExists
   * @return array
   */
  public function getTemplateContent($templateId)
  {
    if (!isset($this->templates[$templateId]) || !is_array($this->templates[$templateId])) {
      throw new TemplateDoesNotExists();
    }
    if (isset($this->templates[$templateId]['content']) && is_array($this->templates[$templateId]['content'])) {
      return $this->templates[$templateId]['content'];
    }
    return array();
  }
}
