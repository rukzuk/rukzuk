<?php
namespace Seitenbau\Screenshot;

/**
 * Screenshot Config Exception
 *
 * @package      Cms
 */

class InvalidConfigException extends ScreenshotException
{
  public function __construct($message)
  {
    parent::__construct($message, \Seitenbau\Log::ERR);
  }
}
