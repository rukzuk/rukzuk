<?php
namespace Cms\Dao\Page;

use Cms\Dao\Page as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Orm\Entity\Page;
use Cms\Exception as CmsException;
use Exception;

/**
 * Page Dao fuer Doctrine2-Anbindung
 *
 * @package      Cms
 * @subpackage   Dao
 */
class Doctrine extends DoctrineBase implements Dao
{
  /**
   * Gibt eine Page anhand der ID und Website-ID zurueck,
   * bereinigt aber NICHT den Entity Manager
   *
   * @param string $id
   * @param string $websiteId
   *
   * @return \Orm\Entity\Page
   * @throws \Cms\Exception
   */
  protected function getByIdInternal($id, $websiteId)
  {
    $data = array(
      'id' => $id,
      'websiteid' => $websiteId
    );

    try {
      $page = $this->getEntityManager()
        ->getRepository('Orm\Entity\Page')
        ->findOneBy($data);
    } catch (\Exception $e) {
      throw new CmsException(703, __METHOD__, __LINE__, $data, $e);
    }

    if ($page == null) {
      throw new CmsException(702, __METHOD__, __LINE__, $data);
    }
    return $page;
  }

  /**
   * Gibt eine Page anhand der ID und Website-ID zurueck
   * und bereinigt den Entity Manager
   *
   * @param string $id
   * @param string $websiteId
   *
   * @return \Orm\Entity\Page
   * @throws \Cms\Exception
   */
  public function getById($id, $websiteId)
  {
    $page = $this->getByIdInternal($id, $websiteId);
    $this->clearEntityManager();
    return $page;
  }

  /**
   * Kopiert eine vorhandene Page anhand der ID unter einem neuen Namen
   *
   * @param string $id
   * @param string $websiteId
   * @param string $newname
   *
   * @throws \Cms\Exception
   * @return \Orm\Entity\Page
   */
  public function copy($id, $websiteId, $newname = null)
  {
    $data = array(
      'id' => $id,
      'websiteid' => $websiteId
    );

    $page = $this->getByIdInternal($id, $websiteId);

    $newPage = clone $page;
    $newPage->setNewGeneratedId();
    if ($newname !== null) {
      $newPage->setName($newname);
    }

    try {
      $this->getEntityManager()->persist($newPage);
      $this->getEntityManager()->flush();
      $this->clearEntityManager();
    } catch (Exception $e) {
      throw new CmsException(742, __METHOD__, __LINE__, $data, $e);
    }

    return $newPage;
  }

  /**
   * Copy all pages to new website
   *
   * @param string $websiteId
   * @param string $newWebsiteId
   *
   * @return true
   * @throws \Cms\Exception
   */
  public function copyPagesToNewWebsite($websiteId, $newWebsiteId)
  {
    // to reduce memory usage: first, collect all page ids, then copy each page
    $pageIds = $this->getIdsByWebsiteId($websiteId);
    foreach ($pageIds as $pageId) {
      try {
        $page = $this->getByIdInternal($pageId, $websiteId);
        $newPage = clone $page;
        $newPage->setWebsiteId($newWebsiteId);
        $this->getEntityManager()->persist($newPage);
        $this->getEntityManager()->flush();
        $this->clearEntityManager();
      } catch (Exception $e) {
        throw new CmsException(703, __METHOD__, __LINE__, null, $e);
      }
    }
    return true;
  }

  /**
   * Loescht eine vorhandene Page anhand der ID und Website-ID
   *
   * @param string $id
   * @param string $websiteId
   *
   * @return bool
   * @throws \Cms\Exception
   */
  public function delete($id, $websiteId)
  {
    $page = $this->getByIdInternal($id, $websiteId);

    try {
      $this->getEntityManager()->remove($page);
      $this->getEntityManager()->flush();
      $this->clearEntityManager();
    } catch (Exception $e) {
      throw new CmsException(722, __METHOD__, __LINE__, null, $e);
    }

    return true;
  }

  /**
   * Loescht mehrere Pages anhand der IDs und Website-ID
   *
   * @param array  $ids
   * @param string $websiteId
   */
  public function deleteByIds($websiteId, array $ids)
  {
    $idstring = "'" . implode("','", $ids) . "'";
    $dql = sprintf(
        "DELETE FROM Orm\Entity\Page p
                    WHERE p.id IN (%s)
                    AND p.websiteid LIKE '%s'",
        $idstring,
        $websiteId
    );

    $query = $this->getEntityManager()->createQuery($dql);
    return $query->getResult();
  }

