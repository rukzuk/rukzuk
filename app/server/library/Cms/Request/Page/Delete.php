<?php
namespace Cms\Request\Page;

use Cms\Request\Base;

/**
 * Request object fuer Page delete
 *
 * @package      Cms
 * @subpackage   Request
 */

class Delete extends Base
{
  private $runId;

  private $id;

  private $websiteId;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setId($this->getRequestParam('id'));
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
}
