<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer Page
 *
 * @package      Application
 * @subpackage   Controller
 */
class Page implements UuidMarker
{
  const ID_PREFIX = 'PAGE-';
  const ID_SUFFIX = '-PAGE';

  public $id;
  
  public $name;

  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setValuesFromOrm(\Orm\Entity\Page $page)
  {
    $this->setId($page->getId());
    $this->setName($page->getName());
  }
}
