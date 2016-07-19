<?php


namespace Render;

/**
 * Unit data transfer object class.
 * Normally used as a API parameter.
 *
 * @package Render
 */
class Unit
{

  protected $id;
  protected $moduleId;
  protected $name;
  protected $htmlClass = '';
  protected $styleSets = '';
  protected $templateUnitId = null;
  protected $ghostContainer = false;
  protected $formValues = array();

  /**
   * @param string      $id
   * @param string      $moduleId
   * @param string      $name
   * @param array       $formValues
   * @param bool        $ghostContainer
   * @param string|null $templateUnitId
   * @param string      $htmlClass
   */
  public function __construct(
      $id,
      $moduleId,
      $name,
      array $formValues = array(),
      $ghostContainer = false,
      $templateUnitId = null,
      $htmlClass = '',
      $styleSets = ''
  ) {
    $this->id = $id;
    $this->moduleId = $moduleId;
    $this->name = $name;
    $this->formValues = $formValues;
    $this->ghostContainer = $ghostContainer;
    $this->templateUnitId = $templateUnitId;
    $this->htmlClass = $htmlClass;
    $this->styleSets = $styleSets;
  }

  /**
   * @return array
   */
  public function getFormValues()
  {
    return $this->formValues;
  }

  /**
   * @return boolean
   */
  public function isGhostContainer()
  {
    return $this->ghostContainer;
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getModuleId()
  {
    return $this->moduleId;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return null|string
   */
  public function getTemplateUnitId()
  {
    return $this->templateUnitId;
  }

  /**
   * @return string
   */
  public function getHtmlClass()
  {
    return $this->htmlClass;
  }

  /**
   * @return string
   */
  public function getStyleSets()
  {
    return $this->styleSets;
  }


  public function toArray()
  {
    return array(
      'id' => $this->getId(),
      'moduleId' => $this->getModuleId(),
      'name' => $this->getName(),
      'templateUnitId' => $this->getTemplateUnitId(),
      'ghostContainer' => $this->isGhostContainer(),
      'formValues' => $this->getFormValues(),
      'htmlClass' => $this->getHtmlClass(),
      'styleSets' => $this->getStyleSets()
    );
  }
}
