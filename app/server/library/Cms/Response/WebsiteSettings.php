<?php


namespace Cms\Response;

use \Cms\Data\WebsiteSettings as WebsiteSettingsData;

/**
 * @package      Cms\Response
 *
 * @SWG\Model(
 *      id="WebsiteSettings",
 *      required="all")
 */
class WebsiteSettings implements IsResponseData
{
  /**
   * @var string
   * @SWG\Property(required=true,description="ID of the website settings")
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
   * @SWG\Property(required=true,description="the form of website settings")
   */
  public $form = null;

  /**
   * @var mixed
   * @SWG\Property(required=true,description="the form data of website settings")
   */
  public $formValues = null;

  /**
   * @param WebsiteSettingsData $data
   */
  public function __construct($data)
  {
    $this->formValues = new \stdClass();
    if ($data instanceof WebsiteSettingsData) {
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

  protected function setValuesFromData(WebsiteSettingsData $data)
  {
    $this->setId($data->getId());
    $this->setWebsiteId($data->getWebsiteId());
    $this->setName($data->getName());
    $this->setDescription($data->getDescription());
    $this->setVersion($data->getVersion());
    $this->setFrom($data->getForm());
    $this->setFromData($data->getFormValues());
  }
}
