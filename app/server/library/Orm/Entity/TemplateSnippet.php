<?php
namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Seitenbau\UniqueIdGenerator;
use Orm\Data\TemplateSnippet as DataTemplateSnippet;

/**
 * Orm\Entity\TemplateSnippet
 */
class TemplateSnippet
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
   * @var string $name
   */
  private $name;

  /**
   * @var text $description
   */
  private $description = '';

  /**
   * @var text $category
   */
  private $category = '';

  /**
   * @var text $content
   */
  private $content = '';

  /**
   * @var timestamp
   */
  private $lastupdate;

  /**
   * @param Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('template_snippet');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\TemplateSnippetRepository');
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
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'name',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'description',
      'type' => 'text',
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'category',
      'type' => 'text',
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'content',
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
   * Set websiteid
   *
   * @param string $websiteid
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
  }

  /**
   * Get websiteid
   *
   * @return string
   */
  public function getWebsiteid()
  {
    return $this->websiteid;
  }

  /**
   * Set id
   *
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * Get id
   *
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set name
   *
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set description
   *
   * @param text $description
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }

  /**
   * Get description
   *
   * @return text
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set category
   *
   * @param text $category
   */
  public function setCategory($category)
  {
    $this->category = $category;
  }

  /**
   * Get category
   *
   * @return text
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * Set content
   *
   * @param text $content
   */
  public function setContent($content)
  {
    $this->content = $content;
  }

  /**
   * Get content
   *
   * @return text
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * Get lastupdate
   *
   * @return int
   */
  public function getLastupdate()
  {
    return $this->lastupdate;
  }

  /**
   * Set lastupdate
   *
   * @param int $lastupdate
   */
  public function setLastupdate($lastupdate)
  {
    $this->lastupdate = $lastupdate;
  }

  /**
   * Setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataTemplateSnippet::ID_PREFIX .
                UniqueIdGenerator::v4() .
                DataTemplateSnippet::ID_SUFFIX;
  }

  /**
   * Liefert die Columns und deren Values welche bei einem Export
   * beruecksichtigt weerden zurueck.
   *
   * @return array
   */
  public function getExportColumnsAndValues()
  {
    return array(
      'id' => $this->getId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'category' => $this->getCategory(),
      'content' => $this->getContent(),
    );
  }

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\TemplateSnippet
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\TemplateSnippet();
    $dataObject->setWebsiteid($this->getWebsiteid())
               ->setId($this->getId())
               ->setName($this->getName())
               ->setDescription($this->getDescription())
               ->setCategory($this->getCategory())
               ->setContent($this->getContent())
               ->setLastUpdate($this->getLastupdate());
    return $dataObject;
  }
}
