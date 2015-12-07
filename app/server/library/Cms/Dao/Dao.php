<?php
namespace Cms\Dao;

/**
 * Dao Marker Interface
 *
 * @package      Cms
 * @subpackage   Service
 */

interface Dao
{
  /**
   * Erstellt aus DAO Objekten CMS eigene Daten Objekte
   *
   * @param mixed $data
   * @return  mixed
   */
  public function convertToCmsDataObject($data);
}
