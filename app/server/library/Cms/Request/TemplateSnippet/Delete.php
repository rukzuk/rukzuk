<?php
namespace Cms\Request\TemplateSnippet;

use Cms\Request\Base;

/**
 * Request object for TemplateSnippet Delete
 *
 * @package      Cms
 * @subpackage   Request\TemplateSnippet
 */
class Delete extends Base
{
  /**
   * @var string
   */
  private $runId;

  /**
   * @var array
   */
  private $ids = array();

  /**
   * @var string
   */
  private $websiteId;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setIds($this->getRequestParam('ids'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
  }

  /**
   * @param string $runId
   */
  public function setRunId($runId)
  {
    $this->runId = $runId;
  }
  /**
   * @return string
   */
  public function getRunId()
  {
    return $this->runId;
  }


  /**
   * @param mixed $ids
   */
  public function setIds($ids)
  {
    $this->ids = $ids;
  }
  /**
   * @return array
   */
  public function getIds()
  {
    return $this->ids;
  }

  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
}
