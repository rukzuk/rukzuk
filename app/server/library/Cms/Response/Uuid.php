<?php
namespace Cms\Response;

/**
 * Einzelne Uuid fuer Response-Darstellung
 *
 * @package      Cms
 * @subpackage   Response
 */
class Uuid implements IsResponseData
{
  /**
   * @var array
   */
  public $uuids = array();
  /**
   * @param array $uuids
   */
  public function __construct(array $uuids)
  {
    $this->uuids = $uuids;
  }
  /**
   * @return array
   */
  public function getUuids()
  {
    return $this->uuids;
  }
}
