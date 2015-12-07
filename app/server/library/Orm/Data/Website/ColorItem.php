<?php
namespace Orm\Data\Website;

use Orm\Iface\Data\Uuidable as UuidMarker;
use Orm\Iface\Data\IsUnit as IsUnitMarker;
use Dual\Render\WebsiteColor as WebsiteColor;

/**
 * Data object fuer Website Colors
 *
 * @package      Orm
 * @subpackage   Data\Website
 */
class ColorItem extends WebsiteColor implements UuidMarker, IsUnitMarker
{
  /**
   * ID of the color value
   *
   * @var string
   */
  public $id = '';

  /**
   * Name of the color value
   *
   * @var string
   */
  public $name = '';

  /**
   * Color value
   *
   * @var string
   */
  public $colorValue = null;
  

  public function __construct($attributes = array())
  {
    $this->setAttributesFromArray($attributes);
  }

  public function setAttributesFromArray(array $attributes)
  {
    if (isset($attributes['id'])) {
      $this->setId($attributes['id']);
    }
    if (isset($attributes['name'])) {
      $this->setName($attributes['name']);
    }
    if (isset($attributes['value'])) {
      $this->setValue($attributes['value']);
    }
  }

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

  public function getValue()
  {
    return $this->colorValue;
  }

  public function setValue($colorValue)
  {
    $this->colorValue = $colorValue;
    return $this;
  }

  public function getBaseId()
  {
    return WebsiteColor::getBaseIdFromColorId($this->getId());
  }

  public function getIdWithFallbackColor()
  {
    return WebsiteColor::extendIdWithFallbackColor($this->getId(), $this->getValue());
  }
}
