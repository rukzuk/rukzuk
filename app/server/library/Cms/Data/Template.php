<?php
namespace Cms\Data;

use Seitenbau\UniqueIdGenerator;
use Orm\Data\Template as DataTemplate;

/**
 * Template Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
 */

class Template
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
   * @var string $contentchecksum
   */
  private $contentchecksum = '';

  /**
   * @var string $content
   */
  private $content;

  /**
   * @var string $pageType
   */
  private $pageType;

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
   * @param string $id
   */
  public function setId($id)
  {
    return $this->id = $id;
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
   * set websiteid
   *
   * @param string $websiteid
   */
  public function setWebsiteid($websiteid)
  {
    return $this->websiteid = $websiteid;
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
   * @return string $name
   */
  public function getName()
  {
    return $this->name;
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
   * get contentchecksum
   *
   * @return string
   */
  public function getContentchecksum()
  {
    return $this->contentchecksum;
  }

  /**
   * set contentchecksum
   *
   * @param string $contentchecksum
   */
  public function setContentchecksum($contentchecksum)
  {
    $this->contentchecksum = $contentchecksum;
  }

  /**
   * Get content
   *
   * @return text $content
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * Set page type
   *
   * @param string $pageType
   */
  public function setPageType($pageType)
  {
    $this->pageType = $pageType;
  }

  /**
   * Get page type
   *
   * @return string
   */
  public function getPageType()
  {
    return $this->pageType;
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
   */
  public function setLastUpdate($lastUpdate)
  {
    $this->lastUpdate = $lastUpdate;
    return $this;
  }

  /**
   * @PreUpdate
   */
  public function setContentChecksumOnUpdate()
  {
    $contentString = (is_array($this->content))
                   ? \Seitenbau\Json::encode($this->content)
                   : $this->content;
    
    $this->contentchecksum = $contentString;
  }

  /**
   * Setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataTemplate::ID_PREFIX .
                UniqueIdGenerator::v4() .
                DataTemplate::ID_SUFFIX;
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
      'name' => $this->getName(),
      'contentchecksum' => $this->getContentchecksum(),
      'content' => $this->getContent(),
      'pageType' => $this->getPageType(),
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
      'name' => $this->getName(),
      'content' => $this->getContent(),
      'pageType' => $this->getPageType(),
    );
  }
}
