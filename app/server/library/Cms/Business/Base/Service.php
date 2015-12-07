<?php
namespace Cms\Business\Base;

use Cms\Business\Iface;
use Cms\Exception;

/**
 * Stellt die Basis fuer Business Objekte mit Service
 *
 * @package      Cms
 * @subpackage   Business
 */

abstract class Service extends Plain implements Iface\Marker, Iface\hasService
{
  protected $service = '';

  public function __construct($businessname)
  {
    $this->initService($businessname);
    parent::__construct();
  }

  protected function initService($servicename)
  {
    $service = 'Cms\Service\\' . $servicename;

    if (class_exists($service)) {
      $this->setService(new $service($servicename));
    } else {
      $message = sprintf("Service '%s' could not be loaded", $service);
      throw new Exception($message);
    }
  }

  protected function setService(\Cms\Service\Iface\Marker $service)
  {
    $this->service = $service;
  }

  /**
   * Gibt den angegebenen Service zurueck
   *
   * Wird kein Servicename uebergeben, so wird der gleichbenannte Service des
   * Business zurueckgegeben
   *
   * @return Cms\Service\Iface\Marker
   */
  public function getService($servicename = '')
  {
    if ($servicename == '') {
      return $this->service;
    } else {
      $service = 'Cms\Service\\' . $servicename;

      if (class_exists($service)) {
        return new $service($servicename);
      }
    }
  }

  /**
   * Aufruf erst im Business suchen, anschliessend im Service
   *
   * @param string $method
   * @param mixed $arguments
   * @return mixed
   */
  public function __call($method, $arguments)
  {
    $service = $this->getService();
    if (method_exists($service, $method)) {
      return $this->getService()->callUserFunction($method, $arguments);
    }

    throw new \Exception(sprintf('Call to undefined method "%s"', $method));
  }
}
