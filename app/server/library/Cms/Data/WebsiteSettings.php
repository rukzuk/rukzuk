<?php

namespace Cms\Data;

use Cms\Dao\Base\SourceItem;

/**
 * Website settings data class
 *
 * @package Cms\Data
 */
class WebsiteSettings
{
  const SOURCE_REPOSITORY = 'repo';
  const SOURCE_DATA = 'data';

  /**
   * @var string $websiteId
   */
  private $websiteId;

  /**
   * @var string $id
   */
  private $id;

  /**
   * @var \stdClass $name
   */
  private $name = null;

  /**
   * @var \stdClass $description
   */
  private $description = null;

  /**
   * @var string $version
   */
  private $version = null;

  /**
   * @var mixed $form
   */
  private $form = null;

  /**
   * @var mixed $formValues
   */
  private $formValues = null;

  /**
   * @var boolean $isReadonly
   */
  private $isReadonly = true;

  /**
   * @var string $sourceType
   */
  private $sourceType = null;

  /**
   * @var SourceItem $source
   */
  private $source = null;

  /**
   * @param string $websiteId
   *
   * @return WebsiteSettings
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
    return $this;
  }

  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param string $id
   *
   * @return WebsiteSettings
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param \stdClass $name
   *
   * @return WebsiteSettings
   */
  public function setName(\stdClass $name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * @return \stdClass
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param \stdClass $description
   *
   * @return WebsiteSettings
   */
  public function setDescription(\stdClass $description)
  {
    $this->description = $description;
    return $this;
  }

  /**
   * @return \stdClass
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @param string $version
   *
   * @return $this
   */
  public function setVersion($version)
  {
    $this->version = $version;
    return $this;
  }

  /**
   * @return string $version
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * @param mixed $form
   *
   * @return $this
   */
  public function setForm($form)
  {
    $this->form = $form;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getForm()
  {
    return $this->form;
  }

  /**
   * @param mixed $formValues
   *
   * @return $this
   */
  public function setFormValues($formValues)
  {
    $this->formValues = $formValues;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getFormValues()
  {
    return $this->formValues;
  }

  /**
   * @param boolean $isReadonly
   *
   * @return WebsiteSettings
   */
  public function setReadonly($isReadonly)
  {
    $this->isReadonly = (bool)$isReadonly;
    return $this;
  }

  /**
   * @return boolean
   */
  public function isReadonly()
  {
    return $this->isReadonly;
  }

  /**
   * @param string $sourceType
   *
   * @return WebsiteSettings
   */
  public function setSourceType($sourceType)
  {
    $this->sourceType = $sourceType;
    return $this;
  }

  /**
   * @return string
   */
  public function getSourceType()
  {
    return $this->sourceType;
  }

  /**
   * @param SourceItem $source
   *
   * @return WebsiteSettings
   */
  public function setSource(SourceItem $source)
  {
    $this->source = $source;
    return $this;
  }

  /**
   * @return SourceItem
   */
  public function getSource()
  {
    return $this->source;
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'websiteId' => $this->getWebsiteid(),
      'id' => $this->getId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'version' => $this->getVersion(),
      'form' => $this->getForm(),
      'formValues' => $this->getFormValues(),
      'readonly' => $this->isReadonly(),
      'sourceType' => $this->getSourceType(),
      'source' => $this->getSource(),
    );
  }

  /**
   * @return array
   */
  public function getExportColumnsAndValues()
  {
    return array(
      'id' => $this->getId(),
      'formValues' => $this->getFormValues(),
    );
  }
}
