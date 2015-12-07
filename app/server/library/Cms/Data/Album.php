<?php
namespace Cms\Data;

use Seitenbau\UniqueIdGenerator;
use Orm\Data\Album as DataAlbum;

/**
 * Album Datenklasse
 *
 * @package      Cms
 * @subpackage   Data
 */

class Album
{
  /**
   * @var string $id
   */
  private $id;

  /**
   * @var string $websiteid
   */
  private $websiteid;

  /**
   * @var string $name
   */
  private $name;

  /**
   * @var int $lastupdate
   */
  private $lastUpdate;
  
  /**
   * Set id
   *
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }
  /**
   * Get id
   *
   * @return string $id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Get websiteid
   *
   * @return string $websiteid
   */
  public function getWebsiteid()
  {
    return $this->websiteid;
  }
  /**
   * Set websiteid
   *
   * @paramstring $websiteid
   */
  public function setWebsiteid($websiteid)
  {
    $this->websiteid = $websiteid;
    return $this;
  }

  /**
   * Set name
   *
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Get name
   *
   * @return string $name
   */
  public function getName()
  {
    return $this->name;
  }
  
  /**
   * Get lastUpdate
   *
   * @return int
   */
  public function getLastUpdate()
  {
    return $this->lastUpdate;
  }

  /**
   * Set lastUpdate
   *
   * @param int $lastUpdate
   */
  public function setLastUpdate($lastUpdate)
  {
    $this->lastUpdate = $lastUpdate;
    return $this;
  }
  
  /**
   * Setzt eine neu generierte ID
   */
  public function setNewGeneratedId()
  {
    $this->id = DataAlbum::ID_PREFIX .
                UniqueIdGenerator::v4() .
                DataAlbum::ID_SUFFIX;
  }
}
