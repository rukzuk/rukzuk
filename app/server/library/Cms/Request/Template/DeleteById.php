<?php
namespace Cms\Request\Template;

use Cms\Request\Base;

/**
 * Request object for Template DeleteById
 *
 * @package      Cms
 * @subpackage   Request\Template
 */
class DeleteById extends Base
{
  /**
   * @var string
   */
  private $runId;

  /**
   * @var string
   */
  private $id;

  /**
   * @var string
   */
  private $websiteId;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setId($this->getRequestParam('id'));
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
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
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
