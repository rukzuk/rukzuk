<?php
namespace Cms\Data;

/**
 * Class ModuleQuota
 * @package Cms\Data
 */
class ModuleQuota
{

  /**
   * @var boolean
   */
  private $enableDev = false;

  /**
   * @param null|boolean $enableDev
   */
  public function __construct($enableDev = null)
  {
    if (!is_null($enableDev)) {
      $this->enableDev = (bool)$enableDev;
    }
  }

  /**
   * @return boolean
   */
  public function getEnableDev()
  {
    return $this->enableDev;
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'enableDev' => $this->getEnableDev(),
    );
  }
}
