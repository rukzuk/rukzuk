<?php
namespace Cms\Request\Website;

use Cms\Request\Base;

/**
 * Request object for Website disablePublishing
 *
 */
class DisablePublishing extends Base
{
  private $runId;

  private $id;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setId($this->getRequestParam('id'));
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
}
