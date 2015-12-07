<?php


namespace Render\APIs\APIv1;

class MediaItemNotFoundException extends MediaException
{

  public function __construct()
  {
    parent::__construct('Media item not found');
  }
}
