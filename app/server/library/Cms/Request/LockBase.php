<?php
namespace Cms\Request;

/**
 * LockBase request
 *
 * @package      Cms
 * @subpackage   Request
 */
abstract class LockBase extends Base
{
  private $runId;

  private $id;

  private $websiteId;

  private $override = false;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setId($this->getRequestParam('id'));
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

  public function getOverride()
  {
    return $this->override;
  }

  public function setOverride($override)
  {
    $this->override = $override;
  }
}
