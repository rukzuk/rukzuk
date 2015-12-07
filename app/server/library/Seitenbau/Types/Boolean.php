<?php
namespace Seitenbau\Types;

/**
 * Boolean
 *
 * @package      Seitenbau
 * @subpackage   Types
 */
class Boolean
{
  const DOCTRINE_VALUE = 'doctrine';
  const STRICT_VALUE   = 'boolean';
  /**
   * @var boolean
   */
  private $value = false;

  public function __construct($value)
  {
    if ($value === null || $value === '' || $value === 'null') {
      $this->value = false;
    }
    if ($value === 'true' || $value === true || $value === 1 || $value === '1') {
      $this->value = true;
    }
    if ($value === 'false' || $value === false || $value === 0 || $value === '0') {
      $this->value = false;
    }
  }
  /**
   * @param  string $mode
   * @return mixed
   */
  public function getValue($mode = self::STRICT_VALUE)
  {
    if ($mode == self::STRICT_VALUE) {
      return $this->value;
    }
    if ($mode == self::DOCTRINE_VALUE) {
      if (!$this->value) {
        return 0;
      }
      return 1;
    }
  }
}
