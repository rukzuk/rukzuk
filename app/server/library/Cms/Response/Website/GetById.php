<?php

namespace Cms\Response\Website;

use Cms\Response\Website;

class GetById extends Website
{
  public function __construct($data, $privileges, $navigation, $publishInfo)
  {
    parent::__construct($data);
    $this->setPrivileges($privileges);
    $this->setNavigation($navigation);
    $this->setPublishInfo($publishInfo);
  }
}
