<?php


namespace Render\InfoStorage\ContentInfoStorage;

interface IContentInfoStorage
{
  /**
   * @param  string $templateId of the template
   *
   * @return array
   */
  public function getTemplateContent($templateId);
}
