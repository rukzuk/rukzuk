<?php
namespace Cms\Response;

/**
 * Response Default
 *
 * @package      Cms
 * @subpackage   Response
 *
 * @SWG\Model(id="Response/Base")
 *  @SWG\Property(name="success", type="boolean", required=true),
 *  @SWG\Property(name="error", type="array", items="$ref:Response/Error", required=true)
 * )
 */

abstract class Base
{
  public $data = null;

  public function setData($data)
  {
    $this->data = $data;
  }

  public function addData($data)
  {
    if (is_array($this->data)) {
      $this->data[] = $data;
    } elseif ($this->data == null) {
      $this->data = array($data);
    } else {
      $tmp = $this->data;
      $this->data = array($tmp);
      $this->addData($data);
    }
  }

  public function getData()
  {
    return $this->data;
  }
}
