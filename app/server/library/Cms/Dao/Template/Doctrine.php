<?php
namespace Cms\Dao\Template;

use Cms\Dao\Template as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Cms\Exception as CmsException;
use Exception;
use Orm\Entity\Template as OrmTemplate;

/**
 * Doctrine Dao fuer Template
 *
 * @package      Application
 * @subpackage   Controller
 */
class Doctrine extends DoctrineBase implements Dao
{
  /**
   * returns the page orm for the given website id and page id
   * this function DON'T clear the doctrine entity manager
   *
   * @param string $websiteId
   * @param string $id
   *
   * @return OrmTemplate
   * @throws \Cms\Exception
   */
  protected function getByIdInternal($websiteId, $id)
  {
    try {
      $template = $this->getEntityManager()
        ->getRepository('Orm\Entity\Template')
        ->findOneBy(array(
          'id' => $id,
          'websiteid' => $websiteId));
    } catch (Exception $e) {
      throw new CmsException(303, __METHOD__, __LINE__, null, $e);
    }
    return $template;
  }

  /**
   * @param string  $websiteId
   * @param array   $columnsValues
   * @param boolean $useColumnsValuesId Defaults to false
   *
   * @return OrmTemplate
   * @throws \Cms\Exception
   */
  public function create($websiteId, array $columnsValues, $useColumnsValuesId = false)
  {
    try {
      $template = new OrmTemplate();

      $this->setAttributesToOrm($template, $columnsValues);
      $template->setWebsiteid($websiteId);

      if ($useColumnsValuesId && isset($columnsValues['id'])) {
        $template->setId($columnsValues['id']);
      } else {
        $template->setNewGeneratedId();
      }

      $entityManager = $this->getEntityManager();
      $entityManager->persist($template);
      $entityManager->flush();
      $entityManager->refresh($template);
      $this->clearEntityManager();
    } catch (Exception $e) {
      throw new CmsException(306, __METHOD__, __LINE__, null, $e);
    }
    return $template;
  }

  /**
   * @param string $id
   * @param string $websiteId
   * @param array  $columnsValues
   *
   * @return OrmTemplate
   * @throws CmsException
   */
  public function update($id, $websiteId, array $columnsValues)
  {
    $template = $this->getByIdInternal($websiteId, $id);
    if ($template === null) {
      throw new CmsException(305, __METHOD__, __LINE__);
    }

    $this->setAttributesToOrm($template, $columnsValues);

    try {
      $entityManager = $this->getEntityManager();
      $entityManager->persist($template);
      $entityManager->flush();
      $entityManager->refresh($template);
      $this->clearEntityManager();
    } catch (Exception $e) {
      throw new CmsException(306, __METHOD__, __LINE__, null, $e);
    }

    return $template;
  }

  /**
   * @return OrmTemplate[]
   * @throws \Cms\Exception
   */
  public function getAll($websiteId)
  {
    try {
      $templates = $this->getEntityManager()
        ->getRepository('Orm\Entity\Template')
        ->findByWebsiteIdOrderedByName($websiteId);
    } catch (Exception $e) {
      throw new CmsException(301, __METHOD__, __LINE__, null, $e);
    }
    $this->clearEntityManager();
    return $templates;
  }

  /**
   * Array with reduced data of the template
   *
   * @param string $websiteId
   *
   * @return array[string] array with id as key and array as value
   *
   */
  public function getInfoByWebsiteId($websiteId)
  {
    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->add('select', 't.id, t.name')
      ->add('from', 'Orm\Entity\Template t')
      ->add('where', 't.websiteid = :websiteid')
      ->setParameter('websiteid', $websiteId);
    $query = $qb->getQuery();
    $result = $query->getResult();

    $data = array();
    foreach ($result as $row) {
      $data[$row['id']] = array(
        'id' => $row['id'],
        'name' => $row['name'],
      );
    }

    $this->clearEntityManager();

    return $data;
  }

  /**
   * @param  string $id
   *
   * @return  OrmTemplate
   * @throws \Cms\Exception
   */
  public function getById($id, $websiteId)
  {
    $template = $this->getByIdInternal($websiteId, $id);
    if ($template === null) {
      throw new CmsException(302, __METHOD__, __LINE__);
    }
    $this->clearEntityManager();
    return $template;
  }

