<?php
namespace Cms\Service\Base;

use Cms\Exception as CmsException;
use Cms\Service\Iface\Marker as ServiceMarker;
use Cms\Service\Iface\Dao as DaoService;
use Cms\Service\Iface\Plain as PlainService;
use \Zend_Controller_Front as FrontController;
use Cms\Dao\Factory as DaoFactory;
use Seitenbau\Registry;
use Seitenbau\Log;

/**
 * Service Dao Base
 *
 * @package      Cms
 * @subpackage   Service
 */

abstract class Dao extends Plain implements ServiceMarker, DaoService, PlainService
{
  protected $servicename;

  private $dao = null;

  /**
   * @param string  $servicename
   */
  public function __construct($servicename)
  {
    $this->servicename = $servicename;
    parent::__construct();
  }

  public function init()
  {
    parent::init();

    $this->initDao();
  }

  /**
   * Initialisiert das Dao zum Service
   */
  public function initDao()
  {
    $daoName = $this->getDaoName();
    $this->setDao(DaoFactory::get($daoName));
  }

  /**
   * @return string
   */
  protected function getDaoName()
  {
    $serviceConfig = Registry::getConfig()->service;
    if (isset($serviceConfig->{$this->servicename}) &&
      isset($serviceConfig->{$this->servicename}->dao)) {
      return $serviceConfig->{$this->servicename}->dao;
    } else {
      return $this->servicename;
    }
  }

  /**
   * Ruft eine Funktion aus dem DAO auf
   * Die Ergebnisse des DAOs werden zu CMS eigenen Datenobjekte konvertiert
   *
   * @param string  $function
   * @param array $arguments
   * @return  mixed
   */
  public function execute($function, array $arguments = array())
  {
    $daoData = call_user_func_array(
        array($this->getDao(), $function),
        $arguments
    );
    $result = $this->getDao()->convertToCmsDataObject($daoData);
    return $result;
  }

  /**
   * Erstellt aus den angegebenen Json decodierten Feldern des
   * Attributes-Array Strings, damit diese in der DB gespeichert werden koennen
   *
   * @param array $attributes
   * @param array $keys
   */
  protected function encodeJsonAttributesToJsonString(array $attributes, array $keys)
  {
    foreach ($keys as $key) {
      if (isset($attributes[$key])) {
        $attributes[$key] = \Zend_Json::encode($attributes[$key]);
      }
    }

    return $attributes;
  }

  /**
   * Setzt das Dao Object
   *
   * @param \Cms\Dao\Dao   $dao
   */
  public function setDao($dao)
  {
    $this->dao = $dao;
  }

  /**
   * Gibt das Dao zurueck
   *
   * @return \Cms\Dao\Dao
   */
  public function getDao()
  {
    return $this->dao;
  }
}
