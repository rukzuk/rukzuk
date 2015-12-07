<?php
namespace Cms\Data;

use Seitenbau\UniqueIdGenerator;
use Orm\Data\Page as DataPage;

/**
 * Page Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
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
   * @var string $media
   */
  private $mediaId = '';

  /**
   * @var string $name
   */
  private $name = '';

  /**
   * @var string $description
   */
  private $description = '';

  /**
   * @var int $date
   */
  private $date = '';

  /**
   * @var int $innavigation
   */
  private $innavigation = '';

  /**
   * @var string $navigationtitle
   */
  private $navigationtitle = '';

  /**
   * @var string $content
   */
  private $content = '';

  /**
   * @var string $templatecontent
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
   * @var string $pageType
   */
  private $pageType;

  /**
   * @var string $pageAttributes
   */
  private $pageAttributes;

  /**
   * @var int $lastupdate
   */
  private $lastUpdate;

  /**
   * Get id
   *
   * @return string $id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set id
   *
   * @param string $id
   *
   * @return self
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * Set websiteid
   *
   * @param string $websiteid
   *
   * @return self
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
    return $this;
  }

  /**
   * Get websiteid
   *
   * @return string $websiteid
   */
  public function getWebsiteid()
  {
    return $this->websiteid;
  }

  /**
   * Set templateid
   *
   * @param string $templateid
   *
   * @return self
   */
  public function setTemplateid($templateid)
  {
    $this->templateid = $templateid;
    return $this;
  }

  /**
   * Get templateid
   *
   * @return string $templateid
   */
  public function getTemplateid()
  {
    return $this->templateid;
  }

  /**
   * @return string
   */
  public function getMediaId()
  {
    return $this->mediaId;
  }

  /**
   * @param string $mediaId
   *
   * @return Page
   */
  public function setMediaId($mediaId)
  {
    $this->mediaId = $mediaId;
    return $this;
  }

  /**
   * Set name
   *
   * @param string $name
   *
   * @return self
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Get name
   *
   * @return string $name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set description
   *
   * @param string $description
   *
   * @return self
   */
  public function setDescription($description)
  {
    $this->description = $description;
    return $this;
  }

  /**
   * Get description
   *
   * @return string $description
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set date
   *
   * @param int $date
   *
   * @return self
   */
  public function setDate($date)
  {
    $this->date = $date;
    return $this;
  }

  /**
   * Get date
   *
   * @return int $date
   */
  public function getDate()
  {
    return $this->date;
  }

  /**
   * Set innavigation
   *
   * @param int $innavigation
   *
   * @return self
   */
  public function setInnavigation($innavigation)
  {
    $this->innavigation = $innavigation;
    return $this;
  }

  /**
   * Get innavigation
   *
   * @return int $innavigation
   */
  public function getInnavigation()
  {
    return $this->innavigation;
  }

  /**
   * Set navigationtitle
   *
   * @param string $navigationtitle
   *
   * @return self
   */
  public function setNavigationtitle($navigationtitle)
  {
    $this->navigationtitle = $navigationtitle;
    return $this;
  }

  /**
   * Get navigationtitle
   *
   * @return string $navigationtitle
   */
  public function getNavigationtitle()
  {
    return $this->navigationtitle;
  }

  /**
   * Set content
   *
   * @param string $content
   *
   * @return self
   */
  public function setContent($content)
  {
    $this->content = $content;
    return $this;
  }

  /**
   * Get content
   *
   * @return string $content
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * Set templatecontent
   *
   * @param string $templatecontent
   *
   * @return self
   */
  public function setTemplatecontent($templatecontent)
  {
    $this->templatecontent = $templatecontent;
    return $this;
  }

  /**
   * Get templatecontent
   *
   * @return string $templatecontent
   */
  public function getTemplatecontent()
  {
    return $this->templatecontent;
  }

  /**
   * Set templatecontentchecksum
   *
   * @param string $templatecontentchecksum
   *
   * @return self
   */
  public function setTemplatecontentchecksum($templatecontentchecksum)
  {
    $this->templatecontentchecksum = $templatecontentchecksum;
    return $this;
  }

  /**
   * Get templatecontentchecksum
   *
   * @return string $templatecontentchecksum
   */
  public function getTemplatecontentchecksum()
  {
    return $this->templatecontentchecksum;
  }

  /**
   * Set global content
   *
   * @param string $globalcontent
   *
   * @return self
   */
  public function setGlobalContent($globalcontent)
  {
    $this->globalcontent = $globalcontent;
    return $this;
  }

  /**
   * Get global content
   *
   * @return string $content
   */
  public function getGlobalContent()
  {
    return $this->globalcontent;
  }

  /**
   * @param string $pageType
   *
   * @return self
   */
  public function setPageType($pageType)
  {
    $this->pageType = $pageType;
    return $this;
  }

  /**
   * @return string
   */
  public function getPageType()
  {
    return $this->pageType;
  }


  /**
   * @param string $pageAttributes
   *
   * @return self
   */
  public function setPageAttributes($pageAttributes)
  {
    $this->pageAttributes = $pageAttributes;
    return $this;
  }

  /**
   * @return string
   */
  public function getPageAttributes()
  {
    return $this->pageAttributes;
  }

  /**
   * Get lastUpdate
   *
   * @return int
   */
  public function getLastUpdate()
  {
    return $this->lastUpdate;
  }

  /**
   * Set lastUpdate
   *
   * @param int $lastUpdate
   *
   * @return $this
   */
  public function setLastUpdate($lastUpdate)
  {
    $this->lastUpdate = $lastUpdate;
    return $this;
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
      'mediaId' => $this->getMediaId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'date' => $this->getDate(),
      'inNavigation' => $this->getInnavigation(),
      'navigationTitle' => $this->getNavigationtitle(),
      'content' => $this->getContent(),
      'templateContent' => $this->getTemplatecontent(),
      'templatecontentchecksum' => $this->getTemplatecontentchecksum(),
      'globalcontent' => $this->getGlobalContent(),
      'pageType' => $this->getPageType(),
      'pageAttributes' => $this->getPageAttributes(),
      'lastupdate' => $this->getLastUpdate()
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
      'mediaId' => $this->getMediaId(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'inNavigation' => $this->getInnavigation(),
      'date' => $this->getDate(),
      'navigationTitle' => $this->getNavigationtitle(),
      'content' => $this->getContent(),
      'templateContent' => $this->getTemplatecontent(),
      'pageType' => $this->getPageType(),
      'pageAttributes' => $this->getPageAttributes(),
    );
  }
}
