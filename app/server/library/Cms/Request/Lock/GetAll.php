<?php
namespace Cms\Request\Lock;

use Cms\Request\Base;

/**
 * Request object fuer Lock lock
 *
 * @package      Cms
 * @subpackage   Request
 */

class GetAll extends Base
{
  private $runId;

  private $websiteId;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
  }

  public function setRunId($runId)
  {
    $this->runId = $runId;
  }

  public function getRunId()
  {
    return $this->runId;
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }
}
