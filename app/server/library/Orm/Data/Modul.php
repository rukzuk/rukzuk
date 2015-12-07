<?php
namespace Orm\Data;

use Orm\Iface\Data\Uuidable as UuidMarker;

/**
 * Data object fuer Modul
 *
 * @package      Orm
 * @subpackage   Data
 */
class Modul implements UuidMarker
{
  const ID_PREFIX = 'MODUL-';
  const ID_SUFFIX = '-MODUL';
  
  /**
   * @var string
   */
  public $id;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $moduleId;

  /**
   * @var array
   */
  public $formValues;

  /**
   * @var boolean
   */
  public $deletable;

  /**
   * @var boolean
   */
  public $readonly;

  /**
   * @var boolean
   */
  public $ghostContainer;
}
