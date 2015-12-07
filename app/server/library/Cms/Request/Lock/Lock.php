<?php
namespace Cms\Request\Lock;

use Cms\Request\Base;
use Seitenbau\Types\Boolean as Boolean;

/**
 * Request object fuer Lock lock
 *
 * @package      Cms
 * @subpackage   Request
 */

class Lock extends Base
{
  private $runId;

  private $id;

  private $websiteId;

  private $type;

  private $override = false;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setType($this->getRequestParam('type'));
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

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  public function getType()
  {
    return $this->type;
  }

  public function setType($type)
  {
    $this->type = $type;
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
