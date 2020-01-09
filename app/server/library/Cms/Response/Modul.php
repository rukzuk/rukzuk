<?php
namespace Cms\Response;

use \Cms\Data;

/**
 * Einzelnes Modul fuer Response-Darstellung
 *
 * @package      Cms
 * @subpackage   Response
 *
 * @SWG\Model(
 *      id="Module",
 *      required="all")
 */
class Modul implements IsResponseData
{
  /**
   * @var string
   * @SWG\Property(required=true,description="id of the module")
   */
  public $id = null;

  /**
   * @var string
   * @SWG\Property(required=true,description="ID of the associated website")
   */
  public $websiteId = null;

  /**
   * @var string
   * @SWG\Property(required=true,description="name of the module")
   */
  public $name = null;

  /**
   * @var string
   * @SWG\Property(required=true,description="description of the module")
   */
  public $description = null;

  /**
   * @var string $version
   * @SWG\Property(required=true,description="version of the module")
   */
  public $version = null;

  /**
   * @var string $icon
   * @SWG\Property(required=true,description="the icon of the module")
   */
  public $icon;

  /**
   * @var string $form
   * @SWG\Property(required=true,description="gui of the module as json string")
   */
  public $form;

  /**
   * @var string $formvalues
   * @SWG\Property(required=true,description="form values of the module as json string")
   */
  public $formValues;

  /**
   * @var string $category
   * @SWG\Property(required=true,description="category of the module")
   */
  public $category;

  /**
   * @var string $moduleType
   * @SWG\Property(required=true,description="type of the module",
   *      enum="['default', 'root', 'extension']")
   */
  public $moduleType;

  /**
   * @var string $allowedChildModuleType
   * @SWG\Property(required=true,description="only children of these types are allowed. '*' = all types allowed",
   *      enum="['*', 'default', 'root', 'extension']")
   */
  public $allowedChildModuleType;
  
  /**
   * @var boolean $reRenderRequired
   * @SWG\Property(required=true,description="page/template should be new rendered if form values changed")
   */
  public $reRenderRequired;

  /**
   * @var string $ghostContainerMode
   * @SWG\Property(required=true,description="the units created out of this module can be either
   * never, always or user-configured be a ghostContainer aka flex-container")
   */
  public $ghostContainerMode;

  /**
   * @var string
   * @SWG\Property(required=true,description="source type of the module",
   *      enum="['local', 'repo']")
   */
  public $sourceType;

  /**
   * @var boolean
   * @SWG\Property(required=true,description="module has overwritten a global module with same id")
   */
  public $overwritten = false;
  
  /**
   * @param \Orm\Modul $data
   */
  public function __construct(Data\Modul $data = null)
  {
    if ($data !== null) {
      $this->setValuesFromData($data);
    }
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getWebsiteid()
  {
    return $this->websiteId;
  }

  /**
   * @param string $websiteId
   */
  public function setWebsiteid($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @param string $description
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }

  /**
   * @return string
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * @param string $version
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }

  /**
   * @return string
   */
  public function getIcon()
  {
    return $this->icon;
  }

  /**
   * @param string $icon
   */
  public function setIcon($icon)
  {
    $this->icon = $icon;
  }

  /**
   * @return string
   */
  public function getForm()
  {
    return $this->form;
  }

  /**
   * @param string $form
   */
  public function setForm($form)
  {
    if (is_string($form)) {
      $form = \Seitenbau\Json::decode($form);
    }
    $this->form = $form;
  }

  /**
   * @return string
   */
  public function getFormvalues()
  {
    return $this->formValues;
  }

  /**
   * @param string $values
   */
  public function setFormValues($values)
  {
    if (is_string($values)) {
      $values = \Seitenbau\Json::decode($values, \Zend_Json::TYPE_OBJECT);
    }
    $this->formValues = $values;
  }

  /**
   * @return string
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * @param string $category
   */
  public function setCategory($category)
  {
    $this->category = $category;
  }

  /**
   * @return string
   */
  public function getModuletype()
  {
    return $this->moduleType;
  }

  /**
   * @param string $moduleType
   */
  public function setModuletype($moduleType)
  {
    $this->moduleType = $moduleType;
  }

  /**
   * @return string
   */
  public function getAllowedchildmoduletype()
  {
    return $this->allowedChildModuleType;
  }

  /**
   * @param string $allowedChildModuleType
   */
  public function setAllowedchildmoduletype($allowedChildModuleType)
  {
    $this->allowedChildModuleType = $allowedChildModuleType;
  }

  /**
   * @return string
   */
  public function getReRenderRequired()
  {
    return $this->reRenderRequired;
  }

  /**
   * @param boolean $reRenderRequired
   */
  public function setReRenderRequired($reRenderRequired)
  {
    $this->reRenderRequired = $reRenderRequired;
  }

  /**
   * @return string
   */
  public function getGhostContainerMode()
  {
    return $this->ghostContainerMode;
  }

  /**
   * @param string $ghostContainerMode
   */
  public function setGhostContainerMode($ghostContainerMode)
  {
    $this->ghostContainerMode = $ghostContainerMode;
  }

  /**
   * @return string
   */
  public function getSourceType()
  {
    return $this->sourceType;
  }

  /**
   * @param string $sourceType
   */
  public function setSourceType($sourceType)
  {
    $this->sourceType = $sourceType;
  }

  /**
   * @return bool
   */
  public function isOverwritten()
  {
    return $this->overwritten;
  }

  /**
   * @param bool $overwritten
   */
  public function setOverwritten($overwritten)
  {
    $this->overwritten = (bool)$overwritten;
  }

  /**
   * @param $data
   */
  protected function setValuesFromData(Data\Modul $data)
  {
    $this->setId($data->getId());
    $this->setWebsiteid($data->getWebsiteId());
    $this->setName($data->getName());
    $this->setDescription($data->getDescription());
    $this->setVersion($data->getVersion());
    $this->setIcon($data->getIcon());
    $this->setForm($data->getForm());
    $this->setFormvalues($data->getFormvalues());
    $this->setCategory($data->getCategory());
    $this->setModuletype($data->getModuletype());
    $this->setAllowedchildmoduletype($data->getAllowedchildmoduletype());
    $this->setReRenderRequired($data->getRerenderrequired());
    $this->setSourceType($data->getSourceType());
    $this->setOverwritten($data->isOverwritten());
    $this->setGhostContainerMode($data->getGhostContainerMode());
  }
}
