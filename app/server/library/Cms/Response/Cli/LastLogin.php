<?php
namespace Cms\Response\Cli;

use Cms\Response\IsResponseData;

class LastLogin implements IsResponseData
{
  /**
   * @var string $lastlogin
   */
  public $lastlogin;

  /**
   * @param \DateTime $lastlogin
   */
  public function __construct($lastlogin)
  {
    if ($lastlogin instanceof \DateTime) {
      $lastlogin_utc = clone $lastlogin;
      $lastlogin_utc->setTimezone(new \DateTimeZone('UTC'));
      $this->setLastlogin($lastlogin_utc->format('c'));
    }
  }

  /**
   * @param string $lastlogin
   */
  public function setLastlogin($lastlogin)
  {
    $this->lastlogin = $lastlogin;
  }
}
