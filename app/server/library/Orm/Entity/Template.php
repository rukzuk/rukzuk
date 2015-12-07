<?php

namespace Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Seitenbau\UniqueIdGenerator;
use Orm\Data\Template as OrmDataTemplate;

/**
 * Orm\Entity\Template
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
   * @var text $content
   */
  private $content = '';

  /**
   * @var string $pagetype
   */
  private $pagetype;

  /**
   * @var text $usedmoduleids
   */
  private $usedmoduleids = '';

  /**
   * @var timestamp
   */
  private $lastupdate;

  /**
   * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
   */
  public static function loadMetadata(ORM\ClassMetadataInfo $metadata)
  {
    $metadata->setTableName('template');
    $metadata->setIdGeneratorType(ORM\ClassMetadataInfo::GENERATOR_TYPE_NONE);
    $metadata->setCustomRepositoryClass('Orm\Repository\TemplateRepository');
    $metadata->addLifecycleCallback('setContentChecksumOnUpdate', 'prePersist');
    $metadata->addLifecycleCallback('setContentChecksumOnUpdate', 'preUpdate');
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
      'fieldName' => 'name',
      'type' => 'string',
      'length' => 255,
    ));
    $metadata->mapField(array(
      'fieldName' => 'contentchecksum',
      'type' => 'string',
      'length' => 100,
    ));
    $metadata->mapField(array(
      'fieldName' => 'content',
      'type' => 'text',
    ));
    $metadata->mapField(array(
      'fieldName' => 'pagetype',
      'type' => 'string',
      'length' => 255,
      'nullable' => true,
    ));
    $metadata->mapField(array(
      'fieldName' => 'usedmoduleids',
      'type' => 'text',
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
   * Set contentchecksum
   *
   * @param string $contentchecksum
   */
  public function setContentchecksum($contentchecksum)
  {
    $this->contentchecksum = $contentchecksum;
  }

  /**
   * Get contentchecksum
   *
   * @return string
   */
  public function getContentchecksum()
  {
    return $this->contentchecksum;
  }

  /**
   * Set content
   *
   * @param text $content
   */
  public function setContent($content)
  {
    $this->content = $content;
    $this->updateUsedModuleIds();
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
   * @PreUpdate
   */
  public function setContentChecksumOnUpdate()
  {
    $contentString = (is_array($this->content))
                   ? \Zend_Json::encode($this->content)
                   : $this->content;

    $this->contentchecksum = md5($contentString);
  }

  /**
   * Set page type
   *
   * @param string $pageType
   */
  public function setPagetype($pageType)
  {
    $this->pagetype = $pageType;
  }

  /**
   * Get page type
   *
   * @return string
   */
  public function getPagetype()
  {
    return $this->pagetype;
  }

  /**
   * Get used module ids
   *
   * @return array
   */
  public function getUsedmoduleids()
  {
    if (empty($this->usedmoduleids)) {
      return array();
    }
    return \Zend_Json::decode($this->usedmoduleids, \Zend_Json::TYPE_ARRAY);
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
    $this->id = OrmDataTemplate::ID_PREFIX .
                UniqueIdGenerator::v4() .
                OrmDataTemplate::ID_SUFFIX;
  }

  public function updateUsedModuleIds()
  {
    $usedModuleIds = array();
    $this->getUsedModuleIdsFromContent(
        $this->convertContentStringToArray($this->getContent()),
        $usedModuleIds
    );
    $this->usedmoduleids = \Zend_Json::encode(array_keys($usedModuleIds));
  }
  
  protected function convertContentStringToArray($contentString)
  {
    if (empty($contentString)) {
      return array();
    }
    return \Zend_Json::decode($contentString, \Zend_Json::TYPE_ARRAY);
  }
  
  protected function getUsedModuleIdsFromContent($content, &$usedModuleIds)
  {
    if (!is_array($content)) {
      return;
    }
    foreach ($content as $unit) {
      if (is_array($unit) && isset($unit['moduleId'])) {
        $usedModuleIds[$unit['moduleId']] = true;
      }
      if (is_array($unit) && isset($unit['children'])) {
        $this->getUsedModuleIdsFromContent($unit['children'], $usedModuleIds);
      }
    }
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
    );
  }

  /**
   * Liefert ein CMS Datenobjekt zurueck mit den Werten des ORM Objektes
   *
   * @return  \Cms\Data\Template
   */
  public function toCmsData()
  {
    $dataObject = new \Cms\Data\Template();
    $dataObject->setContent($this->getContent());
    $dataObject->setContentchecksum($this->getContentchecksum());
    $dataObject->setId($this->getId());
    $dataObject->setName($this->getName());
    $dataObject->setWebsiteid($this->getWebsiteid());
    $dataObject->setLastUpdate($this->getLastupdate());
    $dataObject->setPageType($this->getPagetype());
    return $dataObject;
  }
}
