<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Website Datenabfrage
 *
 * @package      Cms
 * @subpackage   Dao
 */

interface Website extends \Cms\Dao\Dao
{
  /**
   * Gibt ein Array mit allen Website-Objekten zurueck
   *
   * @return array
   */
  public function getAll();

  /**
   * Gibt eine Website anhand der ID zurueck
   * @param string $id
   */
  public function getById($id);

  /**
   * update one website by id
   * @param string $id
   * @param array $attributes
   */
  public function update($id, $attributes);

  /**
   * create a new website with attributes
   * @param array $attributes
   * @param boolean $useAttributesId Defaults to false
   */
  public function create($attributes, $useAttributesId = false);

  /**
   * delete a website by id
   * @param string  $id
   */
  public function deleteById($id);

  /**
   * mark website for deletion by website id
   * @param string  $id
   */
  public function markForDeletion($id);

  /**
   * returns website with flag ismarkedfordeletion
   */
  public function getByMarkedForDeletion();

  /**
   * returns website with given creationmode
   * @param string  $creationMode
   * @return array
   */
  public function getByCreationMode($creationMode);

  /**
   * copy an existing website under a new name
   * @param string  $id
   * @param array   $attributes
   */
  public function copy($id, array $attributes);

  /**
   * Checks if there is a website under the given id
   *
   * @param  string $id
   * @return boolean
   */
  public function existsWebsite($id);

  /**
   * Increases the version number of the given website
   *
   * @param  string  $id
   * @return integer The increased version number
   */
  public function increaseVersion($id);
  /**
   * Decreases the version number of the given website
   *
   * @param  string  $id
   * @return integer The decreased version number
   */
  public function decreaseVersion($id);


  /**
   * Number of actual Websites in Database
   *
   * @return int
   */
  public function getCount();
}
