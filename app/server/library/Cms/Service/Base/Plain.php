<?php
namespace Cms\Service\Base;

use Cms\Service\Iface\Marker as ServiceMarker;
use Cms\Service\Iface\Plain as PlainService;

/**
 * Plain Service Base
 *
 * @package      Cms
 * @subpackage   Service
 */
abstract class Plain implements ServiceMarker, PlainService
{
  public function __construct()
  {
    $this->init();
  }

  public function init()
  {
  }

  public function callUserFunction($method, $arguments)
  {
    return call_user_func_array(array($this, $method), $arguments);
  }
  
  /**
   * @param  string $servicename
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
}
