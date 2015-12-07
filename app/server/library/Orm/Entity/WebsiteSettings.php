<?php
namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Cms\Data\WebsiteSettings as DataWebsiteSettings;

/**
 * Orm\Entity\WebsiteSettings
 */
class WebsiteSettings
{
  /**
   * @var string $id
   */
  private $id;

  /**
   * @var string $websiteid
   */
  private $websiteid;

  /**
   * @var string $formValues
   */
  private $formValues = '';

  /**
   * @var timestamp
   */
  private $lastupdate;

  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('website_settings');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\WebsiteSettingsRepository');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'prePersist');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'preUpdate');

    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'websiteid',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'id',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'formValues',
      'type' => 'text',
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'lastupdate',
      'type' => 'bigint',
      'default' => 0,
    ));
  }

  /**
   * set lastupdate to now
   */
  public function setLastupdateToNow()
  {
    $this->lastupdate = time();
  }

  /**
   * @param string $websiteid
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
  }

  /**
   * @return string
   */
  public function getWebsiteid()
  {
    return $this->websiteid;
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
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param string $formValues
   */
  public function setFormValues($formValues)
  {
    $this->formValues = $formValues;
  }

  /**
   * @return string
   */
  public function getFormValues()
  {
    return $this->formValues;
  }

  /**
   * @return int
   */
  public function getLastupdate()
  {
    return $this->lastupdate;
  }

  /**
   * @param int $lastupdate
   */
  public function setLastupdate($lastupdate)
  {
    $this->lastupdate = $lastupdate;
  }

  /**
   * @return  DataWebsiteSettings
   */
  public function toCmsData()
  {
    $dataObject = new DataWebsiteSettings();
    $dataObject->setWebsiteid($this->getWebsiteid());
    $dataObject->setId($this->getId());
    $dataObject->setFormValues(json_decode($this->getFormValues()));
    $dataObject->setSourceType($dataObject::SOURCE_DATA);
    return $dataObject;
  }
}
