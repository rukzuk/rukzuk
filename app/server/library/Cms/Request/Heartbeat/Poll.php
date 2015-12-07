<?php
namespace Cms\Request\Heartbeat;

use Cms\Request\Base;

/**
 * Request object fuer heartbeat poll
 *
 * @package      Cms
 * @subpackage   Request
 */

class Poll extends Base
{
  private $runId;

  private $openItems;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setOpenItems($this->getRequestParam('openitems'));
  }

  public function setRunId($runId)
  {
    $this->runId = $runId;
  }

  public function getRunId()
  {
    return $this->runId;
  }

  public function setOpenItems($openItems)
  {
    $this->openItems = $openItems;
  }

  public function getOpenItems()
  {
    return $this->openItems;
  }
}
