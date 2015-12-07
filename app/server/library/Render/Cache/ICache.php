<?php
namespace Render\Cache;

use Render\Unit;

/**
 * Interface ICache
 * @package Render\Cache
 */
interface ICache
{
  /**
   * @param Unit   $unit
   * @param string $key
   * @param  array $value
   *
   */
  public function setUnitValue(Unit $unit, $key, $value);

  /**
   * @param Unit   $unit
   * @param string $key
   *
   * @return array|null
   */
  public function getUnitValue(Unit $unit, $key);

  /**
   * @param string $key
   * @param array $value
   *
   */
  public function setTemporaryValue($key, $value);

  /**
   * @param string $key
   *
   * @return array|null
   */
  public function getTemporaryValue($key);
}