  /**
   * @param string $websiteId
   */
  public function deleteByWebsiteId($websiteId)
  {
    $dql = sprintf("DELETE FROM Orm\Entity\Page p WHERE p.websiteid LIKE '%s'", $websiteId);
    $query = $this->getEntityManager()->createQuery($dql);
    return $query->getResult();
  }

  /**
   * Aktualisiert einen Eintrag mit den angegeben Attributen
   *
   * @param string $id
   * @param string $websiteId
   * @param array  $attributes
   *
   * @throws \Cms\Exception
   * @return \Orm\Entity\Page
   */
  public function update($id, $websiteId, $attributes)
  {
    // get the page and do not clear the entity manager
    $page = $this->getByIdInternal($id, $websiteId);

    try {
      $this->setAttributesToPage($attributes, $page);
      $this->getEntityManager()->persist($page);
      $this->getEntityManager()->flush();
      $this->getEntityManager()->refresh($page);
      $this->clearEntityManager();
    } catch (Exception $e) {
      throw new CmsException(708, __METHOD__, __LINE__, null, $e);
    }

    return $page;
  }

  /**
   * erstellt eine neue Page anhand der uebergebenen Attribute
   *
   * @param string  $websiteId
   * @param array   $attributes
   * @param boolean $useColumnsValuesId Defaults to false
   *
   * @throws \Cms\Exception
   * @return \Orm\Entity\Page
   */
  public function create($websiteId, $attributes, $useColumnsValuesId = false)
  {
    $page = new Page();

    if ($useColumnsValuesId && isset($attributes['id'])) {
      $page->setId($attributes['id']);
    } else {
      $page->setNewGeneratedId();
    }
    $page->setWebsiteId($websiteId);
    $this->setAttributesToPage($attributes, $page);

    try {
      $this->getEntityManager()->persist($page);
      $this->getEntityManager()->flush();
      $this->getEntityManager()->refresh($page);
      $this->clearEntityManager();
    } catch (Exception $e) {
      throw new CmsException(709, __METHOD__, __LINE__, null, $e);
    }

    return $page;
  }

  /**
   * Setzt die Attribute in ein ORM-Objekt
   *
   * @param array            $attributes
   * @param \Orm\Entity\Page $orm
   */
  private function setAttributesToPage($attributes, Page $orm)
  {
    if (isset($attributes['name'])) {
      $orm->setName($attributes['name']);
    }
    if (isset($attributes['description'])) {
      $orm->setDescription($attributes['description']);
    }
    if (isset($attributes['mediaid']) || array_key_exists('mediaid', $attributes)) {
      $orm->setMediaid($attributes['mediaid']);
    }
    if (isset($attributes['innavigation'])) {
      $orm->setInnavigation($attributes['innavigation']);
    }
    if (isset($attributes['navigationtitle'])) {
      $orm->setNavigationtitle($attributes['navigationtitle']);
    }
    if (isset($attributes['date'])) {
      $orm->setDate($attributes['date']);
    }
    if (isset($attributes['content'])) {
      $orm->setContent($attributes['content']);
    }
    if (isset($attributes['templateid'])) {
      $orm->setTemplateid($attributes['templateid']);
    }
    if (isset($attributes['templatecontent'])) {
      $orm->setTemplatecontent($attributes['templatecontent']);
    }
    if (isset($attributes['globalcontent'])) {
      $orm->setGlobalContent($attributes['globalcontent']);
    }
    if (isset($attributes['pageType'])) {
      $orm->setPagetype($attributes['pageType']);
    }
    if (isset($attributes['pageAttributes'])) {
      if (is_array($attributes['pageAttributes']) || is_object($attributes['pageAttributes'])) {
        $attributes['pageAttributes'] = json_encode($attributes['pageAttributes']);
      }
      if (is_string($attributes['pageAttributes'])) {
        $orm->setPageattributes($attributes['pageAttributes']);
      } else {
        $orm->setPageattributes(null);
      }
    }
  }

