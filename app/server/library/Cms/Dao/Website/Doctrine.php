<?php
namespace Cms\Dao\Website;

use Cms\Dao\Website as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Cms\Exception as CmsException;
use Exception;
use Orm\Entity\Website;

/**
 * Website Dao fuer Doctrine2-Anbindung
 *
 * @package      Cms
 * @subpackage   Dao
 */

class Doctrine extends DoctrineBase implements Dao
{

  /**
   * Gibt alle Website Eintraege zurueck
   *
   * @throws \Cms\Exception
   * @return  array
   */
  public function getAll()
  {
    try {
      $queryBuilder = $this->getEntityManager()
                           ->createQueryBuilder();

      $queryBuilder->add('select', 'w')
                   ->add('from', 'Orm\Entity\Website w')
                   ->orderBy('w.name', 'ASC');

      $query = $queryBuilder->getQuery();
      $result = $query->getResult();
    } catch (Exception $e) {
      throw new CmsException(601, __METHOD__, __LINE__, null, $e);
    }

    return $result;
  }

  /**
   * Number of actual Websites in Database
   *
   * @throws \Cms\Exception
   * @return int
   */
  public function getCount()
  {
    try {
      $qb = $this->getEntityManager()->createQueryBuilder();
      $qb->select('count(w.id)');
      $qb->from('Orm\Entity\Website', 'w');
      $result = $qb->getQuery()->getSingleScalarResult();
    } catch (Exception $e) {
      throw new CmsException(612, __METHOD__, __LINE__, null, $e);
    }

    return $result;
  }

  /**
   * @param string $id
   *
   * @throws \Cms\Exception
   * @return \Orm\Entity\Website
   */
  public function getById($id)
  {
    try {
      $data = $this->getEntityManager()
                   ->getRepository('Orm\Entity\Website')
                   ->findOneById($id);
    } catch (Exception $e) {
      throw new CmsException(600, __METHOD__, __LINE__, array('id' => $id), $e);
    }

    if ($data === null) {
      throw new CmsException(602, __METHOD__, __LINE__, array('id' => $id));
    }

    return $data;
  }

  /**
   * @param  string $id
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function existsWebsite($id)
  {
    try {
      $data = $this->getEntityManager()
                   ->getRepository('Orm\Entity\Website')
                   ->findOneById($id);
    } catch (Exception $e) {
      throw new CmsException(600, __METHOD__, __LINE__, array('id' => $id), $e);
    }
    return $data !== null;
  }

  public function copy($id, array $attributes)
  {
    $website = $this->getById($id);
    try {
      $newWebsite = clone $website;
      $this->setAttributesToWebsite($attributes, $newWebsite);
      $newWebsite->setNewGeneratedId();
      $newWebsite->setShortId($this->createShortId());
      $newWebsite->setPublishingEnabled(false);
      $newWebsite->setPublish(json_encode((object)[]));
      $newWebsite->setCreationMode(\Cms\Version::getMode());
      $newWebsite->setMarkedForDeletion(false);
      $this->getEntityManager()->persist($newWebsite);
      $this->getEntityManager()->flush();
    } catch (Exception $e) {
      throw new CmsException(603, __METHOD__, __LINE__, array('id' => $id), $e);
    }

    return $newWebsite;
  }

  public function deleteById($id)
  {
    $website = $this->getById($id);
    try {
      $this->getEntityManager()->remove($website);
      $this->getEntityManager()->flush();
    } catch (Exception $e) {
      throw new CmsException(604, __METHOD__, __LINE__, array('id' => $id), $e);
    }
  }

  /**
   * @param string $id
   *
   * @throws \Cms\Exception
   */
  public function markForDeletion($id)
  {
    $website = $this->getById($id);
    try {
      $website->setMarkedForDeletion(true);
      $this->getEntityManager()->persist($website);
      $this->getEntityManager()->flush();
    } catch (Exception $e) {
      throw new CmsException(607, __METHOD__, __LINE__, array('id' => $id), $e);
    }
  }

  public function getByMarkedForDeletion()
  {
    try {
      $websites = $this->getEntityManager()
                       ->getRepository('Orm\Entity\Website')
                       ->findBy(array('ismarkedfordeletion' => true));
    } catch (Exception $e) {
      throw new CmsException(610, __METHOD__, __LINE__, null, $e);
    }

    return $websites;
  }

  public function getByCreationMode($creationMode)
  {
    try {
      $websites = $this->getEntityManager()
                       ->getRepository('Orm\Entity\Website')
                       ->findBy(array('creationmode' => $creationMode));
    } catch (Exception $e) {
      throw new CmsException(611, __METHOD__, __LINE__, null, $e);
    }

    return $websites;
  }

