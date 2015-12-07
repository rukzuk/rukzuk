<?php
namespace Cms\Dao;

use Seitenbau\Registry as Registry;
use Cms\Dao\Dao as DaoMarkerInterface;

/**
 * Stelle die Voraussetzungen zur Nutzung von Doctrine zu Verfuegung
 *
 * @package      Cms
 * @subpackage   Dao
 */

abstract class Doctrine implements DaoMarkerInterface
{
  /**
   * Entity Manager
   * @var Doctrine\ORM\EntityManager
   */
  private $entityManager;

  public function __construct()
  {
    $this->initEntityManager();
  }

  /**
   * init the doctrine entity manager form registry
   */
  protected function initEntityManager()
  {
    $this->setEntityManager(Registry::getEntityManager());
  }

  /**
   * set the doctrine entity manager
   *
   * @param Doctrine\Orm\EntityManager $entityManager
   */
  protected function setEntityManager($entityManager)
  {
    $this->entityManager = $entityManager;
  }

  /**
   * return doctrine entity manager
   *
   * @return \Doctrine\ORM\EntityManager
   */
  protected function getEntityManager()
  {
    return $this->entityManager;
  }

  /**
   * clear the entity manager
   */
  protected function clearEntityManager()
  {
    $this->getEntityManager()->clear();
  }

  /**
   * Konvertiert Doctrince DAOs zu CMS eigenen Daten-Objekten
   *
   * @param mixed $data
   *
   * @throws \Cms\Exception
   * @return  mixed
   */
  public function convertToCmsDataObject($data)
  {
    if (is_array($data)) {
      $result = array();
      if (count($data) > 0) {
        foreach ($data as $key => $value) {
          $result[$key] = $this->convertToCmsDataObject($value);
        }
      }
    } elseif (is_object($data)) {
      if (method_exists($data, 'toCmsData')) {
        $result = $data->toCmsData();
      } else {
        throw new \Cms\Exception(-13, __METHOD__, __LINE__);
      }
    } else {
      $result = $data;
    }

    return $result;
  }
}