  /**
   * Gibt die Page-Namen zu den uebergebenen Website-ID zurueck
   *
   * @param string $websiteId
   *
   * @return array
   */
  public function getInfosByWebsiteId($websiteId)
  {
    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->add('select', 'p.id, p.name, p.mediaid, p.description, p.navigationtitle, p.innavigation, p.date, p.templateid, p.pagetype')
      ->add('from', 'Orm\Entity\Page p')
      ->add('where', 'p.websiteid = :websiteid')
      ->setParameter('websiteid', $websiteId);
    $query = $qb->getQuery();
    $result = $query->getResult();

    $data = array();
    foreach ($result as $row) {
      $data[$row['id']] = array(
        'name' => $row['name'],
        'description' => $row['description'],
        'mediaId' => $row['mediaid'],
        'navigationTitle' => $row['navigationtitle'],
        'inNavigation' => $row['innavigation'],
        'date' => $row['date'],
        'templateId' => $row['templateid'],
        'pageType' => $row['pagetype'],
      );
    }

    $this->clearEntityManager();

    return $data;
  }

  /**
   * @param  string $mediaId
   * @param  string $websiteId
   *
   * @return array
   * @throws \Cms\Exception
   */
  public function findByMediaAndWebsiteId($mediaId, $websiteId)
  {
    try {
      $data = $this->getEntityManager()
        ->getRepository('Orm\Entity\Page')
        ->findByMediaAndWebsiteId($mediaId, $websiteId);
      $this->clearEntityManager();
      return $data;
    } catch (Exception $e) {
      throw new CmsException(1, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function existsPage($id, $websiteId)
  {
    try {
      $data = $this->getEntityManager()
        ->getRepository('Orm\Entity\Page')
        ->findOneBy(array(
          'id' => $id,
          'websiteid' => $websiteId
        ));
    } catch (Exception $e) {
      throw new CmsException(702, __METHOD__, __LINE__, null, $e);
    }
    $this->clearEntityManager();
    return $data !== null;
  }

  /**
   * Liefert alle zu einem Template verlinkten Pages einer Website zurueck
   *
   * @param string $templateId
   * @param string $websiteId
   *
   * @return  array
   */
  public function getTemplateLinkedPages($templateId, $websiteId)
  {
    $data = $this->getEntityManager()
      ->getRepository('Orm\Entity\Page')
      ->findBy(array(
        'websiteid' => $websiteId,
        'templateid' => $templateId
      ));
    $this->clearEntityManager();
    return $data;
  }

  /**
   * returns the page ids that have a relation with the given template id
   *
   * @param string $websiteId
   * @param string $templateId
   *
   * @throws \Cms\Exception
   * @return  array
   */
  public function getIdsByWebsiteIdAndTemplateId($websiteId, $templateId)
  {

    try {
      $data = $this->getEntityManager()
        ->getRepository('Orm\Entity\Page')
        ->getIdsByWebsiteIdAndTemplateId($websiteId, $templateId);
      $this->clearEntityManager();
      return $data;
    } catch (Exception $e) {
      throw new CmsException(1, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * returns the pages that have a relation with the given module id
   *
   * @param string $websiteId
   * @param string $moduleId
   *
   * @throws \Cms\Exception
   * @return  array
   */
  public function findByWebsiteIdAndModuleId($websiteId, $moduleId)
  {
    try {
      // this is a very bad way to check the relation
      // you know it, i know it, so go away
      $data = $this->getEntityManager()
        ->getRepository('Orm\Entity\Page')
        ->searchInContentAndTemplateContent($websiteId, $moduleId);
      $this->clearEntityManager();
      return $data;
    } catch (Exception $e) {
      throw new CmsException(1, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * returns the ids of the pages related to the website given by website id
   *
   * @param string $websiteId
   *
   * @return array
   * @throws \Cms\Exception
   */
  public function getIdsByWebsiteId($websiteId)
  {
    $pageIds = array();
    try {
      $qb = $this->getEntityManager()->createQueryBuilder();
      $qb->add('select', 'p.id')
        ->add('from', 'Orm\Entity\Page p')
        ->add('where', 'p.websiteid = :websiteid')
        ->setParameter('websiteid', $websiteId);
      $result = $qb->getQuery()->getResult();
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(704, __METHOD__, __LINE__, array(
        'websiteId' => $websiteId), $e);
    }
    foreach ($result as $row) {
      $pageIds[] = $row['id'];
    }
    return $pageIds;
  }
}