  /**
   * Ermittelt mehrere Templates in einer Abfrage
   *
   * @param array  $ids
   * @param string $websiteId
   *
   * @return array
   * @throws CmsException
   */
  public function getByIds(array $ids, $websiteId)
  {
    try {
      $data = $this->getEntityManager()
        ->getRepository('Orm\Entity\Template')
        ->findByIds($ids, $websiteId);
    } catch (\Exception $e) {
      throw new CmsException(112, __METHOD__, __LINE__, null, $e);
    }
    $this->clearEntityManager();
    return $data;
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   *
   * @return boolean
   * @throws \Cms\Exception
   */
  public function delete($id, $websiteId)
  {
    $template = $this->getByIdInternal($websiteId, $id);
    if ($template === null) {
      throw new CmsException(307, __METHOD__, __LINE__);
    }

    $entityManager = $this->getEntityManager();
    $relatedPages = $entityManager->getRepository('Orm\Entity\Page')
      ->findBy(array(
          'templateid' => $id,
          'websiteid' => $websiteId));

    if (count($relatedPages) > 0) {
      throw new CmsException(308, __METHOD__, __LINE__);
    }

    try {
      $entityManager->remove($template);
      $entityManager->flush();
      $this->clearEntityManager();
      return true;
    } catch (Exception $e) {
      throw new CmsException(309, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  array  $ids
   * @param  string $websiteId
   *
   * @throws \Cms\Exception
   */
  public function deleteByIds($websiteId, array $ids)
  {
    $dql = sprintf(
        "DELETE FROM Orm\\Entity\\Template t
                    WHERE t.id IN ('%s')
                    AND t.websiteid LIKE '%s'",
        implode("','", $ids),
        $websiteId
    );

    $query = $this->getEntityManager()->createQuery($dql);
    $result = $query->getResult();
    $this->clearEntityManager();
    return $result;
  }

  /**
   * @param string $websiteId
   */
  public function deleteByWebsiteId($websiteId)
  {
    $dql = sprintf("DELETE FROM Orm\\Entity\\Template t WHERE t.websiteid LIKE '%s'", $websiteId);
    $query = $this->getEntityManager()->createQuery($dql);
    $result = $query->getResult();
    $this->clearEntityManager();
    return $result;
  }

  /**
   * Durchsucht den Content eines Templates
   *
   * @param  string $needle
   * @param  string $websiteId
   *
   * @return array
   * @throws \Cms\Exception
   */
  public function searchInContent($needle, $websiteId)
  {
    try {
      $data = $this->getEntityManager()
        ->getRepository('Orm\Entity\Template')
        ->searchInContent($needle, $websiteId);
      $this->clearEntityManager();
      return $data;
    } catch (Exception $e) {
      throw new CmsException(1, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * Kopiert Templates zu einer neuen Website
   *
   * @param string $websiteId
   * @param string $newWebsiteId
   * @param array  $templateIds
   *
   * @return bool
   * @throws CmsException
   */
  public function copyToNewWebsite($websiteId, $newWebsiteId, array $templateIds = array())
  {
    try {
      // keine Templates explizit angegeben -> alle Templates kopieren
      if (count($templateIds) == 0) {
        $templates = $this->getEntityManager()
          ->getRepository('Orm\Entity\Template')
          ->findByWebsiteid($websiteId);
      } else {
        $templates = $this->getEntityManager()
          ->getRepository('Orm\Entity\Template')
          ->findByIds($templateIds, $websiteId);
      }
    } catch (Exception $e) {
      throw new CmsException(703, __METHOD__, __LINE__, null, $e);
    }

    foreach ($templates as $template) {
      $newTemplate = clone $template;
      $newTemplate->setWebsiteid($newWebsiteId);
      $this->getEntityManager()->persist($newTemplate);
    }
    $this->getEntityManager()->flush();
    $this->clearEntityManager();

    return true;
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   *
   * @return boolean
   */
  public function existsTemplate($id, $websiteId)
  {
    $template = $this->getByIdInternal($websiteId, $id);
    $this->clearEntityManager();
    return $template !== null;
  }

  /**
   * returns the pages that have a relation with the given module id
   *
   * @param string $websiteId
   * @param string $moduleId
   *
   * @return array
   * @throws CmsException
   */
  public function findByWebsiteIdAndModuleId($websiteId, $moduleId)
  {
    try {
      // this is a very bad way to check the relation
      // you know it, i know it, so go away
      $data = $this->getEntityManager()
        ->getRepository('Orm\Entity\Template')
        ->findByWebsiteIdAndModuleId($websiteId, $moduleId);
      $this->clearEntityManager();
      return $data;
    } catch (Exception $e) {
      throw new CmsException(1, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * returns the used module ids for the given website and template id
   *
   * @param  string $websiteId
   * @param  string $id
   *
   * @return array
   * @throws CmsException
   */
  public function getUsedModuleIds($websiteId, $id)
  {
    $template = $this->getByIdInternal($websiteId, $id);
    if ($template === null) {
      throw new CmsException(302, __METHOD__, __LINE__);
    }
    return $template->getUsedmoduleids();
  }

  /**
   * {@inheritDoc}
   */
  public function getIdsByWebsiteId($websiteId)
  {
    $ids = array();
    try {
      $qb = $this->getEntityManager()->createQueryBuilder();
      $qb->add('select', 't.id')
        ->add('from', 'Orm\Entity\Template t')
        ->add('where', 't.websiteid = :websiteid')
        ->setParameter('websiteid', $websiteId);
      $result = $qb->getQuery()->getResult();
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(310, __METHOD__, __LINE__, array(
        'websiteId' => $websiteId), $e);
    }
    foreach ($result as $row) {
      $ids[] = $row['id'];
    }
    return $ids;
  }

  /**
   * @param OrmTemplate $templateOrm
   * @param array       $attributes
   */
  protected function setAttributesToOrm(OrmTemplate $templateOrm, array $attributes)
  {
    if (array_key_exists('name', $attributes)) {
      $templateOrm->setName($attributes['name']);
    }
    if (array_key_exists('pageType', $attributes)) {
      $templateOrm->setPagetype($attributes['pageType']);
    }
    if (array_key_exists('content', $attributes)) {
      $contentString = (is_array($attributes['content']))
        ? \Seitenbau\Json::encode($attributes['content'])
        : $attributes['content'];
      $templateOrm->setContent($contentString);
    }
  }
}
