<?php


namespace Render;

class UnitFactory
{

  /**
   * @param array $content
   * @param array $defaultFromValues
   *
   * @return Unit
   */
  public function contentToUnit(
      array &$content,
      array $defaultFromValues = array()
  ) {
    return new Unit(
        $content['id'],
        $content['moduleId'],
        $content['name'],
        $this->getFormValues($content, $defaultFromValues),
        $this->isGhostContainer($content),
        $this->getTemplateUnitId($content),
        $this->getHtmlClass($content)
    );
  }

  /**
   * @param array $content
   * @param array $defaultValues
   *
   * @return array
   */
  protected function getFormValues(array &$content, array &$defaultValues)
  {
    if (!$this->hasFormValues($content)) {
      return $defaultValues;
    }

    return array_replace($defaultValues, $content['formValues']);
  }

  /**
   * @param array $content
   *
   * @return bool
   */
  protected function hasFormValues(array &$content)
  {
    return isset($content['formValues']) && is_array($content['formValues']);
  }

  /**
   * @param array $content
   *
   * @return string|null
   */
  protected function getTemplateUnitId(array &$content)
  {
    if (!isset($content['templateUnitId'])) {
      return null;
    }
    return  $content['templateUnitId'];
  }

  /**
   * @param array $content
   *
   * @return boolean
   */
  protected function isGhostContainer(array &$content)
  {
    return (isset($content['ghostContainer']) && $content['ghostContainer']);
  }

  /**
   * @param array $content
   *
   * @return string
   */
  protected function getHtmlClass(array &$content)
  {
    if (!isset($content['htmlClass'])) {
      return '';
    }
    return  $content['htmlClass'];
  }

}
