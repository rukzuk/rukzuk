<?php


namespace Cms\Dao\WebsiteSettings;

use Cms\Dao\WebsiteSettings as WebsiteSettingsDaoInterface;
use Cms\Dao\Doctrine as DoctrineBase;
use Cms\Dao\WebsiteSettings\Source as WebsiteSettingsSource;
use Cms\Data\WebsiteSettings as DataWebsiteSettings;
use Cms\Exception as CmsException;
use Orm\Entity\WebsiteSettings as OrmWebsiteSettings;

/**
 * Doctrine dao for websiteSettings
 *
 * @package Cms\Dao\WebsiteSettings
 */
class Doctrine extends DoctrineBase implements WebsiteSettingsDaoInterface
{
  /**
   * returns all Packages of the given source
   *
   * @param WebsiteSettingsSource $source
   *
   * @return \Cms\Data\WebsiteSettings[]
   * @throws CmsException
   */
  public function getAll(WebsiteSettingsSource $source)
  {
    $websiteId = $source->getWebsiteId();
    try {
      /** @var \Orm\Entity\WebsiteSettings[] $ormList */
      $ormList = $this->getEntityManager()
        ->getRepository('Orm\Entity\WebsiteSettings')
        ->findBy(array('websiteid' => $websiteId));
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(2501, __METHOD__, __LINE__, null, $e);
    }

    return $this->convertOrm($ormList);
  }

  /**
   * returns the WebsiteSettings of the given source and id
   *
   * @param WebsiteSettingsSource $source
   * @param string                $id
   *
   * @return \Cms\Data\WebsiteSettings
   * @throws \Cms\Exception
   */
  public function getById(WebsiteSettingsSource $source, $id)
  {
    $orm = $this->getOrmById($source, $id);
    $this->clearEntityManager();
    return $this->convertOrm($orm);
  }

  /**
   * Checks if there is are WebsiteSettings under the given WebsiteSettings-Id and Website-Id
   *
   * @param WebsiteSettingsSource $source
   * @param string                $id
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function exists(WebsiteSettingsSource $source, $id)
  {
    $orm = $this->getOrmById($source, $id, false);
    $this->clearEntityManager();
    return $orm !== null;
  }

  /**
   * creates a new WebsiteSettings
   *
   * @param WebsiteSettingsSource $source
   * @param DataWebsiteSettings   $websiteSettings
   *
   * @return DataWebsiteSettings
   * @throws \Cms\Exception
   */
  public function create(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings)
  {
    $settingsId = $websiteSettings->getId();
    if (empty($settingsId)) {
      throw new CmsException(2513, __METHOD__, __LINE__);
    }

    $websiteId = $source->getWebsiteId();
    if (empty($websiteId)) {
      throw new CmsException(2512, __METHOD__, __LINE__);
    }

    if ($this->exists($source, $settingsId)) {
      throw new CmsException(2514, __METHOD__, __LINE__);
    }

    try {
      $orm = new OrmWebsiteSettings();
      $orm->setWebsiteid($websiteId);
      $orm->setId($settingsId);

      $this->setAttributesToOrm($orm, $websiteSettings);

      $entityManager = $this->getEntityManager();
      $entityManager->persist($orm);
      $entityManager->flush();
      $entityManager->refresh($orm);
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(2511, __METHOD__, __LINE__, null, $e);
    }
    return $this->convertOrm($orm);
  }

  /**
   * updates a new TemplateSnippet
   *
   * @param WebsiteSettingsSource $source
   * @param DataWebsiteSettings   $websiteSettings
   *
   * @return DataWebsiteSettings
   * @throws \Cms\Exception
   */
  public function update(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings)
  {
    $orm = $this->getOrmById($source, $websiteSettings->getId());
    try {
      $this->setAttributesToOrm($orm, $websiteSettings);

      $entityManager = $this->getEntityManager();
      $entityManager->persist($orm);
      $entityManager->flush();
      $entityManager->refresh($orm);
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(2511, __METHOD__, __LINE__, null, $e);
    }
    return $this->convertOrm($orm);
  }

