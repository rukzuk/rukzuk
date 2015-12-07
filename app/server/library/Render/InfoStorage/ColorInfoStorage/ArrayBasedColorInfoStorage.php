<?php


namespace Render\InfoStorage\ColorInfoStorage;

use Render\InfoStorage\ColorInfoStorage\Exceptions\ColorDoesNotExists;
use Render\InfoStorage\ColorInfoStorage\Exceptions\ColorValueDoesNotExists;

/**
 * Class ArrayBasedColorInfoStorage
 *
 * @package Render\InfoStorage\ColorInfoStorage
 */
class ArrayBasedColorInfoStorage implements IColorInfoStorage
{
  const ID_PREFIX = 'COLOR-';
  const ID_SUFFIX = '-COLOR';

  /**
   * @var array
   */
  private $colors = array();
  /**
   * @var array
   */
  private $oldIdRegExp;
  /**
   * @var array
   */
  private $baseIdRegExp;

  /**
   * @param array $colors
   */
  public function __construct(array $colors)
  {
    $this->colors = $colors;
    $this->oldIdRegExp = array(
      'search' => '/([^-])(' . preg_quote(self::ID_SUFFIX, '/') . ')$/',
      'replace' => '${1}--000000000000-${2}'
    );
    $this->baseIdRegExp = array(
      'search' => '/(\-\-).*?(\-' . preg_quote(self::ID_SUFFIX, '/') . ')$/',
      'replace' => '${1}000000000000${2}',
    );
    $this->fallbackColorSearch = '/\-\-(.*?)\-' . preg_quote(self::ID_SUFFIX, '/') . '$/';
  }

  /**
   * @param string $colorId
   *
   * @return string
   */
  public function getColor($colorId)
  {
    try {
      return $this->getColorById($colorId);
    } catch (ColorDoesNotExists $ignore) {
    }
    try {
      return $this->getFallbackColorFormColorId($colorId);
    } catch (ColorValueDoesNotExists $ignore) {
    }
    return $colorId;
  }

  /**
   * @param string $colorId
   *
   * @return string
   * @throws Exceptions\ColorDoesNotExists
   */
  protected function getColorById($colorId)
  {
    try {
      return $this->getColorValueById($colorId);
    } catch (ColorValueDoesNotExists $ignore) {
    }
    try {
      $baseColorId = $this->getBaseIdFromColorId($colorId);
      return $this->getColorValueById($baseColorId);
    } catch (ColorValueDoesNotExists $ignore) {
    }

    throw new ColorDoesNotExists();
  }

  /**
   * @param string $colorId
   *
   * @return string
   */
  protected function getBaseIdFromColorId($colorId)
  {
    $newColorId = $this->getNewColorIdFormat($colorId);
    return preg_replace(
        $this->baseIdRegExp['search'],
        $this->baseIdRegExp['replace'],
        $newColorId
    );
  }

  /**
   * @param string $colorId
   *
   * @return string
   * @throws Exceptions\ColorValueDoesNotExists
   */
  protected function getFallbackColorFormColorId($colorId)
  {
    if (!preg_match($this->fallbackColorSearch, $colorId, $matches)) {
      throw new ColorValueDoesNotExists();
    }
    $fallbackColor = $matches[1];
    switch (substr($fallbackColor, 0, 1)) {
      case 1:
          $rValue = intval(substr($fallbackColor, 1, 2), 16);
          $gValue = intval(substr($fallbackColor, 3, 2), 16);
          $bValue = intval(substr($fallbackColor, 5, 2), 16);
          $aValue = intval(substr($fallbackColor, 7, 2), 16)/100;
        if ($aValue > 1) {
          $aValue = 1;
        }
            return sprintf(
                'rgba(%d,%d,%d,%.2f)',
                $rValue,
                $gValue,
                $bValue,
                $aValue
            );
        break;
    }
    throw new ColorValueDoesNotExists();
  }

  /**
   * @param string $colorId
   *
   * @return string
   * @throws Exceptions\ColorValueDoesNotExists
   */
  protected function getColorValueById($colorId)
  {
    if (!isset($this->colors[$colorId])) {
      throw new ColorValueDoesNotExists();
    }
    if (!isset($this->colors[$colorId]['value'])) {
      throw new ColorValueDoesNotExists();
    }
    if (empty($this->colors[$colorId]['value'])) {
      throw new ColorValueDoesNotExists();
    }
    if (!is_string($this->colors[$colorId]['value'])) {
      throw new ColorValueDoesNotExists();
    }
    return $this->colors[$colorId]['value'];
  }

  /**
   * @param string $colorId
   *
   * @return string
   */
  protected function getNewColorIdFormat($colorId)
  {
    return preg_replace(
        $this->oldIdRegExp['search'],
        $this->oldIdRegExp['replace'],
        $colorId
    );
  }

  /**
   * All color ids known by this info storage
   * @return string[]
   */
  public function getColorIds()
  {
    return array_keys($this->colors);
  }

  /**
   * Constructor compatible array representation
   * @return array
   */
  public function toArray()
  {
    return $this->colors;
  }
}
