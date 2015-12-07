<?php


namespace Render\APIs\APIv1;

class WebsiteSettingsNotFound extends \Exception
{
  public function __construct()
  {
    parent::__construct('Website settings not found');
  }
}