  /**
   * @param WebsiteSettingsSource $fromSource
   * @param WebsiteSettingsSource $toSource
   *
   * @return bool
   * @throws CmsException
   */
  public function copyToNewWebsite(WebsiteSettingsSource $fromSource, WebsiteSettingsSource $toSource)
  {
    $fromWebsiteId = $fromSource->getWebsiteId();
    $toWebsiteId = $toSource->getWebsiteId();
    try {
      $entityManager = $this->getEntityManager();

      /** @var \Orm\Entity\WebsiteSettings[] $allWebsiteSettings */
      $ormList = $entityManager->getRepository('Orm\Entity\WebsiteSettings')
        ->findBy(array('websiteid' => $fromWebsiteId));

      foreach ($ormList as $websiteSettingsOrm) {
        $newWebsiteSettingsOrm = clone $websiteSettingsOrm;
        $newWebsiteSettingsOrm->setWebsiteid($toWebsiteId);
        $entityManager->persist($newWebsiteSettingsOrm);
      }

      $entityManager->flush();
      $this->clearEntityManager();

      return true;
    } catch (\Exception $e) {
      throw new CmsException(2515, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * deletes all website settings of the given website id
   *
   * @param WebsiteSettingsSource $source
   *
   * @throws CmsException
   */
  public function deleteByWebsiteId(WebsiteSettingsSource $source)
  {
    $websiteId = $source->getWebsiteId();
    $entityManager = $this->getEntityManager();
    try {
      $result = $entityManager->getRepository('Orm\Entity\WebsiteSettings')
        ->deleteByWebsiteId($websiteId);
      $this->clearEntityManager();
      return $result;
    } catch (\Exception $e) {
      throw new CmsException(2509, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * return the WebsiteSettings orm of the given id and website id
   *
   * @param WebsiteSettingsSource $source
   * @param string                $id
   * @param bool                  $throwExceptionIfNotExists
   *
   * @return OrmWebsiteSettings
   * @throws CmsException
   */
  protected function getOrmById($source, $id, $throwExceptionIfNotExists = true)
  {
    try {
      $ormEntity = $this->getEntityManager()
        ->getRepository('Orm\Entity\WebsiteSettings')
        ->findOneBy(array(
          'websiteid' => $source->getWebsiteId(),
          'id' => $id,
        ));
    } catch (\Exception $e) {
      throw new CmsException(2503, __METHOD__, __LINE__, null, $e);
    }

    if ($throwExceptionIfNotExists && $ormEntity === null) {
      throw new CmsException(2502, __METHOD__, __LINE__);
    }

    return $ormEntity;
  }

  /**
   * set the WebsiteSettings attributes to orm
   *
   * @param \Orm\Entity\WebsiteSettings $orm
   * @param \Cms\Data\WebsiteSettings   $websiteSettings
   */
  protected function setAttributesToOrm(
      OrmWebsiteSettings $orm,
      DataWebsiteSettings $websiteSettings
  ) {
    $orm->setFormValues(json_encode($websiteSettings->getFormValues()));
  }

  /**
   * @param \Orm\Entity\WebsiteSettings|\Orm\Entity\WebsiteSettings[] $data
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\WebsiteSettings|\Cms\Data\WebsiteSettings[]
   */
  protected function convertOrm($data)
  {
    if (is_array($data)) {
      return $this->convertOrmListToDataObjectList($data);
    }
    if ($data instanceof OrmWebsiteSettings) {
      return $this->convertOrmToDataObject($data);
    }
    throw new CmsException(2, __METHOD__, __LINE__, array(
      'message' => 'Error at converting website settings result'));
  }

  /**
   * @param \Orm\Entity\WebsiteSettings[] $ormList
   *
   * @return \Cms\Data\WebsiteSettings[]
   */
  protected function convertOrmListToDataObjectList(array $ormList)
  {
    $snippets = array();
    foreach ($ormList as $orm) {
      if ($orm instanceof OrmWebsiteSettings) {
        $snippets[$orm->getId()] = $this->convertOrmToDataObject($orm);
      }
    }
    return $snippets;
  }

  /**
   * @param \Orm\Entity\WebsiteSettings $orm
   *
   * @return \Cms\Data\WebsiteSettings
   */
  protected function convertOrmToDataObject(OrmWebsiteSettings $orm)
  {
    /** @var $snippet \Cms\Data\WebsiteSettings */
    return $orm->toCmsData();
  }
}