  /**
   * @param string $id
   * @param array  $attributes
   *
   * @throws \Cms\Exception
   * @return \Orm\Entity\Website
   */
  public function update($id, $attributes)
  {
    $website = $this->getById($id);
    try {
      $this->setAttributesToWebsite($attributes, $website);
      $this->getEntityManager()->persist($website);
      $this->getEntityManager()->flush();
      $this->getEntityManager()->refresh($website);
    } catch (Exception $e) {
      throw new CmsException(605, __METHOD__, __LINE__, array('id' => $id), $e);
    }

    return $website;
  }

  /**
   * @param array   $attributes
   * @param boolean $useAttributesId
   *
   * @return \Orm\Entity\Website
   */
  public function create($attributes, $useAttributesId = false)
  {
    $website = new Website();
    try {
      if ($useAttributesId && isset($attributes['id'])) {
        $website->setId($attributes['id']);
      } else {
        $website->setNewGeneratedId();
      }
      $website->setShortId($this->createShortId());
      $this->setAttributesToWebsite($attributes, $website);
      $website->setCreationMode(\Cms\Version::getMode());
      $website->setMarkedForDeletion(false);
      $this->getEntityManager()->persist($website);
      $this->getEntityManager()->flush();
      $this->getEntityManager()->refresh($website);
    } catch (Exception $e) {
      throw new CmsException(606, __METHOD__, __LINE__, null, $e);
    }
    return $website;
  }

  /**
   * @param  string $id
   *
   * @throws \Cms\Exception
   * @return integer
   */
  public function increaseVersion($id)
  {
    $website = $this->getById($id);
    try {
      $website->setVersion($website->getVersion() + 1);
      $this->getEntityManager()->persist($website);
      $this->getEntityManager()->flush();
    } catch (Exception $e) {
      throw new CmsException(608, __METHOD__, __LINE__, null, $e);
    }

    return $website->getVersion();
  }

  /**
   * @param  string $id
   *
   * @throws \Cms\Exception
   * @return integer
   */
  public function decreaseVersion($id)
  {
    $website = $this->getById($id);
    try {
      if ($website->getVersion() > 0) {
        $website->setVersion($website->getVersion() - 1);
      } else {
        return $website->getVersion();
      }
      $this->getEntityManager()->persist($website);
      $this->getEntityManager()->flush();
    } catch (Exception $e) {
      throw new CmsException(609, __METHOD__, __LINE__, null, $e);
    }

    return $website->getVersion();
  }

  /**
   * @param string $shortId
   *
   * @return bool
   * @throws \Cms\Exception
   */
  protected function shortIdExists($shortId)
  {
    try {
      $data = $this->getEntityManager()
        ->getRepository('Orm\Entity\Website')
        ->findOneBy(array('shortid' => $shortId));
      $this->clearEntityManager();
    } catch (\Exception $e) {
      throw new CmsException(600, __METHOD__, __LINE__, array('id' => $shortId), $e);
    }
    return ($data !== null);
  }

  /**
   * Setzt Attribute in einer Website neu
   *
   * @param array $attributes
   * @param Website $orm
   */
  protected function setAttributesToWebsite($attributes, Website $orm)
  {
    if (isset($attributes['name'])) {
      $orm->setName($attributes['name']);
    }
    if (isset($attributes['navigation'])) {
      $orm->setNavigation($attributes['navigation']);
    }
    if (isset($attributes['description'])) {
      $orm->setDescription($attributes['description']);
    }
    if (isset($attributes['publishingenabled'])) {
      $orm->setPublishingEnabled((bool)$attributes['publishingenabled']);
    }
    if (isset($attributes['publish'])) {
      $orm->setPublish($attributes['publish']);
    }
    if (isset($attributes['version']) && is_int($attributes['version'])) {
      $orm->setVersion($attributes['version']);
    }
    if (isset($attributes['colorscheme'])) {
      $orm->setColorscheme($attributes['colorscheme']);
    }
    if (isset($attributes['resolutions'])) {
      $orm->setResolutions($attributes['resolutions']);
    }
    if (isset($attributes['home'])) {
      $orm->setHome($attributes['home']);
    }
    if (isset($attributes['usedsetid'])) {
      $orm->setUsedSetId($attributes['usedsetid']);
    }
  }

  /**
   * @return string
   * @throws \Exception
   */
  protected function createShortId()
  {
    $secCounter = pow(36, 4);
    do {
      $shortId = $this->createRandomString(2, 4);
      if (!$this->shortIdExists($shortId)) {
        return $shortId;
      }
    } while (--$secCounter > 0);
    throw new CmsException(613, __METHOD__, __LINE__);
  }

  /**
   * @param integer $minDigits
   * @param integer $maxDigits
   *
   * @return string
   */
  protected function createRandomString($minDigits, $maxDigits)
  {
    return base_convert(mt_rand(pow(36, $minDigits-1), pow(36, $maxDigits)-1), 10, 36);
  }
}
