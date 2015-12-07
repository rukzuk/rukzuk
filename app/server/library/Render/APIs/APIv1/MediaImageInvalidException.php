<?php


namespace Render\APIs\APIv1;

class MediaImageInvalidException extends MediaException
{

  public function __construct()
  {
    parent::__construct('Invalid image format');
  }
}
