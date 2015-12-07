<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Seitenbau\UniqueIdGenerator;
use Orm\Data\Page as DataPage;

/**
 * Orm\Entity\Page
 */
class Page
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
   * @var string $templateid
   */
  private $templateid = '';

  /**
   * @var string $mediaid
   */
  private $mediaid = '';

  /**
   * @var string $name
   */
  private $name = '';

  /**
   * @var text $description
   */
  private $description = '';

  /**
   * @var bigint $date
   */
  private $date = '';

  /**
   * @var smallint $innavigation
   */
  private $innavigation = '';

  /**
   * @var string $navigationtitle
   */
  private $navigationtitle = '';

  /**
   * @var text $content
   */
  private $content = '';

  /**
   * @var text $templatecontent
   */
  private $templatecontent = '';

  /**
   * @var string $templatecontentchecksum
   */
  private $templatecontentchecksum = '';

  /**
   * @var string $globalcontent
   */
  private $globalcontent = '';

  /**
   * @var string $pagetype
   */
  private $pagetype;

  /**
   * @var string $pageattributes
   */
  private $pageattributes;

  /**
   * @var timestamp
   */
  private $lastupdate;

  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('page');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\PageRepository');
    $metadata->addLifecycleCallback('setTemplatecontentChecksumOnUpdate', 'prePersist');
    $metadata->addLifecycleCallback('setTemplatecontentChecksumOnUpdate', 'preUpdate');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'prePersist');
    $metadata->addLifecycleCallback('setLastupdateToNow', 'preUpdate');

    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'id',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'id' => true,
      'fieldName' => 'websiteid',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'templateid',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'mediaid',
      'type' => 'string',
      'length' => 255,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'name',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'description',
      'type' => 'text',
    ));
    $metadata->mapField(array(
      'fieldName' => 'date',
      'type' => 'bigint',
      'length' => 20,
    ));
    $metadata->mapField(array(
      'fieldName' => 'innavigation',
      'type' => 'smallint',
      'length' => 1,
    ));
    $metadata->mapField(array(
      'fieldName' => 'navigationtitle',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'content',
      'type' => 'text',
    ));
    $metadata->mapField(array(
      'fieldName' => 'templatecontent',
      'type' => 'text',
    ));
    $metadata->mapField(array(
      'fieldName' => 'templatecontentchecksum',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'globalcontent',
      'type' => 'text',
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'pagetype',
      'type' => 'string',
      'length' => 255,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'pageattributes',
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
   * Set templateid
   *
   * @param string $templateid
   */
  public function setTemplateid($templateid)
  {
    $this->templateid = $templateid;
  }

  /**
   * Get templateid
   *
   * @return string
   */
  public function getTemplateid()
  {
    return $this->templateid;
  }

  /**
   * Get the mediaId db id (e.g. MDB-..............)
   *
   * @return string
   */
  public function getMediaid()
  {
    return $this->mediaid;
  }

  /**
   * Set the mediaId db id (e.g. MDB-..............)
   *
   * @param string $mediaId
   */
  public function setMediaid($mediaId)
  {
    $this->mediaid = $mediaId;
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
   * Set date
   *
   * @param bigint $date
   */
  public function setDate($date)
  {
    $this->date = $date;
  }

  /**
   * Get date
   *
   * @return bigint
   */
  public function getDate()
  {
    return $this->date;
  }

  /**
   * Set innavigation
   *
   * @param smallint $innavigation
   */
  public function setInnavigation($innavigation)
  {
    $this->innavigation = $innavigation;
  }

  /**
   * Get innavigation
   *
   * @return smallint
   */
  public function getInnavigation()
  {
    return $this->innavigation;
  }

  /**
   * Set navigationtitle
   *
   * @param string $navigationtitle
   */
  public function setNavigationtitle($navigationtitle)
  {
    $this->navigationtitle = $navigationtitle;
  }

  /**
   * Get navigationtitle
   *
   * @return string
   */
  public function getNavigationtitle()
  {
    return $this->navigationtitle;
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
   * Set templatecontent
   *
   * @param text $templatecontent
   */
  public function setTemplatecontent($templatecontent)
  {
    $this->templatecontent = $templatecontent;
  }

  /**
   * Get templatecontent
   *
   * @return text
   */
  public function getTemplatecontent()
  {
    return $this->templatecontent;
  }

  /**
   * Set templatecontentchecksum
   *
   * @param string $templatecontentchecksum
   */
  public function setTemplatecontentchecksum($templatecontentchecksum)
  {
    $this->templatecontentchecksum = $templatecontentchecksum;
  }

  /**
   * Get templatecontentchecksum
   *
   * @return string
   */
  public function getTemplatecontentchecksum()
  {
    return $this->templatecontentchecksum;
  }

  /**
   * @preUpdate
   */
  public function setTemplatecontentChecksumOnUpdate()
  {
    $this->templatecontentchecksum = md5($this->templatecontent);
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
   * Set global content
   *
   * @param text $globalcontent
   */
  public function setGlobalContent($globalcontent)
  {
    $this->globalcontent = $globalcontent;
  }

  /**
   * Get global content
   *
   * @return text $globalcontent
   */
  public function getGlobalContent()
  {
    return $this->globalcontent;
  }

  /**
   * @param string $pagetype
   */
  public function setPagetype($pagetype)
  {
    $this->pagetype = $pagetype;
  }

  /**
   * @return string
   */
  public function getPagetype()
  {
    return $this->pagetype;
  }

  /**
   * @param string $pageattributes
   */
  public function setPageattributes($pageattributes)
  {
    $this->pageattributes = $pageattributes;
  }

  /**
   * @return string
   */
  public function getPageattributes()
  {
    return $this->pageattributes;
  }

  /**
   * setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataPage::ID_PREFIX .
      UniqueIdGenerator::v4() .
      DataPage::ID_SUFFIX;
  }

  /**
   * Liefert alle Columns und deren Values
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'id' => $this->getId(),
      'websiteid' => $this->getWebsiteid(),
      'templateId' => $this->getTemplateid(),
      'mediaid' => $this->getMediaid(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'date' => $this->getDate(),
      'inNavigation' => $this->getInnavigation(),
      'navigationTitle' => $this->getNavigationtitle(),
      'content' => $this->getContent(),
      'templateContent' => $this->getTemplatecontent(),
      'templatecontentchecksum' => $this->getTemplatecontentchecksum(),
      'globalcontent' => $this->getGlobalContent(),
    );
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
      'templateId' => $this->getTemplateid(),
      'mediaid' => $this->getMediaid(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'inNavigation' => $this->getInnavigation(),
      'date' => $this->getDate(),
      'navigationTitle' => $this->getNavigationtitle(),
      'content' => $this->getContent(),
      'templateContent' => $this->getTemplatecontent(),
      'lastUpdate' => $this->getLastupdate()
    );
  }

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\Page
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\Page();
    $dataObject->setContent($this->getContent())
      ->setDate($this->getDate())
      ->setDescription($this->getDescription())
      ->setId($this->getId())
      ->setInnavigation($this->getInnavigation())
      ->setName($this->getName())
      ->setNavigationtitle($this->getNavigationtitle())
      ->setTemplatecontent($this->getTemplatecontent())
      ->setTemplatecontentchecksum($this->getTemplatecontentchecksum())
      ->setGlobalContent($this->getGlobalContent())
      ->setTemplateid($this->getTemplateid())
      ->setMediaId($this->getMediaid())
      ->setWebsiteid($this->getWebsiteid())
      ->setPageType($this->getPagetype())
      ->setPageAttributes($this->getPageattributes())
      ->setLastUpdate($this->getLastupdate());
    return $dataObject;
  }
}
