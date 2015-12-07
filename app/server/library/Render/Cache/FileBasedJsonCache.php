<?php
namespace Render\Cache;

use Render\Unit;

/**
 * Class FileBasedJsonCache
 * @package Render\Cache
 */
class FileBasedJsonCache implements ICache
{
  /**
   * @var string
   */
  private $cachePath;

  /**
   * @var array
   */
  private $temporaryCache = array();

  /**
   * @param string $cachePath path must exist and be writable
   */
  public function __construct($cachePath)
  {
    $this->cachePath = $cachePath;
  }

  /**
   * @param Unit   $unit
   * @param string $key
   * @param array  $value
   */
  public function setUnitValue(Unit $unit, $key, $value)
  {
    $key = (string)$key;
    $path = $this->cachePath . DIRECTORY_SEPARATOR . $unit->getId();
    if (!is_dir($path)) {
      mkdir($path);
    }
    $payload = json_encode($value);
    file_put_contents($path . DIRECTORY_SEPARATOR . $this->getFileName($key), $payload);
  }

  /**
   * @param Unit   $unit
   * @param string $key
   *
   * @return array|null
   */
  public function getUnitValue(Unit $unit, $key)
  {
    $key = (string)$key;
    $path = $this->cachePath . DIRECTORY_SEPARATOR . $unit->getId() . DIRECTORY_SEPARATOR . $this->getFileName($key);

    if (!is_readable($path)) {
      return null;
    }

    $content = file_get_contents($path);
    return json_decode($content, true);
  }

  /**
   * @param string $key
   * @param array $value
   *
   */
  public function setTemporaryValue($key, $value)
  {
    $this->temporaryCache[$key] = $value;
  }

  /**
   * @param string $key
   *
   * @return array|null
   */
  public function getTemporaryValue($key)
  {
    if (array_key_exists($key, $this->temporaryCache)) {
      return $this->temporaryCache[$key];
    }
    return null;
  }

  /**
   * @param string $key
   *
   * @return string
   */
  private function getFileName($key)
  {
    return md5((string)$key) . '.json';
  }
}
