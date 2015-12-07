<?php


namespace Cms\Dao\TemplateSnippet;

use Cms\Dao\TemplateSnippet as TemplateSnippetDaoInterface;
use Cms\Dao\Doctrine as DoctrineBase;
use Cms\Exception as CmsException;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;
use Orm\Entity\TemplateSnippet as OrmTemplateSnippet;

/**
 * Doctrine dao for template snippets
 *
 * @package Cms\Dao\TemplateSnippet
 */
class Doctrine extends DoctrineBase implements TemplateSnippetDaoInterface
{
  /**
   * returns all TemplateSnippets of the given Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param  string               $orderDirection
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\TemplateSnippet[]
   */
  public function getAll(TemplateSnippetSource $snippetSource, $orderDirection = null)
  {
    if (!is_string($orderDirection)) {
      $orderDirection = 'ASC';
    }
    $websiteId = $snippetSource->getWebsiteId();
    try {
      $ormList = $this->getEntityManager()
        ->getRepository('Orm\Entity\TemplateSnippet')
        ->findByWebsiteIdOrderedByName($websiteId, $orderDirection);
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(1601, __METHOD__, __LINE__, null, $e);
    }

    return $this->convertOrm($ormList);
  }

  /**
   * returns the specified "Template Snippets" of the given Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param  array                $ids
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\TemplateSnippet[]
   */
  public function getByIds(TemplateSnippetSource $snippetSource, array $ids)
  {
    $websiteId = $snippetSource->getWebsiteId();
    try {
      $ormList = $this->getEntityManager()
        ->getRepository('Orm\Entity\TemplateSnippet')
        ->findByIds($websiteId, $ids);
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(1610, __METHOD__, __LINE__, null, $e);
    }

    return $this->convertOrmListToDataObjectList($ormList);
  }

  /**
   * return the TemplateSnippets of the given id and Website Id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param  string               $id
   *
   * @return \Cms\Data\TemplateSnippet
   */
  public function getById(TemplateSnippetSource $snippetSource, $id)
  {
    $websiteId = $snippetSource->getWebsiteId();
    $snippet = $this->convertOrmToDataObject($this->getOrmById($websiteId, $id));
    $this->clearEntityManager();
    return $snippet;
  }

