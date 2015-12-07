<?php
namespace Cms\Data;

/**
 * Class ExportQuota
 * @package Cms\Data
 */
class ExportQuota
{
  /**
   * @var boolean
   */
  private $exportAllowed = false;

  /**
   * @param null|boolean $exportAllowed
   */
  public function __construct($exportAllowed = null)
  {
    if (!is_null($exportAllowed)) {
      $this->exportAllowed = (bool)$exportAllowed;
    }
  }

  /**
   * @return boolean
   */
  public function getExportAllowed()
  {
    return $this->exportAllowed;
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'exportAllowed' => $this->getExportAllowed(),
    );
  }
}
