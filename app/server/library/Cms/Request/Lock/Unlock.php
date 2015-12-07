<?php
namespace Cms\Request\Lock;

use Cms\Request\Base;

/**
 * Request object fuer Lock unlock
 *
 * @package      Cms
 * @subpackage   Request
 */

class Unlock extends Base
{
  private $runId;

  private $items;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setItems($this->getRequestParam('items'));
  }

  public function setRunId($runId)
  {
    $this->runId = $runId;
  }

  public function getRunId()
  {
    return $this->runId;
  }

  public function setItems($items)
  {
    $this->items = $items;
  }

  public function getItems()
  {
    return $this->items;
  }
}