  /**
   * deletes the TemplateSnippets of the given ids and website id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param  array                $ids
   *
   * @throws \Cms\Exception
   * @internal param string $websiteId
   */
  public function deleteByIds(TemplateSnippetSource $snippetSource, array $ids)
  {
    $websiteId = $snippetSource->getWebsiteId();
    $entityManager = $this->getEntityManager();
    try {
      $result = $entityManager->getRepository('Orm\Entity\TemplateSnippet')
        ->deleteByIds($websiteId, $ids);
      $this->clearEntityManager();
      return $result;
    } catch (\Exception $e) {
      throw new CmsException(1608, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * deletes the TemplateSnippet of the given id and website id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param  string               $id
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function delete(TemplateSnippetSource $snippetSource, $id)
  {
    $websiteId = $snippetSource->getWebsiteId();
    $entityManager = $this->getEntityManager();
    $templateSnippet = $entityManager->getRepository('Orm\Entity\TemplateSnippet')
      ->findOneBy(array(
          'id' => $id,
          'websiteid' => $websiteId));
    if ($templateSnippet === null) {
      throw new CmsException(1607, __METHOD__, __LINE__);
    }

    try {
      $entityManager->remove($templateSnippet);
      $entityManager->flush();
      $this->clearEntityManager();
      return true;
    } catch (\Exception $e) {
      throw new CmsException(1609, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param string $websiteId
   */
  public function deleteByWebsiteId(TemplateSnippetSource $snippetSource)
  {
    $websiteId = $snippetSource->getWebsiteId();
    $dql = sprintf("DELETE FROM Orm\Entity\TemplateSnippet ts WHERE ts.websiteid LIKE '%s'", $websiteId);
    $query = $this->getEntityManager()->createQuery($dql);
    $result = $query->getResult();
    $this->clearEntityManager();
    return $result;
  }

  /**
   * creates a new TemplateSnippet
   *
   * @param TemplateSnippetSource     $snippetSource
   * @param \Cms\Data\TemplateSnippet $snippet
   *
   * @return \Cms\Data\TemplateSnippet
   * @throws \Cms\Exception
   */
  public function create(TemplateSnippetSource $snippetSource, DataTemplateSnippet $snippet)
  {
    try {
      $snippetOrm = new OrmTemplateSnippet();

      $this->setAttributesToOrm($snippetOrm, $snippet, true);
      $snippetOrm->setWebsiteid($snippetSource->getWebsiteId());

      $currentId = $snippet->getId();
      if (!empty($currentId)) {
        $snippetOrm->setId($currentId);
      } else {
        $snippetOrm->setNewGeneratedId();
      }

      $entityManager = $this->getEntityManager();
      $entityManager->persist($snippetOrm);
      $entityManager->flush();
      $entityManager->refresh($snippetOrm);
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(1606, __METHOD__, __LINE__, null, $e);
    }
    return $this->convertToCmsDataObject($snippetOrm);
  }

  /**
   * updates the TemplateSnippet of the given id and website id
   *
   * @param TemplateSnippetSource     $snippetSource
   * @param \Cms\Data\TemplateSnippet $snippet
   *
   * @return \Cms\Data\TemplateSnippet
   * @throws \Cms\Exception
   */
  public function update(TemplateSnippetSource $snippetSource, DataTemplateSnippet $snippet)
  {
    $snippetOrm = $this->getOrmById($snippetSource->getWebsiteId(), $snippet->getId());
    try {
      $this->setAttributesToOrm($snippetOrm, $snippet);

      $entityManager = $this->getEntityManager();
      $entityManager->persist($snippetOrm);
      $entityManager->flush();
      $entityManager->refresh($snippetOrm);
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(1611, __METHOD__, __LINE__, null, $e);
    }

    return $this->convertToCmsDataObject($snippetOrm);
  }

  /**
   * copy TemplateSnippets of the given ids and website id into another website
   *
   * @param TemplateSnippetSource $snippetSourceFrom
   * @param TemplateSnippetSource $snippetSourceTo
   * @param array                 $ids
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function copyToNewWebsite(
      TemplateSnippetSource $snippetSourceFrom,
      TemplateSnippetSource $snippetSourceTo,
      array $ids = array()
  ) {
    $websiteIdFrom = $snippetSourceFrom->getWebsiteId();
    $websiteIdTo = $snippetSourceTo->getWebsiteId();
    try {
      if (count($ids) == 0) {
        // copy all template snippets
        $templateSnippets = $this->getEntityManager()
          ->getRepository('Orm\Entity\TemplateSnippet')
          ->findByWebsiteIdOrderedByName($websiteIdFrom);
      } else {
        // copy template snippets by given ids
        $templateSnippets = $this->getEntityManager()
          ->getRepository('Orm\Entity\TemplateSnippet')
          ->findByIds($websiteIdFrom, $ids);
      }
    } catch (\Exception $e) {
      throw new CmsException(1612, __METHOD__, __LINE__, null, $e);
    }

    foreach ($templateSnippets as $snippets) {
      $newSnippet = clone $snippets;
      $newSnippet->setWebsiteid($websiteIdTo);
      $this->getEntityManager()->persist($newSnippet);
    }
    $this->getEntityManager()->flush();
    $this->clearEntityManager();

    return true;
  }

  /**
   * Checks if there is a template snippet under the given TemplateSnippet-Id and Website-Id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function existsSnippet(TemplateSnippetSource $snippetSource, $id)
  {
    try {
      $websiteId = $snippetSource->getWebsiteId();
      $data = $this->getEntityManager()
        ->getRepository('Orm\Entity\TemplateSnippet')
        ->findOneBy(array(
          'id' => $id,
          'websiteid' => $websiteId
        ));
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(1602, __METHOD__, __LINE__, null, $e);
    }
    return $data !== null;
  }

  /**
   * search over the TemplateSnippets content and returns the findings
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $needle
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\TemplateSnippet[]
   */
  public function searchInContent(TemplateSnippetSource $snippetSource, $needle)
  {
    try {
      $websiteId = $snippetSource->getWebsiteId();
      $ormList = $this->getEntityManager()
        ->getRepository('Orm\Entity\TemplateSnippet')
        ->searchInContent($websiteId, $needle);
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(1, __METHOD__, __LINE__, null, $e);
    }
    return $this->convertOrmListToDataObjectList($ormList);
  }

  /**
   * return the TemplateSnippets orm of the given id and website id
   *
   * @param  string $websiteId
   * @param  string $id
   *
   * @return \Orm\Entity\TemplateSnippet
   * @throws \Cms\Exception
   */
  protected function getOrmById($websiteId, $id)
  {
    try {
      $templateSnippet = $this->getEntityManager()
        ->getRepository('Orm\Entity\TemplateSnippet')
        ->findOneBy(array(
          'id' => $id,
          'websiteid' => $websiteId));
    } catch (\Exception $e) {
      throw new CmsException(1603, __METHOD__, __LINE__, null, $e);
    }

    if ($templateSnippet === null) {
      throw new CmsException(1602, __METHOD__, __LINE__);
    }

    return $templateSnippet;
  }

  /**
   * set the TemplateSnippet attributes (if they are exists in the attributes array)
   *
   * @param \Orm\Entity\TemplateSnippet      $snippetOrm
   * @param \Cms\Data\TemplateSnippet $snippet
   */
  protected function setAttributesToOrm(
      OrmTemplateSnippet $snippetOrm,
      DataTemplateSnippet $snippet
  ) {
    $snippetOrm->setName($snippet->getName());
    $snippetOrm->setDescription($snippet->getDescription());
    $snippetOrm->setCategory($snippet->getCategory());
    $snippetOrm->setContent($snippet->getContent());
  }

  /**
   * @param \Orm\Entity\TemplateSnippet|\Orm\Entity\TemplateSnippet[] $data
   *
   * @throws \Cms\Exception
   * @return \Cms\Data\TemplateSnippet[]
   */
  protected function convertOrm($data)
  {
    if (is_array($data)) {
      return $this->convertOrmListToDataObjectList($data);
    }
    if ($data instanceof OrmTemplateSnippet) {
      return $this->convertOrmToDataObject($data);
    }
    throw new CmsException(2, __METHOD__, __LINE__, array(
      'message' => 'Error at converting snippet result'));
  }

  /**
   * @param \Orm\Entity\TemplateSnippet[] $ormList
   *
   * @return \Cms\Data\TemplateSnippet[]
   */
  protected function convertOrmListToDataObjectList(array $ormList)
  {
    $snippets = array();
    foreach ($ormList as $key => $orm) {
      if ($orm instanceof OrmTemplateSnippet) {
        $snippets[$key] = $this->convertOrmToDataObject($orm);
      }
    }
    return $snippets;
  }

  /**
   * @param \Orm\Entity\TemplateSnippet $orm
   *
   * @return \Cms\Data\TemplateSnippet
   */
  protected function convertOrmToDataObject(OrmTemplateSnippet $orm)
  {
    /** @var $snippet \Cms\Data\TemplateSnippet */
    $snippet = $orm->toCmsData();
    $snippet->setReadonly(false);
    $snippet->setSourceType($snippet::SOURCE_LOCAL);
    return $snippet;
  }
}
