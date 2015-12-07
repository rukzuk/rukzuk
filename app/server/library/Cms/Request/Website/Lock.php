<?php
namespace Cms\Request\Website;

use Cms\Request\Base;

/**
 * Request object fuer Website lock
 *
 * @package      Cms
 * @subpackage   Request
 */

class Lock extends Base
{
  private $runId;

  private $websiteId;

  private $override = false;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    if ($this->getRequestParam('override')) {
      $this->setOverride($this->getRequestParam('override'));
    } else {
      $this->setOverride(false);
    }
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

  public function getOverride()
  {
    return $this->override;
  }

  public function setOverride($override)
  {
    $this->override = $override;
  }
}
