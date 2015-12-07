<?php
namespace Orm\Data\Template;

use Orm\Iface\Data\Uuidable as UuidMarker;
use Orm\Iface\Data\IsUnit as IsUnitMarker;

/**
 * Data object for template unit
 *
 * @package      Orm
 * @subpackage   Data\Template
 */
class MUnit implements UuidMarker, IsUnitMarker
{
  const ID_PREFIX = 'MUNIT-';
  const ID_SUFFIX = '-MUNIT';

  public $id;
  public $name;
  public $moduleId;
  public $formValues;
  public $deletable;
  public $readonly;
  public $ghostContainer;
  public $visibleFormGroups;
  public $expanded;
  public $children;
  public $description;
  public $icon;
  public $inserted;
  public $htmlClass;


  /**
   * Function that returns properties that should use as an subtree
   *
   * @return array
   */
  public function getChildPropertiesNames()
  {
      return array("children", "ghostChildren");
  }
}
