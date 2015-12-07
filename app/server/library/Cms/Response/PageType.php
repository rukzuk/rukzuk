<?php


namespace Cms\Response;

use \Cms\Data\PageType as PageTypeData;

/**
 * @package      Cms\Response
 *
 * @SWG\Model(
 *      id="PageType",
 *      required="all")
 */
class PageType implements IsResponseData
{
  /**
   * @var string
   * @SWG\Property(required=true,description="ID of the page type")
   */
  public $id = null;

  /**
   * @var string
   * @SWG\Property(required=true,description="ID of the associated website")
   */
  public $websiteId = null;

  /**
   * @var \stdClass
   * @SWG\Property(required=true,description="the name")
   */
  public $name = null;

  /**
   * @var \stdClass
   * @SWG\Property(required=true,description="the description")
   */
  public $description = null;

  /**
   * @var string
   * @SWG\Property(required=true,description="the version")
   */
  public $version = null;

  /**
   * @var mixed
   * @SWG\Property(required=true,description="the form of the page type")
   */
  public $form = null;

  /**
   * @var mixed
   * @SWG\Property(required=true,description="the form data of page type")
   */
  public $formValues = null;

  /**
   * @var string
   * @SWG\Property(required=true,description="the url of the preview image")
   */
  public $previewImageUrl = null;

  /**
   * @param PageTypeData $data
   */
  public function __construct($data)
  {
    $this->formValues = new \stdClass();
    if ($data instanceof PageTypeData) {
      $this->setValuesFromData($data);
    }
  }

  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  /**
   * @param \stdClass $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @param \stdClass $description
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }

  /**
   * @param string $version
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }

  /**
   * @param mixed $form
   */
  public function setFrom($form)
  {
    $this->form = $form;
  }

  /**
   * @param mixed $formValues
   */
  public function setFromData($formValues)
  {
    if (is_object($formValues)) {
      $this->formValues = $formValues;
    }
  }

  /**
   * @param string $previewImageUrl
   */
  public function setPreviewImageUrl($previewImageUrl)
  {
    $this->previewImageUrl = $previewImageUrl;
  }

  protected function setValuesFromData(PageTypeData $data)
  {
    $this->setId($data->getId());
    $this->setWebsiteId($data->getWebsiteId());
    $this->setName($data->getName());
    $this->setDescription($data->getDescription());
    $this->setVersion($data->getVersion());
    $this->setFrom($data->getForm());
    $this->setFromData($data->getFormValues());
    $this->setPreviewImageUrl($data->getPreviewImageUrl());
  }
}
