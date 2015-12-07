<?php
namespace Test\Rukzuk;

require_once(TEST_PATH.'/Helper/Test/Rukzuk/MediaImageMock.php');

class MediaItemMock extends GetSetMock
{
  public function getImage()
  {
    return new MediaImageMock($this->data, $this);
  }
}
